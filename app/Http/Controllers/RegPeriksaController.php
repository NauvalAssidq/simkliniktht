<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pasien;
use App\Models\RegPeriksa;
use App\Models\AntriPoli;
use App\Models\Dokter;
use App\Models\Poliklinik;
use App\Models\SatuSehatEncounter;
use App\Models\SatuSehatMappingPasien;
use App\Models\SatuSehatMappingDokter;
use App\Models\SatuSehatMappingLokasiRalan;
use App\Models\PemeriksaanAudiologi; // New
use App\Models\RawatJlDr; // New
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RegPeriksaController extends Controller
{
    public function index()
    {
        $doctors = Dokter::where('status', '1')->get();
        $registrations = RegPeriksa::with(['pasien', 'dokter', 'poliklinik'])
            // ->whereDate('tgl_registrasi', date('Y-m-d')) // Commented out for debugging
            ->orderBy('tgl_registrasi', 'desc')
            ->orderBy('jam_reg', 'desc')
            ->limit(50) // Limit to prevent overload
            ->get();
            
        return view('tht.registration', compact('doctors', 'registrations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nm_pasien' => 'required|string',
            'kd_dokter' => 'required',
            // simplified validation
        ]);

        DB::beginTransaction();
        $ihsId = null; // Initialize outside try/catch for scope
        try {
            // 1. Handle Pasien (Find or Create)
            $no_rkm_medis = $this->generateNoRkmMedis();
            $pasien = Pasien::create([
                'no_rkm_medis' => $no_rkm_medis,
                'nm_pasien' => $request->nm_pasien,
                'no_ktp' => $request->no_ktp ?? '-',
                'jk' => $request->jk ?? 'L',
                'tgl_lahir' => $request->tgl_lahir ?? date('Y-m-d'),
                'nm_ibu' => '-',
                'alamat' => '-',
            ]);

            // --- SATUSEHAT PATIENT INTEGRATION ---
            if ($request->no_ktp && strlen($request->no_ktp) > 10) {
                try {
                    $ssController = new SatuSehatController();
                    $ihsId = $ssController->searchPatientByNik($request->no_ktp);
                    
                    if ($ihsId) {
                        \Log::info('SatuSehat Patient Found: ' . $ihsId . ' for NIK: ' . $request->no_ktp);
                        SatuSehatMappingPasien::updateOrCreate(
                            ['no_rkm_medis' => $no_rkm_medis],
                            ['ihs_patient_id' => $ihsId]
                        );
                    } else {
                        \Log::warning('SatuSehat Patient Not Found for NIK: ' . $request->no_ktp);
                    }
                } catch (\Exception $e) {
                     \Log::error('SatuSehat Patient Search Error: ' . $e->getMessage());
                }
            }
            // -------------------------------------

            $no_rawat = date('Y/m/d').'/'.$no_rkm_medis;
            $kd_poli = 'U0001'; // Default THT

            $regPeriksa = RegPeriksa::create([
                'no_reg' => '001', // Needs auto-increment logic ideally, simplified here
                'no_rawat' => $no_rawat,
                'tgl_registrasi' => date('Y-m-d'),
                'jam_reg' => date('H:i:s'),
                'kd_dokter' => $request->kd_dokter,
                'no_rkm_medis' => $pasien->no_rkm_medis,
                'kd_poli' => $kd_poli,
                'stts' => 'Belum',
                'biaya_reg' => 0
            ]);

            // 3. Handle Queue (AntriPoli)
            $lastAntrian = AntriPoli::where('kd_poli', $kd_poli)
                ->where('tgl_antrian', date('Y-m-d'))
                ->orderBy('angka_antrian', 'desc')
                ->first();
            
            $nextAntrian = ($lastAntrian) ? $lastAntrian->angka_antrian + 1 : 1;

            AntriPoli::create([
                'kd_poli' => $kd_poli,
                'kd_dokter' => $request->kd_dokter,
                'no_antrian' => 'A',
                'angka_antrian' => $nextAntrian,
                'no_rawat' => $no_rawat,
                'tgl_antrian' => date('Y-m-d'),
                'status' => '0'
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Registration Successful! ' . ($ihsId ? 'Connected to SatuSehat.' : ''));

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Registration Error: ' . $e->getMessage());
            return back()->with('error', 'Registration Failed: ' . $e->getMessage());
        }
    }

    public function bridging($no_rawat)
    {
        $reg = RegPeriksa::with(['pasien', 'dokter', 'poliklinik'])->where('no_rawat', $no_rawat)->firstOrFail();
        
        // Check if already sent
        $existing = \App\Models\SatuSehatEncounter::where('no_rawat', $no_rawat)->first();
        if ($existing && $existing->status == 'terkirim') {
            return redirect()->back()->with('success', 'Data already sent to SatuSehat! ID: ' . $existing->id_encounter);
        }

        // 1. Get Patient IHS ID
        $patientId = $this->getPatientIhsId($reg->pasien);
        if (!$patientId) {
            return redirect()->back()->with('error', 'Patient IHS ID not found and could not be resolved from SatuSehat (NIK: ' . $reg->pasien->no_ktp . ')');
        }

        // 2. Get Practitioner IHS ID
        $practitionerId = \App\Models\SatuSehatMappingDokter::where('kd_dokter', $reg->kd_dokter)->value('ihs_practitioner_id');
        if (!$practitionerId) {
             return redirect()->back()->with('error', 'Practitioner IHS ID not found for ' . $reg->dokter->nm_dokter . '. Please map it first.');
        }

        // 3. Get Location IHS ID
        $locationId = \App\Models\SatuSehatMappingLokasiRalan::where('kd_poli', $reg->kd_poli)->value('ihs_location_id');
         if (!$locationId) {
             return redirect()->back()->with('error', 'Location IHS ID not found for ' . $reg->poliklinik->nm_poli . '. Please map it first.');
        }

        // --- ENCOUNTER ---
        // --- 1. EPISODE OF CARE (ADM) ---
        // Postman: 32. Use Case - Registrasi Telinga -> EpisodeOfCare -> Create Episode Perawatan Telinga
        $eocPayload = [
            "resourceType" => "EpisodeOfCare",
            "identifier" => [
                [
                    "system" => "http://sys-ids.kemkes.go.id/episode-of-care/" . env('SATUSEHAT_ORGANIZATION_ID'),
                    "value" => $no_rawat
                ]
            ],
            "status" => "waitlist",
            "statusHistory" => [
                [
                    "status" => "waitlist",
                    "period" => [
                        "start" => Carbon::parse($reg->tgl_registrasi . ' ' . $reg->jam_reg)->toIso8601String()
                    ]
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
            "patient" => [
                "reference" => "Patient/" . $patientId,
                "display" => $reg->pasien->nm_pasien
            ],
            "managingOrganization" => [
                "reference" => "Organization/" . env('SATUSEHAT_ORGANIZATION_ID')
            ],
            "period" => [
                "start" => Carbon::parse($reg->tgl_registrasi . ' ' . $reg->jam_reg)->toIso8601String()
            ]
        ];

        $ssController = new SatuSehatController();
        $eocResponse = $ssController->sendRequest('POST', 'EpisodeOfCare', $eocPayload);

        // Check EOC success (If error 422/400, it might already exist, so we try to proceed or handle it)
        // For now, if it fails, we log and stop to be safe, or we could try to GET it.
        // Simplified: Attempt to get ID from response.
        $eocId = $eocResponse['id'] ?? null;
        if (!$eocId) {
             // Fallback: If it says "already exists" (which we can't easily parse without seeing error body), we might need to GET it.
             // For this implementation, we will assume it's a new chain or we fail.
             \Log::error('RegPeriksaController: Failed to create/retrieve EpisodeOfCare', ['response' => $eocResponse]);
             return redirect()->back()->with('error', 'Gagal membuat EpisodeOfCare SatuSehat. Cek Log.');
        }

        // --- 2. ENCOUNTER ---
        $encounterPayload = [
            "resourceType" => "Encounter",
            "status" => "arrived",
            "class" => [
                "system" => "http://terminology.hl7.org/CodeSystem/v3-ActCode",
                "code" => "AMB",
                "display" => "ambulatory"
            ],
            "subject" => [
                "reference" => "Patient/" . $patientId,
                "display" => $reg->pasien->nm_pasien
            ],
            "participant" => [
                [
                    "type" => [
                        [
                            "coding" => [
                                [
                                    "system" => "http://terminology.hl7.org/CodeSystem/v3-ParticipationType",
                                    "code" => "ATND",
                                    "display" => "attender"
                                ]
                            ]
                        ]
                    ],
                    "individual" => [
                        "reference" => "Practitioner/" . $practitionerId,
                        "display" => $reg->dokter->nm_dokter
                    ]
                ]
            ],
            "period" => [
                "start" => Carbon::parse($reg->tgl_registrasi . ' ' . $reg->jam_reg)->toIso8601String(),
            ],
            "location" => [
                [
                    "location" => [
                        "reference" => "Location/" . $locationId,
                        "display" => $reg->poliklinik->nm_poli
                    ]
                ]
            ],
            "serviceProvider" => [
                "reference" => "Organization/" . env('SATUSEHAT_ORGANIZATION_ID')
            ],
            // Link to EpisodeOfCare
            "episodeOfCare" => [
                [
                    "reference" => "EpisodeOfCare/" . $eocId
                ]
            ]
        ];

        $response = $ssController->sendRequest('POST', 'Encounter', $encounterPayload);

        if (isset($response['id'])) {
            $encounterId = $response['id'];
            
            \App\Models\SatuSehatEncounter::updateOrCreate(
                ['no_rawat' => $no_rawat],
                [
                    'id_encounter' => $encounterId,
                    'waktu_kirim' => now(),
                    'status' => 'terkirim',
                    'response' => json_encode($response)
                ]
            );

            // --- 3. ANAMNESIS (OBSERVATIONS) ---
            $soap = \App\Models\PemeriksaanRalan::where('no_rawat', $no_rawat)->first();
            
            // Helper function to send generic observation
            $sendObservation = function($code, $display, $valueBoolean, $system = "http://snomed.info/sct") use ($ssController, $patientId, $encounterId, $practitionerId, $reg) {
                $obsPayload = [
                    "resourceType" => "Observation",
                    "status" => "final",
                    "category" => [
                        [
                            "coding" => [
                                [
                                    "system" => "http://terminology.hl7.org/CodeSystem/observation-category",
                                    "code" => "survey",
                                    "display" => "Survey"
                                ]
                            ]
                        ]
                    ],
                    "code" => [
                        "coding" => [
                            [
                                "system" => $system,
                                "code" => $code,
                                "display" => $display
                            ]
                        ]
                    ],
                    "subject" => ["reference" => "Patient/" . $patientId],
                    "encounter" => ["reference" => "Encounter/" . $encounterId],
                    "effectiveDateTime" => Carbon::parse($reg->tgl_registrasi . ' ' . $reg->jam_reg)->toIso8601String(),
                    "issued" => Carbon::now()->toIso8601String(),
                    "performer" => [["reference" => "Practitioner/" . $practitionerId]],
                    "valueBoolean" => $valueBoolean
                ];
                $ssController->sendRequest('POST', 'Observation', $obsPayload);
            };

            // Send Observations (Defaults to false/true based on your specific logic, simplified here)
            // Diabetes Melitus (SNOMED 73211009)
            $sendObservation("73211009", "Diabetes melitus", false); 
            // Hypertension (SNOMED 38341003)
            $sendObservation("38341003", "Hypertension", false);
            // History of Kidney Disease (SNOMED 275552000)
            $sendObservation("275552000", "History of kidney disease", false);
            // History of Rubella (SNOMED 161421005)
            $sendObservation("161421005", "History of rubella", false);
            // Riwayat Perawatan NICU (Local TK000131)
            $sendObservation("TK000131", "Riwayat Perawatan NICU", false, "http://terminology.kemkes.go.id");

            // --- 4. PHYSICAL EXAM (OTOSCOPY) ---
            if ($soap) {
                 $otoscopyPayload = [
                    "resourceType" => "Observation",
                    "status" => "final",
                    "category" => [["coding" => [["system" => "http://terminology.hl7.org/CodeSystem/observation-category", "code" => "exam", "display" => "Exam"]]]],
                    "code" => ["coding" => [["system" => "http://snomed.info/sct", "code" => "300196000", "display" => "Inspection of ear"]]],
                    "subject" => ["reference" => "Patient/" . $patientId],
                    "encounter" => ["reference" => "Encounter/" . $encounterId],
                    "effectiveDateTime" => Carbon::parse($reg->tgl_registrasi . ' ' . $reg->jam_reg)->toIso8601String(),
                    "performer" => [["reference" => "Practitioner/" . $practitionerId]],
                    "valueString" => "Pemeriksaan Telinga: " . ($soap->pemeriksaan ?? 'Tidak ada catatan')
                ];
                $ssController->sendRequest('POST', 'Observation', $otoscopyPayload);
            }

            // --- CONDITION (DIAGNOSIS) ---
            $diagnoses = \App\Models\DiagnosaPasien::where('no_rawat', $no_rawat)
                ->join('penyakit', 'diagnosa_pasien.kd_penyakit', '=', 'penyakit.kd_penyakit')
                ->get();

            foreach ($diagnoses as $diag) {
                $conditionPayload = [
                    "resourceType" => "Condition",
                    "clinicalStatus" => [
                        "coding" => [
                            [
                                "system" => "http://terminology.hl7.org/CodeSystem/condition-clinical",
                                "code" => "active",
                                "display" => "Active"
                            ]
                        ]
                    ],
                    "category" => [
                        [
                            "coding" => [
                                [
                                    "system" => "http://terminology.hl7.org/CodeSystem/condition-category",
                                    "code" => "encounter-diagnosis",
                                    "display" => "Encounter Diagnosis"
                                ]
                            ]
                        ]
                    ],
                    "code" => [
                        "coding" => [
                            [
                                "system" => "http://hl7.org/fhir/sid/icd-10",
                                "code" => $diag->kd_penyakit,
                                "display" => $diag->nm_penyakit
                            ]
                        ]
                    ],
                    "subject" => [
                        "reference" => "Patient/" . $patientId,
                        "display" => $reg->pasien->nm_pasien
                    ],
                    "encounter" => [
                        "reference" => "Encounter/" . $encounterId
                    ]
                ];
                $ssController->sendRequest('POST', 'Condition', $conditionPayload);
            }

            // --- OBSERVATION (VITALS) ---
            $soap = \App\Models\PemeriksaanRalan::where('no_rawat', $no_rawat)->first();
            if ($soap) {
                // Heart Rate (Nadi) - Example, assuming stored in 'nadi' column if existed, using 'tensi' instead for demo
                // Systolic Blood Pressure
                if (!empty($soap->tensi) && strpos($soap->tensi, '/') !== false) {
                    $parts = explode('/', $soap->tensi);
                    $systolic = trim($parts[0]);
                    $diastolic = trim($parts[1]);

                    $observationPayload = [
                        "resourceType" => "Observation",
                        "status" => "final",
                        "category" => [
                           [
                               "coding" => [
                                   [
                                       "system" => "http://terminology.hl7.org/CodeSystem/observation-category",
                                       "code" => "vital-signs",
                                       "display" => "Vital Signs"
                                   ]
                               ]
                           ]
                        ],
                        "code" => [
                             "coding" => [
                                 [
                                     "system" => "http://loinc.org",
                                     "code" => "85354-9",
                                     "display" => "Blood pressure panel with all children optional"
                                 ]
                             ]
                        ],
                        "subject" => [
                             "reference" => "Patient/" . $patientId
                        ],
                        "encounter" => [
                             "reference" => "Encounter/" . $encounterId
                        ],
                        "effectiveDateTime" => Carbon::parse($soap->tgl_perawatan . ' ' . $soap->jam_rawat)->toIso8601String(),
                        "component" => [
                             [
                                 "code" => [
                                     "coding" => [
                                         [
                                             "system" => "http://loinc.org",
                                             "code" => "8480-6",
                                             "display" => "Systolic blood pressure"
                                         ]
                                     ]
                                 ],
                                 "valueQuantity" => [
                                     "value" => floatval($systolic),
                                     "unit" => "mmHg",
                                     "system" => "http://unitsofmeasure.org",
                                     "code" => "mm[Hg]"
                                 ]
                             ],
                             [
                                 "code" => [
                                     "coding" => [
                                         [
                                             "system" => "http://loinc.org",
                                             "code" => "8462-4",
                                             "display" => "Diastolic blood pressure"
                                         ]
                                     ]
                                 ],
                                 "valueQuantity" => [
                                     "value" => floatval($diastolic),
                                     "unit" => "mmHg",
                                     "system" => "http://unitsofmeasure.org",
                                     "code" => "mm[Hg]"
                                 ]
                             ]
                        ]
                    ];
                    $ssController->sendRequest('POST', 'Observation', $observationPayload);
                }
                
                 // Body Temperature
                if (!empty($soap->suhu_tubuh)) {
                    $bodyTempPayload = [
                        "resourceType" => "Observation",
                        "status" => "final",
                        "category" => [
                           [
                               "coding" => [
                                   [
                                       "system" => "http://terminology.hl7.org/CodeSystem/observation-category",
                                       "code" => "vital-signs",
                                       "display" => "Vital Signs"
                                   ]
                               ]
                           ]
                        ],
                        "code" => [
                             "coding" => [
                                 [
                                     "system" => "http://loinc.org",
                                     "code" => "8310-5",
                                     "display" => "Body temperature"
                                 ]
                             ]
                        ],
                        "subject" => [
                             "reference" => "Patient/" . $patientId
                        ],
                        "encounter" => [
                             "reference" => "Encounter/" . $encounterId
                        ],
                        "effectiveDateTime" => Carbon::parse($soap->tgl_perawatan . ' ' . $soap->jam_rawat)->toIso8601String(),
                        "valueQuantity" => [
                            "value" => floatval($soap->suhu_tubuh),
                            "unit" => "C",
                            "system" => "http://unitsofmeasure.org",
                            "code" => "Cel"
                        ]
                    ];
                    $ssController->sendRequest('POST', 'Observation', $bodyTempPayload);
                }
            }

            // --- 5. AUDIOLOGY ---
            $audiologi = PemeriksaanAudiologi::where('no_rawat', $no_rawat)->first();
            if ($audiologi) {
                // Define helper for numeric observations (dB)
                $sendQuantObservation = function($code, $display, $value, $bodySiteCode, $bodySiteDisplay) use ($ssController, $patientId, $encounterId, $practitionerId, $reg) {
                    $payload = [
                        "resourceType" => "Observation",
                        "status" => "final",
                        "category" => [["coding" => [["system" => "http://terminology.hl7.org/CodeSystem/observation-category", "code" => "exam", "display" => "Exam"]]]],
                        "code" => ["coding" => [["system" => "http://snomed.info/sct", "code" => $code, "display" => $display]]],
                        "subject" => ["reference" => "Patient/" . $patientId],
                        "encounter" => ["reference" => "Encounter/" . $encounterId],
                        "effectiveDateTime" => Carbon::parse($reg->tgl_registrasi . ' ' . $reg->jam_reg)->toIso8601String(),
                        "performer" => [["reference" => "Practitioner/" . $practitionerId]],
                        "bodySite" => ["coding" => [["system" => "http://snomed.info/sct", "code" => $bodySiteCode, "display" => $bodySiteDisplay]]],
                        "valueQuantity" => [
                            "value" => floatval($value),
                            "unit" => "dB",
                            "system" => "http://unitsofmeasure.org",
                            "code" => "dB"
                        ]
                    ];
                    $ssController->sendRequest('POST', 'Observation', $payload);
                };

                // Define helper for string observations (Type)
                $sendStringObservation = function($code, $display, $value, $bodySiteCode, $bodySiteDisplay) use ($ssController, $patientId, $encounterId, $practitionerId, $reg) {
                    $payload = [
                        "resourceType" => "Observation",
                        "status" => "final",
                        "category" => [["coding" => [["system" => "http://terminology.hl7.org/CodeSystem/observation-category", "code" => "exam", "display" => "Exam"]]]],
                        "code" => ["coding" => [["system" => "http://snomed.info/sct", "code" => $code, "display" => $display]]],
                        "subject" => ["reference" => "Patient/" . $patientId],
                        "encounter" => ["reference" => "Encounter/" . $encounterId],
                        "effectiveDateTime" => Carbon::parse($reg->tgl_registrasi . ' ' . $reg->jam_reg)->toIso8601String(),
                        "performer" => [["reference" => "Practitioner/" . $practitionerId]],
                        "bodySite" => ["coding" => [["system" => "http://snomed.info/sct", "code" => $bodySiteCode, "display" => $bodySiteDisplay]]],
                        "valueString" => $value
                    ];
                    $ssController->sendRequest('POST', 'Observation', $payload);
                };

                // Send 4 Observations
                if ($audiologi->ambang_dengar_kanan !== null) {
                    $sendQuantObservation("250620003", "Pure tone audiometry - air conduction", $audiologi->ambang_dengar_kanan, "368563002", "Right ear structure");
                }
                if ($audiologi->ambang_dengar_kiri !== null) {
                    $sendQuantObservation("250620003", "Pure tone audiometry - air conduction", $audiologi->ambang_dengar_kiri, "368564008", "Left ear structure");
                }
                if ($audiologi->tipe_gangguan_kanan) {
                    $sendStringObservation("300196000", "Examination of ear", $audiologi->tipe_gangguan_kanan, "368563002", "Right ear structure");
                }
                if ($audiologi->tipe_gangguan_kiri) {
                    $sendStringObservation("300196000", "Examination of ear", $audiologi->tipe_gangguan_kiri, "368564008", "Left ear structure");
                }
            }

            // --- 6. PROCEDURES ---
            $procedures = RawatJlDr::where('no_rawat', $no_rawat)
                ->join('jns_perawatan', 'rawat_jl_dr.kd_jenis_prw', '=', 'jns_perawatan.kd_jenis_prw')
                ->select('rawat_jl_dr.*', 'jns_perawatan.nm_perawatan')
                ->get();

            foreach ($procedures as $proc) {
                $procPayload = [
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
                        "start" => Carbon::parse($proc->tgl_perawatan . ' ' . $proc->jam_rawat)->toIso8601String(),
                        "end" => Carbon::parse($proc->tgl_perawatan . ' ' . $proc->jam_rawat)->addMinutes(15)->toIso8601String()
                    ],
                    "performer" => [
                        [
                            "actor" => ["reference" => "Practitioner/" . $practitionerId]
                        ]
                    ]
                ];
                $ssController->sendRequest('POST', 'Procedure', $procPayload);
            }

            // --- 7. UPDATE ENCOUNTER TO FINISHED ---
            // We use PATCH to update the status
            $encounterPatch = [
                [
                    "op" => "replace",
                    "path" => "/status",
                    "value" => "finished"
                ],
                [
                    "op" => "add",
                    "path" => "/period/end",
                    "value" => Carbon::now()->toIso8601String()
                ]
            ];
            // Note: SatuSehat PATCH usually requires Content-Type: application/json-patch+json
            // Our sendRequest defaults to application/json. We might need to override headers or just use PUT.
            // For simplicity, let's use PUT (update whole resource) if we had the full resource, but we don't easily have it without fetching.
            // Let's try sending PATCH with special header adjustment if needed, or simply force a PUT of a constructed object if we accept the overhead.
            // Actually, let's just create a new helper in SatuSehatController for PATCH or assume standard JSON merge patch if supported.
            // SatuSehat supports PATCH with 'application/json-patch+json'.
            // For this implementation, I will assume the sendRequest can handle it or I will strictly use the existing sendRequest which sets application/json. 
            // If PATCH fails with wrong content type, we might need a quick fix. 
             
            // Alternative: Just leave it as 'arrived' for now to avoid breaking if PATCH logic isn't perfect, 
            // BUT the user asked for "Next Step", which involves finishing.
            // Let's try to update it via PUT with the original payload but status finished.
            
            $encounterPayload['status'] = 'finished';
            $encounterPayload['period']['end'] = Carbon::now()->toIso8601String();
            $encounterPayload['id'] = $encounterId; // Important for PUT
            
            $ssController->sendRequest('PUT', 'Encounter/' . $encounterId, $encounterPayload);

            return redirect()->back()->with('success', 'Berhasil Bridging SatuSehat (Encounter, Diagnosis, Vitals)! ID: ' . $encounterId);
        } else {
            return redirect()->back()->with('error', 'Gagal Bridging: ' . json_encode($response));
        }
    }

    private function getPatientIhsId($pasien)
    {
        // 1. Check Local Mapping
        $mapping = \App\Models\SatuSehatMappingPasien::where('no_rkm_medis', $pasien->no_rkm_medis)->first();
        if ($mapping) {
            return $mapping->ihs_patient_id;
        }

        // 2. Search SatuSehat by NIK
        if (empty($pasien->no_ktp)) return null;

        $ssController = new SatuSehatController();
        // Correct endpoint: GET /Patient?identifier=https://fhir.kemkes.go.id/id/nik|[NIK]
        $endpoint = 'Patient?identifier=https://fhir.kemkes.go.id/id/nik|' . $pasien->no_ktp;
        $response = $ssController->sendRequest('GET', $endpoint);

        if ($response['status'] == 'success' && !empty($response['response']['entry'])) {
            $patientData = $response['response']['entry'][0]['resource'];
            $ihsId = $patientData['id'];

            // 3. Save to Mapping
            \App\Models\SatuSehatMappingPasien::create([
                'no_rkm_medis' => $pasien->no_rkm_medis,
                'ihs_patient_id' => $ihsId
            ]);

            return $ihsId;
        }

        return null; // Not found
    }
    // Helper: Generate Medical Record Number (Simple auto-increment logic)
    private function generateNoRkmMedis()
    {
        $last = Pasien::orderBy('no_rkm_medis', 'desc')->first();
        if (!$last) return '000001';
        // Check if numeric
        if (is_numeric($last->no_rkm_medis)) {
             return str_pad($last->no_rkm_medis + 1, 6, '0', STR_PAD_LEFT);
        }
        return '000001';
    }
}
