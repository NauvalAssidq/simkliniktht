<?php

namespace App\Services;

use App\Models\RegPeriksa;
use App\Models\SatuSehatEncounter;
use App\Models\SatuSehatMappingPasien;
use App\Models\SatuSehatMappingDokter;
use App\Models\PemeriksaanRalan;
use App\Models\PemeriksaanAudiologi;
use App\Models\DiagnosaPasien;
use App\Models\RawatJlDr;
use App\Http\Controllers\SatuSehatController;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\ResepObat;
use App\Models\DetailResepObat;

class SatuSehatBridgingService
{
    protected $ssController;

    public function __construct()
    {
        $this->ssController = new SatuSehatController();
    }

    public function startEncounter($no_rawat)
    {
        try {
            $ssEncounter = SatuSehatEncounter::where('no_rawat', $no_rawat)->first();
            
            if (!$ssEncounter) {
                return ['success' => false, 'message' => 'Encounter not found'];
            }

            if ($ssEncounter->status === 'in-progress' || $ssEncounter->status === 'finished') {
                return ['success' => true, 'message' => 'Encounter already started or finished'];
            }

            $encounterId = $ssEncounter->id_encounter;
            $startTime = Carbon::now()->toIso8601String();
            
            $patchBody = [
                [ "op" => "replace", "path" => "/status", "value" => "in-progress" ],
                [ "op" => "add", "path" => "/statusHistory/-", "value" => [
                    "status" => "in-progress",
                    "period" => ["start" => $startTime]
                ]]
            ];
            
            $response = $this->ssController->sendRequest('PATCH', 'Encounter/' . $encounterId, $patchBody);
            
            if ($response['status'] === 'success') {
                $ssEncounter->update([
                    'status' => 'terkirim'
                ]);
                Log::info("Encounter $encounterId started (in-progress)");
                return ['success' => true, 'message' => 'Encounter started'];
            } else {
                 Log::error("Failed to start encounter $encounterId: " . json_encode($response));
                 return ['success' => false, 'message' => 'Failed to start encounter in SatuSehat'];
            }

        } catch (\Exception $e) {
            Log::error("Start Encounter Error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function bridgeEncounter($no_rawat)
    {
        try {
            $reg = RegPeriksa::with(['pasien', 'dokter', 'poliklinik'])->where('no_rawat', $no_rawat)->firstOrFail();

            $ssEncounter = SatuSehatEncounter::where('no_rawat', $no_rawat)->first();
            if (!$ssEncounter) {
                throw new \Exception("Encounter ID SatuSehat tidak ditemukan (Registrasi belum terhubung).");
            }
            $encounterId = $ssEncounter->id_encounter;

            $patientMap = SatuSehatMappingPasien::where('no_rkm_medis', $reg->no_rkm_medis)->first();
            $patientId = $patientMap ? $patientMap->ihs_patient_id : null;

            $doctorMap = SatuSehatMappingDokter::where('kd_dokter', $reg->kd_dokter)->first();
            $practitionerId = $doctorMap ? $doctorMap->ihs_practitioner_id : null;

            if (!$patientId || !$practitionerId) {
                throw new \Exception("Mapping ID Pasien atau Dokter tidak ditemukan.");
            }

            $episodeId = $this->createEpisodeOfCare($no_rawat, $patientId, $practitionerId, $reg);
            $this->activateEpisodeOfCare($episodeId, $reg);

            $this->linkEncounterToEpisode($encounterId, $episodeId);

            $this->sendVitals($no_rawat, $patientId, $encounterId, $practitionerId, $reg);
            $this->sendAnatomyObservation($no_rawat, $patientId, $encounterId, $practitionerId, $reg);
            $this->sendFunctionObservation($no_rawat, $patientId, $encounterId, $practitionerId, $reg);
            $this->sendHearingLossObservation($no_rawat, $patientId, $encounterId, $practitionerId, $reg);
            $this->sendAudiology($no_rawat, $patientId, $encounterId, $practitionerId, $reg);
            $this->sendDiagnoses($no_rawat, $patientId, $encounterId, $reg);
            $this->sendProcedures($no_rawat, $patientId, $encounterId, $practitionerId, $reg);
            $this->sendClinicalImpression($no_rawat, $patientId, $encounterId, $practitionerId, $reg);
            $this->sendCarePlanAndOutcomes($no_rawat, $patientId, $encounterId, $practitionerId, $reg);
            $this->sendMedicationRequest($no_rawat, $patientId, $encounterId, $practitionerId, $reg);
            $this->sendDischargeCondition($no_rawat, $patientId, $encounterId, $reg);

            $this->finishEncounter($encounterId);
            $this->finishEpisodeOfCare($episodeId);
            $ssEncounter->update([
                'status' => 'terkirim',
                'waktu_kirim' => now()
            ]);

            return ['success' => true, 'message' => 'Bridging Berhasil (Encounter + EpisodeOfCare)', 'encounter_id' => $encounterId];

        } catch (\Exception $e) {
            Log::error("Bridging Failed: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function createEpisodeOfCare($no_rawat, $patientId, $practitionerId, $reg) {
        $orgId = env('SATUSEHAT_ORGANIZATION_ID');
        $payload = [
            "resourceType" => "EpisodeOfCare",
            "identifier" => [
                [
                    "system" => "http://sys-ids.kemkes.go.id/episode-of-care/" . $orgId,
                    "value" => $no_rawat
                ]
            ],
            "status" => "waitlist",
            "statusHistory" => [
                [
                    "status" => "waitlist",
                    "period" => ["start" => Carbon::parse($reg->tgl_registrasi)->toIso8601String()]
                ]
            ],
            "type" => [
                [
                    "coding" => [
                        [
                            "system" => "http://terminology.kemkes.go.id",
                            "code" => "ADM",
                            "display" => "Auditory Disease Management Care"
                        ]
                    ]
                ]
            ],
            "patient" => ["reference" => "Patient/" . $patientId],
            "managingOrganization" => ["reference" => "Organization/" . $orgId],
            "period" => ["start" => Carbon::parse($reg->tgl_registrasi)->toIso8601String()],
            "careManager" => ["reference" => "Practitioner/" . $practitionerId]
        ];

        $resp = $this->ssController->sendRequest('POST', 'EpisodeOfCare', $payload);
        if (isset($resp['response']['id'])) {
            Log::info("Created EpisodeOfCare: " . $resp['response']['id']);
            return $resp['response']['id'];
        }
        if (isset($resp['id'])) {
             Log::info("Created EpisodeOfCare: " . $resp['id']);
             return $resp['id'];
        }
        
        throw new \Exception("Gagal membuat EpisodeOfCare: " . json_encode($resp));
    }

    private function activateEpisodeOfCare($episodeId, $reg) {
        $startTime = Carbon::parse($reg->tgl_registrasi)->toIso8601String();
        $activeTime = Carbon::parse($reg->tgl_registrasi)->addMinutes(5)->toIso8601String();

        $patchBody = [
            [
                "op" => "replace",
                "path" => "/status",
                "value" => "active"
            ],
            [
                "op" => "add",
                "path" => "/statusHistory/0/period/end",
                "value" => $activeTime
            ],
            [
                "op" => "add",
                "path" => "/statusHistory/-",
                "value" => [
                    "status" => "active",
                    "period" => [
                        "start" => $activeTime
                    ]
                ]
            ]
        ];
        
        $this->ssController->sendRequest('PATCH', 'EpisodeOfCare/' . $episodeId, $patchBody);
    }

    private function linkEncounterToEpisode($encounterId, $episodeId) {
        // Patch Encounter to add episodeOfCare reference
        $patchBody = [
            [ 
                "op" => "add", 
                "path" => "/episodeOfCare", 
                "value" => [
                    ["reference" => "EpisodeOfCare/" . $episodeId]
                ]
            ]
        ];
        $this->ssController->sendRequest('PATCH', 'Encounter/' . $encounterId, $patchBody);
    }

    private function finishEpisodeOfCare($episodeId) {
        $endTime = Carbon::now()->toIso8601String();
        
        $patchBody = [
            [ "op" => "replace", "path" => "/status", "value" => "finished" ],
            [ "op" => "add", "path" => "/period/end", "value" => $endTime ]
        ];
        $this->ssController->sendRequest('PATCH', 'EpisodeOfCare/' . $episodeId, $patchBody);
    }

    private function sendVitals($no_rawat, $patientId, $encounterId, $practitionerId, $reg) {
        $soap = PemeriksaanRalan::where('no_rawat', $no_rawat)->first();
        if (!$soap) return;

        // Tensi
        if (!empty($soap->tensi) && strpos($soap->tensi, '/') !== false) {
            $parts = explode('/', $soap->tensi);
            $systolic = trim($parts[0]);
            $diastolic = trim($parts[1]);
            
            $payload = [
                "resourceType" => "Observation",
                "status" => "final",
                "category" => [["coding" => [["system" => "http://terminology.hl7.org/CodeSystem/observation-category", "code" => "vital-signs", "display" => "Vital Signs"]]]],
                "code" => ["coding" => [["system" => "http://loinc.org", "code" => "85354-9", "display" => "Blood pressure panel"]]],
                "subject" => ["reference" => "Patient/" . $patientId],
                "encounter" => ["reference" => "Encounter/" . $encounterId],
                "effectiveDateTime" => Carbon::parse($reg->tgl_registrasi)->toIso8601String(),
                "performer" => [["reference" => "Practitioner/" . $practitionerId]],
                "component" => [
                    ["code" => ["coding" => [["system" => "http://loinc.org", "code" => "8480-6", "display" => "Systolic BP"]]], "valueQuantity" => ["value" => floatval($systolic), "unit" => "mmHg", "system" => "http://unitsofmeasure.org", "code" => "mm[Hg]"]],
                    ["code" => ["coding" => [["system" => "http://loinc.org", "code" => "8462-4", "display" => "Diastolic BP"]]], "valueQuantity" => ["value" => floatval($diastolic), "unit" => "mmHg", "system" => "http://unitsofmeasure.org", "code" => "mm[Hg]"]]
                ]
            ];
            $this->ssController->sendRequest('POST', 'Observation', $payload);
        }

        // Suhu
        if (!empty($soap->suhu_tubuh)) {
            $payload = [
                "resourceType" => "Observation",
                "status" => "final",
                "category" => [["coding" => [["system" => "http://terminology.hl7.org/CodeSystem/observation-category", "code" => "vital-signs", "display" => "Vital Signs"]]]],
                "code" => ["coding" => [["system" => "http://loinc.org", "code" => "8310-5", "display" => "Body temperature"]]],
                "subject" => ["reference" => "Patient/" . $patientId],
                "encounter" => ["reference" => "Encounter/" . $encounterId],
                "effectiveDateTime" => Carbon::parse($reg->tgl_registrasi)->toIso8601String(),
                "performer" => [["reference" => "Practitioner/" . $practitionerId]],
                "valueQuantity" => ["value" => floatval($soap->suhu_tubuh), "unit" => "C", "system" => "http://unitsofmeasure.org", "code" => "Cel"]
            ];
            $this->ssController->sendRequest('POST', 'Observation', $payload);
        }
    }

    private function sendAudiology($no_rawat, $patientId, $encounterId, $practitionerId, $reg) {
        $audiologi = PemeriksaanAudiologi::where('no_rawat', $no_rawat)->first();
        if (!$audiologi) return;

        $date = Carbon::parse($reg->tgl_registrasi)->toIso8601String();

        // Helper
        $sendObs = function($code, $display, $val, $siteCode, $siteDisp) use ($patientId, $encounterId, $practitionerId, $date) {
             $payload = [
                "resourceType" => "Observation",
                "status" => "final",
                "category" => [["coding" => [["system" => "http://terminology.hl7.org/CodeSystem/observation-category", "code" => "exam", "display" => "Exam"]]]],
                "code" => ["coding" => [["system" => "http://snomed.info/sct", "code" => $code, "display" => $display]]],
                "subject" => ["reference" => "Patient/" . $patientId],
                "encounter" => ["reference" => "Encounter/" . $encounterId],
                "effectiveDateTime" => $date,
                "performer" => [["reference" => "Practitioner/" . $practitionerId]],
                "bodySite" => ["coding" => [["system" => "http://snomed.info/sct", "code" => $siteCode, "display" => $siteDisp]]]
            ];
            
            if (is_numeric($val)) {
                $payload['valueQuantity'] = ["value" => floatval($val), "unit" => "dB", "system" => "http://unitsofmeasure.org", "code" => "dB"];
            } else {
                $payload['valueString'] = $val;
            }
            $this->ssController->sendRequest('POST', 'Observation', $payload);
        };

        if ($audiologi->ambang_dengar_kanan !== null) $sendObs("250620003", "Pure tone audiometry", $audiologi->ambang_dengar_kanan, "368563002", "Right ear");
        if ($audiologi->ambang_dengar_kiri !== null) $sendObs("250620003", "Pure tone audiometry", $audiologi->ambang_dengar_kiri, "368564008", "Left ear");
    }

    private function sendDiagnoses($no_rawat, $patientId, $encounterId, $reg) {
        $diagnoses = DiagnosaPasien::where('no_rawat', $no_rawat)
            ->join('penyakit', 'diagnosa_pasien.kd_penyakit', '=', 'penyakit.kd_penyakit')
            ->get();

        foreach ($diagnoses as $diag) {
            $payload = [
                "resourceType" => "Condition",
                "clinicalStatus" => ["coding" => [["system" => "http://terminology.hl7.org/CodeSystem/condition-clinical", "code" => "active", "display" => "Active"]]],
                "category" => [["coding" => [["system" => "http://terminology.hl7.org/CodeSystem/condition-category", "code" => "encounter-diagnosis", "display" => "Encounter Diagnosis"]]]],
                "code" => ["coding" => [["system" => "http://hl7.org/fhir/sid/icd-10", "code" => $diag->kd_penyakit, "display" => $diag->nm_penyakit]]],
                "subject" => ["reference" => "Patient/" . $patientId],
                "encounter" => ["reference" => "Encounter/" . $encounterId]
            ];
            $this->ssController->sendRequest('POST', 'Condition', $payload);
        }
    }

    private function sendProcedures($no_rawat, $patientId, $encounterId, $practitionerId, $reg) {
        $procedures = RawatJlDr::where('no_rawat', $no_rawat)
                ->join('jns_perawatan', 'rawat_jl_dr.kd_jenis_prw', '=', 'jns_perawatan.kd_jenis_prw')
                ->select('rawat_jl_dr.*', 'jns_perawatan.nm_perawatan')
                ->get();

        foreach ($procedures as $proc) {
            $payload = [
                "resourceType" => "Procedure",
                "status" => "completed",
                "code" => [
                    "coding" => [
                        [
                            "system" => "http://sys-ids.kemkes.go.id/procedure/" . env('SATUSEHAT_ORGANIZATION_ID'),
                            "code" => $proc->kd_jenis_prw,
                            "display" => $proc->nm_perawatan
                        ]
                    ],
                    "text" => $proc->nm_perawatan
                ],
                "subject" => ["reference" => "Patient/" . $patientId],
                "encounter" => ["reference" => "Encounter/" . $encounterId],
                "performedPeriod" => [
                    "start" => Carbon::parse($reg->tgl_registrasi)->toIso8601String(),
                    "end" => Carbon::parse($reg->tgl_registrasi)->addMinutes(15)->toIso8601String()
                ]
            ];
            $this->ssController->sendRequest('POST', 'Procedure', $payload);
        }
    }

    private function sendClinicalImpression($no_rawat, $patientId, $encounterId, $practitionerId, $reg) {
        // Implementation of Prognosis/ClinicalImpression
        $payload = [
             "resourceType" => "ClinicalImpression",
             "status" => "completed",
             "code" => ["coding" => [["system" => "http://snomed.info/sct", "code" => "20481000", "display" => "Determination of prognosis"]]],
             "subject" => ["reference" => "Patient/" . $patientId],
             "encounter" => ["reference" => "Encounter/" . $encounterId],
             "effectiveDateTime" => Carbon::now()->toIso8601String(),
             "assessor" => ["reference" => "Practitioner/" . $practitionerId],
             // Fallback default prognosis
             "prognosisCodeableConcept" => [[
                 "coding" => [["system" => "http://snomed.info/sct", "code" => "170968001", "display" => "Prognosis good"]]
             ]]
        ];
        
        $this->ssController->sendRequest('POST', 'ClinicalImpression', $payload);
    }

    private function sendDischargeCondition($no_rawat, $patientId, $encounterId, $reg) {
         $payload = [
            "resourceType" => "Condition",
            "clinicalStatus" => ["coding" => [["system" => "http://terminology.hl7.org/CodeSystem/condition-clinical", "code" => "active", "display" => "Active"]]],
            "category" => [["coding" => [["system" => "http://terminology.hl7.org/CodeSystem/condition-category", "code" => "problem-list-item", "display" => "Problem List Item"]]]],
            "code" => ["coding" => [["system" => "http://snomed.info/sct", "code" => "359746009", "display" => "Patient's condition stable"]]],
            "subject" => ["reference" => "Patient/" . $patientId],
            "encounter" => ["reference" => "Encounter/" . $encounterId]
        ];
        $this->ssController->sendRequest('POST', 'Condition', $payload);
    }

    private function sendCarePlanAndOutcomes($no_rawat, $patientId, $encounterId, $practitionerId, $reg) {
         // Send CarePlan (Specific THT)
        $payload = [
            "resourceType" => "CarePlan",
            "status" => "active",
            "intent" => "plan",
            "category" => [[
                "coding" => [["system" => "http://snomed.info/sct", "code" => "773983001", "display" => "Ear, nose and throat care plan"]]
            ]],
            "title" => "Akhir Pengobatan Telinga",
            "description" => "Rencana perawatan THT untuk pasien.",
            "subject" => ["reference" => "Patient/" . $patientId],
            "encounter" => ["reference" => "Encounter/" . $encounterId],
            "period" => ["start" => Carbon::now()->toIso8601String()],
            "author" => ["reference" => "Practitioner/" . $practitionerId],
            "activity" => [[
                "outcomeCodeableConcept" => [[ "coding" => [["system" => "http://snomed.info/sct", "code" => "182992009", "display" => "Treatment completed"]] ]]
            ]]
        ];
        $this->ssController->sendRequest('POST', 'CarePlan', $payload);
    }

    private function finishEncounter($encounterId) {
        $endTime = Carbon::now()->toIso8601String();
        
        $patchBody = [
            [ "op" => "replace", "path" => "/status", "value" => "finished" ],
            [ "op" => "add", "path" => "/period/end", "value" => $endTime ],
            [ "op" => "add", "path" => "/statusHistory/-", "value" => [
                "status" => "finished",
                "period" => ["start" => $endTime, "end" => $endTime]
            ]],
            [ 
                "op" => "add", 
                "path" => "/hospitalization", 
                "value" => [
                    "dischargeDisposition" => [
                        "coding" => [[
                            "system" => "http://terminology.hl7.org/CodeSystem/discharge-disposition",
                            "code" => "home",
                            "display" => "Home"
                        ]],
                        "text" => "Pasien dapat dipulangkan"
                    ]
                ] 
            ]
        ];
        
        $this->ssController->sendRequest('PATCH', 'Encounter/' . $encounterId, $patchBody);
    }

    private function sendAnatomyObservation($no_rawat, $patientId, $encounterId, $practitionerId, $reg) {
        $soap = PemeriksaanRalan::where('no_rawat', $no_rawat)->first();
        if (!$soap || empty($soap->pemeriksaan)) return;

        $payload = [
            "resourceType" => "Observation",
            "status" => "final",
            "category" => [["coding" => [["system" => "http://terminology.hl7.org/CodeSystem/observation-category", "code" => "survey", "display" => "Survey"]]]],
            "code" => ["coding" => [["system" => "http://snomed.info/sct", "code" => "385898003", "display" => "Ear care assessment"]]],
            "subject" => ["reference" => "Patient/" . $patientId],
            "encounter" => ["reference" => "Encounter/" . $encounterId],
            "effectiveDateTime" => Carbon::parse($reg->tgl_registrasi)->toIso8601String(),
            "performer" => [["reference" => "Practitioner/" . $practitionerId]],
            "component" => [
                ["code" => ["coding" => [["system" => "http://terminology.kemkes.go.id", "code" => "TK000140", "display" => "Lampu Kepala"]]], "valueBoolean" => true],
                ["code" => ["coding" => [["system" => "http://terminology.kemkes.go.id", "code" => "TK000143", "display" => "Otoskopi"]]], "valueBoolean" => true],
            ],
             "note" => [
                ["text" => $soap->pemeriksaan]
            ]
        ];
        $this->ssController->sendRequest('POST', 'Observation', $payload);
    }

    private function sendFunctionObservation($no_rawat, $patientId, $encounterId, $practitionerId, $reg) {
         $payload = [
            "resourceType" => "Observation",
            "status" => "final",
            "category" => [["coding" => [["system" => "http://terminology.hl7.org/CodeSystem/observation-category", "code" => "survey", "display" => "Survey"]]]],
            "code" => ["coding" => [["system" => "http://terminology.kemkes.go.id", "code" => "TK000135", "display" => "Pemeriksaan Fungsi"]]],
            "subject" => ["reference" => "Patient/" . $patientId],
            "encounter" => ["reference" => "Encounter/" . $encounterId],
            "effectiveDateTime" => Carbon::parse($reg->tgl_registrasi)->toIso8601String(),
            "performer" => [["reference" => "Practitioner/" . $practitionerId]],
            "component" => [
                ["code" => ["coding" => [["system" => "http://snomed.info/sct", "code" => "21727005", "display" => "Audiometry"]]], "valueBoolean" => true]
            ],
            "valueString" => "Pemeriksaan fungsi pendengaran dilakukan."
        ];
        $this->ssController->sendRequest('POST', 'Observation', $payload);
    }

    private function sendHearingLossObservation($no_rawat, $patientId, $encounterId, $practitionerId, $reg) {
        $audiologi = PemeriksaanAudiologi::where('no_rawat', $no_rawat)->first();
        if (!$audiologi) return;

        $mapTypeToCode = function($type, $side) {
            if ($side === 'Right') {
                if ($type === 'Conductive') return '1010236009';
                if ($type === 'Sensorineural') return '1010237000';
                if ($type === 'Mixed') return '1010238005';
            } else {
                if ($type === 'Conductive') return '1010231004';
                if ($type === 'Sensorineural') return '1010232008';
                if ($type === 'Mixed') return '77507001'; 
            }
            return null;
        };

        // Right Ear
        if (!empty($audiologi->tipe_gangguan_kanan)) {
            $code = $mapTypeToCode($audiologi->tipe_gangguan_kanan, 'Right');
            if ($code) {
                $payload = [
                    "resourceType" => "Observation",
                    "status" => "final",
                     "category" => [["coding" => [["system" => "http://terminology.hl7.org/CodeSystem/observation-category", "code" => "exam", "display" => "Exam"]]]],
                    "code" => ["coding" => [["system" => "http://snomed.info/sct", "code" => "47078008", "display" => "Hearing, function"]]],
                    "subject" => ["reference" => "Patient/" . $patientId],
                    "encounter" => ["reference" => "Encounter/" . $encounterId],
                    "effectiveDateTime" => Carbon::parse($reg->tgl_registrasi)->toIso8601String(),
                    "performer" => [["reference" => "Practitioner/" . $practitionerId]],
                    "valueCodeableConcept" => ["coding" => [["system" => "http://snomed.info/sct", "code" => $code, "display" => $audiologi->tipe_gangguan_kanan . " hearing loss"]]],
                    "bodySite" => ["coding" => [["system" => "http://snomed.info/sct", "code" => "25577004", "display" => "Right ear structure"]]]
                ];
                $this->ssController->sendRequest('POST', 'Observation', $payload);
            }
        }

        // Left Ear
        if (!empty($audiologi->tipe_gangguan_kiri)) {
            $code = $mapTypeToCode($audiologi->tipe_gangguan_kiri, 'Left');
            if ($code) {
                $payload = [
                    "resourceType" => "Observation",
                    "status" => "final",
                     "category" => [["coding" => [["system" => "http://terminology.hl7.org/CodeSystem/observation-category", "code" => "exam", "display" => "Exam"]]]],
                    "code" => ["coding" => [["system" => "http://snomed.info/sct", "code" => "47078008", "display" => "Hearing, function"]]],
                    "subject" => ["reference" => "Patient/" . $patientId],
                    "encounter" => ["reference" => "Encounter/" . $encounterId],
                    "effectiveDateTime" => Carbon::parse($reg->tgl_registrasi)->toIso8601String(),
                    "performer" => [["reference" => "Practitioner/" . $practitionerId]],
                    "valueCodeableConcept" => ["coding" => [["system" => "http://snomed.info/sct", "code" => $code, "display" => $audiologi->tipe_gangguan_kiri . " hearing loss"]]],
                    "bodySite" => ["coding" => [["system" => "http://snomed.info/sct", "code" => "89644007", "display" => "Left ear structure"]]]
                ];
                $this->ssController->sendRequest('POST', 'Observation', $payload);
            }
        }
    }

    private function sendMedicationRequest($no_rawat, $patientId, $encounterId, $practitionerId, $reg) {
        $resep = ResepObat::where('no_rawat', $no_rawat)->first();
        if (!$resep) return;

        $orgId = env('SATUSEHAT_ORGANIZATION_ID');
        
        foreach ($resep->detail as $item) {
            if (!$item->kd_brng) continue;

            $payload = [
                "resourceType" => "MedicationRequest",
                "identifier" => [
                    [
                        "system" => "http://sys-ids.kemkes.go.id/prescription/" . $orgId,
                        "use" => "official",
                        "value" => $resep->no_resep
                    ]
                ],
                "status" => "active",
                "intent" => "order",
                "category" => [
                    [
                        "coding" => [
                            [
                                "system" => "http://terminology.hl7.org/CodeSystem/medicationrequest-category",
                                "code" => "outpatient",
                                "display" => "Outpatient"
                            ]
                        ]
                    ]
                ],
                "priority" => "routine",
                "medicationCodeableConcept" => [
                    "coding" => [
                        [
                            "system" => "http://sys-ids.kemkes.go.id/kfa",
                            "code" => $item->kd_brng,
                            "display" => $item->nama_obat
                        ]
                    ]
                ],
                "subject" => [
                    "reference" => "Patient/" . $patientId,
                    "display" => $reg->pasien->nm_pasien ?? 'Pasien'
                ],
                "encounter" => [
                    "reference" => "Encounter/" . $encounterId
                ],
                "authoredOn" => Carbon::parse($resep->tgl_resep . ' ' . $resep->jam_resep)->toIso8601String(),
                "requester" => [
                    "reference" => "Practitioner/" . $practitionerId,
                    "display" => $resep->dokter->nm_dokter ?? 'Dokter'
                ],
                "dosageInstruction" => [
                    [
                        "sequence" => 1,
                        "text" => ($item->dosis ?? '1') . ', ' . ($item->frekuensi ?? '1x1') . ', ' . ($item->instruksi ?? '-'),
                        "timing" => [
                            "repeat" => [
                                "frequency" => 1,
                                "period" => 1,
                                "periodUnit" => "d"
                            ]
                        ],
                        "doseAndRate" => [
                            [
                                "type" => [
                                    "coding" => [
                                        [
                                            "system" => "http://terminology.hl7.org/CodeSystem/dose-rate-type",
                                            "code" => "ordered",
                                            "display" => "Ordered"
                                        ]
                                    ]
                                ],
                                "doseQuantity" => [
                                    "value" => (float)($item->dosis ?? 1),
                                    "unit" => "Tablet", // Idealnya dari master obat
                                    "system" => "http://terminology.hl7.org/CodeSystem/v3-orderableDrugForm",
                                    "code" => "TAB"
                                ]
                            ]
                        ]
                    ]
                ],
                "dispenseRequest" => [
                    "dispenseInterval" => [
                        "value" => 1,
                        "unit" => "days",
                        "system" => "http://unitsofmeasure.org",
                        "code" => "d"
                    ],
                    "validityPeriod" => [
                        "start" => Carbon::parse($resep->tgl_resep)->toIso8601String(),
                        "end" => Carbon::parse($resep->tgl_resep)->addDays(1)->toIso8601String()
                    ],
                    "numberOfRepeatsAllowed" => 0,
                    "quantity" => [
                        "value" => (float)$item->jumlah,
                        "unit" => "Tablet",
                        "system" => "http://terminology.hl7.org/CodeSystem/v3-orderableDrugForm",
                        "code" => "TAB"
                    ],
                    "expectedSupplyDuration" => [
                        "value" => 3, // Estimasi 3 hari
                        "unit" => "days",
                        "system" => "http://unitsofmeasure.org",
                        "code" => "d"
                    ],
                    "performer" => [
                        "reference" => "Organization/" . $orgId
                    ]
                ]
            ];

            $this->ssController->sendRequest('POST', 'MedicationRequest', $payload);
        }
    }

    public function bridgeMedicationDispense($no_resep)
    {
        $resep = ResepObat::with(['detail', 'regPeriksa.pasien'])->where('no_resep', $no_resep)->first();
        if (!$resep) return ['success' => false, 'message' => 'Resep not found'];

        // Get Patient ID
        $ssPasien = SatuSehatMappingPasien::where('no_rkm_medis', $resep->regPeriksa->no_rkm_medis)->first();
        if (!$ssPasien) return ['success' => false, 'message' => 'Pasien belum terhubung ke SatuSehat'];
        $patientId = $ssPasien->ihs_patient_id;

        // Get Encounter ID
        $ssEncounter = SatuSehatEncounter::where('no_rawat', $resep->no_rawat)->first();
        if (!$ssEncounter) return ['success' => false, 'message' => 'Encounter belum terhubung ke SatuSehat'];
        $encounterId = $ssEncounter->id_encounter;

        $orgId = env('SATUSEHAT_ORGANIZATION_ID');
        $dispenseTime = Carbon::now()->toIso8601String();

        foreach ($resep->detail as $item) {
            if (!$item->kd_brng) continue;

            $payload = [
                "resourceType" => "MedicationDispense",
                "identifier" => [
                    [
                        "system" => "http://sys-ids.kemkes.go.id/prescription/" . $orgId,
                        "value" => $resep->no_resep
                    ]
                ],
                 "status" => "completed",
                 "category" => [
                    "coding" => [
                        [
                            "system" => "http://terminology.hl7.org/CodeSystem/medicationdispense-category",
                             "code" => "outpatient",
                             "display" => "Outpatient"
                        ]
                    ]
                 ],
                 "medicationCodeableConcept" => [
                    "coding" => [
                        [
                            "system" => "http://sys-ids.kemkes.go.id/kfa",
                            "code" => $item->kd_brng,
                            "display" => $item->nama_obat
                        ]
                    ]
                 ],
                 "subject" => [
                    "reference" => "Patient/" . $patientId
                 ],
                 "context" => [
                    "reference" => "Encounter/" . $encounterId
                 ],
                 "performer" => [
                     [
                         "actor" => [
                             "reference" => "Practitioner/" . ($this->getPractitionerId($resep->kd_dokter) ?: 'Unknown')
                         ]
                     ]
                 ],
                 "location" => [
                     "reference" => "Location/" . $orgId
                 ],
                 "authorizingPrescription" => [
                     [
                        "reference" => "MedicationRequest/" . $resep->no_resep
                     ]
                 ],
                 "type" => [
                    "coding" => [
                        [
                            "system" => "http://terminology.hl7.org/CodeSystem/v3-ActPharmacySupplyType",
                            "code" => "RFP",
                            "display" => "Refill Part Fill"
                        ]
                    ]
                 ],
                 "quantity" => [
                     "value" => (float)$item->jumlah,
                     "unit" => "Tablet",
                     "system" => "http://terminology.hl7.org/CodeSystem/v3-orderableDrugForm",
                     "code" => "TAB"
                 ],
                 "daysSupply" => [
                     "value" => 3,
                     "unit" => "days",
                     "system" => "http://unitsofmeasure.org",
                     "code" => "d"
                 ],
                 "whenPrepared" => $dispenseTime,
                 "whenHandedOver" => $dispenseTime,
                 "dosageInstruction" => [
                    [
                        "sequence" => 1,
                         "text" => ($item->dosis ?? '1') . ', ' . ($item->frekuensi ?? '1x1') . ', ' . ($item->instruksi ?? '-'),
                         "timing" => [
                            "repeat" => [
                                "frequency" => 1,
                                "period" => 1,
                                "periodUnit" => "d"
                            ]
                        ]
                    ]
                 ]
            ];
             
             unset($payload['authorizingPrescription']);

            $this->ssController->sendRequest('POST', 'MedicationDispense', $payload);
        }

        return ['success' => true, 'message' => 'Medication Dispense info sent to SatuSehat'];
    }

    private function getPractitionerId($kd_dokter) {
         $map = SatuSehatMappingDokter::where('kd_dokter', $kd_dokter)->first();
         return $map ? $map->ihs_practitioner_id : null;
    }
}
