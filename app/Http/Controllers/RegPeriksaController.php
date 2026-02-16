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
            ->orderBy('tgl_registrasi', 'desc')
            ->orderBy('jam_reg', 'desc')
            ->limit(50) 
            ->get();
            
        return view('tht.registration', compact('doctors', 'registrations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nm_pasien' => 'required|string',
            'kd_dokter' => 'required',
        ]);

        DB::beginTransaction();
        try {
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

            $ihsId = $request->input('ihs_id');
            
            // 1. Update/Create Patient Mapping if IHS ID is provided
            if ($ihsId) {
                SatuSehatMappingPasien::updateOrCreate(
                    ['no_rkm_medis' => $no_rkm_medis],
                    ['ihs_patient_id' => $ihsId]
                );
                \Log::info('SatuSehat Mapping Updated for NIK ' . $request->no_ktp . ': ' . $ihsId);
            } else {
                // FALLBACK 1: Check if patient already has mapping
                $existingMap = SatuSehatMappingPasien::where('no_rkm_medis', $no_rkm_medis)->first();
                if ($existingMap) {
                    $ihsId = $existingMap->ihs_patient_id;
                    \Log::info('Using existing SatuSehat Mapping for ' . $no_rkm_medis . ': ' . $ihsId);
                } else {
                    // FALLBACK 2: Auto-lookup to SatuSehat by NIK
                    if (!empty($request->no_ktp) && strlen($request->no_ktp) >= 16) {
                        try {
                            $ssController = new \App\Http\Controllers\SatuSehatController();
                            $ssPatient = $ssController->searchPatientByNik($request->no_ktp);
                            
                            if (isset($ssPatient['found']) && $ssPatient['found']) {
                                $ihsId = $ssPatient['ihs_id'];
                                SatuSehatMappingPasien::create([
                                    'no_rkm_medis' => $no_rkm_medis,
                                    'ihs_patient_id' => $ihsId
                                ]);
                                \Log::info('Auto-lookup Success. Mapped ' . $no_rkm_medis . ' to ' . $ihsId);
                            } else {
                                \Log::warning('Auto-lookup Failed for NIK: ' . $request->no_ktp);
                            }
                        } catch (\Exception $e) {
                            \Log::error('Auto-lookup Error: ' . $e->getMessage());
                        }
                    }
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

            // 4. Create Encounter IMMEDIATELY if IHS ID exists (No more strict sandbox whitelist)
            // Still check doctor mapping though
            
            $doctorMap = SatuSehatMappingDokter::where('kd_dokter', $request->kd_dokter)->first();
            
            if ($ihsId && $doctorMap && $doctorMap->ihs_practitioner_id) {
                
                $ssController = new SatuSehatController();
                
                // Get Location (Hardcoded for demo/sandbox or mapped)
                $locationId = env('SATUSEHAT_LOCATION_ID', 'b017aa54-f1df-460f-9dc0-68153b564fcf'); 

                $encounterPayload = [
                    "resourceType" => "Encounter",
                    "status" => "arrived",
                    "class" => [
                        "system" => "http://terminology.hl7.org/CodeSystem/v3-ActCode",
                        "code" => "AMB",
                        "display" => "ambulatory"
                    ],
                    "identifier" => [
                        [
                            "system" => "http://sys-ids.kemkes.go.id/encounter/" . env('SATUSEHAT_ORGANIZATION_ID'),
                            "value" => $no_rawat
                        ]
                    ],
                    "subject" => ["reference" => "Patient/" . $ihsId, "display" => $request->nm_pasien],
                    "participant" => [
                        [
                            "type" => [["coding" => [["system" => "http://terminology.hl7.org/CodeSystem/v3-ParticipationType", "code" => "ATND", "display" => "attender"]]]],
                            "individual" => ["reference" => "Practitioner/" . $doctorMap->ihs_practitioner_id, "display" => "Dokter"]
                        ]
                    ],
                    "period" => ["start" => Carbon::now()->toIso8601String()],
                    "statusHistory" => [
                        [
                            "status" => "arrived",
                            "period" => ["start" => Carbon::now()->toIso8601String()]
                        ]
                    ],
                    "location" => [
                        [
                            "location" => ["reference" => "Location/" . $locationId, "display" => "Poli THT"]
                        ]
                    ],
                    "serviceProvider" => ["reference" => "Organization/" . env('SATUSEHAT_ORGANIZATION_ID')]
                ];

                \Log::info('SatuSehat Encounter Payload:', $encounterPayload);
                $response = $ssController->sendRequest('POST', 'Encounter', $encounterPayload);
                \Log::info('SatuSehat Encounter Response:', $response);

                if (isset($response['response']['id'])) {
                    SatuSehatEncounter::create([
                        'no_rawat' => $no_rawat,
                        'id_encounter' => $response['response']['id'],
                        'waktu_kirim' => now(),
                        'status' => 'terkirim',
                        'response' => json_encode($response)
                    ]);
                    \Log::info('SatuSehat Encounter Created: ' . $response['response']['id']);
                } else {
                    \Log::error('Failed to create SatuSehat Encounter', ['response' => $response]);
                }
            } else {
                \Log::warning('Skipping SatuSehat Encounter: Missing IHS ID (Patient or Doctor). Patient: ' . ($ihsId ?? 'None') . ', Doctor: ' . ($doctorMap ? 'Mapped' : 'Not Mapped'));
            }

            DB::commit();

            return redirect()->back()->with('success', 'Registration Successful! ' . ($ihsId ? 'Connected to SatuSehat.' : ''));

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Registration Error: ' . $e->getMessage());
            return back()->with('error', 'Registration Failed: ' . $e->getMessage());
        }
    }

    // Restore missing helper
    private function generateNoRkmMedis()
    {
        $last = Pasien::orderBy('no_rkm_medis', 'desc')->first();
        if (!$last) return '000001';
        if (is_numeric($last->no_rkm_medis)) {
             return str_pad($last->no_rkm_medis + 1, 6, '0', STR_PAD_LEFT);
        }
        return '000001';
    }

    public function bridging($no_rawat)
    {
        try {
            $bridgingService = new \App\Services\SatuSehatBridgingService();
            $result = $bridgingService->bridgeEncounter($no_rawat);

            if ($result['success']) {
                return redirect()->back()->with('success', $result['message']);
            } else {
                return redirect()->back()->with('error', 'Gagal Bridging: ' . $result['message']);
            }

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal Bridging: ' . $e->getMessage());
        }
    }
}
