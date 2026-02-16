<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RegPeriksa;
use App\Models\Penyakit;
use App\Models\JnsPerawatan;
use App\Models\PemeriksaanRalan;
use App\Models\DiagnosaPasien;
use App\Models\RawatJlDr;
use App\Models\PemeriksaanAudiologi;
use App\Models\PemeriksaanRalanLaterality;
use App\Models\ResepObat;
use App\Models\DetailResepObat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PemeriksaanController extends Controller
{
    public function getExamData(Request $request)
    {
        $no_rawat = $request->query('no_rawat');
        $soap = PemeriksaanRalan::find($no_rawat);
        $audiologi = PemeriksaanAudiologi::find($no_rawat);
        
        $diagnosa = DiagnosaPasien::where('diagnosa_pasien.no_rawat', $no_rawat)
            ->join('penyakit', 'diagnosa_pasien.kd_penyakit', '=', 'penyakit.kd_penyakit')
            ->leftJoin('pemeriksaan_ralan_laterality', function($join) use ($no_rawat) {
                $join->on('diagnosa_pasien.kd_penyakit', '=', 'pemeriksaan_ralan_laterality.kode_brng')
                     ->where('pemeriksaan_ralan_laterality.no_rawat', '=', $no_rawat)
                     ->where('pemeriksaan_ralan_laterality.jenis', '=', 'Diagnosis');
            })
            ->select('diagnosa_pasien.*', 'penyakit.nm_penyakit', 'pemeriksaan_ralan_laterality.sisi as laterality')
            ->get();
            
        $procedures = RawatJlDr::where('rawat_jl_dr.no_rawat', $no_rawat)
            ->join('jns_perawatan', 'rawat_jl_dr.kd_jenis_prw', '=', 'jns_perawatan.kd_jenis_prw')
            ->leftJoin('pemeriksaan_ralan_laterality', function($join) use ($no_rawat) {
                $join->on('rawat_jl_dr.kd_jenis_prw', '=', 'pemeriksaan_ralan_laterality.kode_brng')
                     ->where('pemeriksaan_ralan_laterality.no_rawat', '=', $no_rawat)
                     ->where('pemeriksaan_ralan_laterality.jenis', '=', 'Procedure');
            })
            ->select('rawat_jl_dr.*', 'jns_perawatan.nm_perawatan', 'jns_perawatan.total_byr', 'pemeriksaan_ralan_laterality.sisi as laterality')
            ->get();

        $encounter = \App\Models\SatuSehatEncounter::where('no_rawat', $no_rawat)->first();

        return response()->json([
            'soap' => $soap,
            'audiologi' => $audiologi,
            'diagnosa' => $diagnosa,
            'procedures' => $procedures,
            'encounter' => $encounter
        ]);
    }

    public function index()
    {
        $kd_dokter = auth()->user()->kd_dokter;

        $queue = RegPeriksa::with(['pasien.satuSehatMapping', 'dokter', 'poliklinik', 'antrian'])
            ->whereDate('tgl_registrasi', date('Y-m-d'))
            ->where('stts', 'Belum')
            ->when($kd_dokter, function($q) use ($kd_dokter) {
                $q->where('kd_dokter', $kd_dokter);
            })
            ->orderBy('jam_reg', 'asc')
            ->get();
            
        $doctors = \App\Models\Dokter::where('status', '1')->get();
        return view('tht.examination', compact('queue', 'doctors'));
    }

    public function getQueue(Request $request)
    {
        $search = $request->get('q');
        $kd_dokter = auth()->user()->kd_dokter;

        $query = RegPeriksa::with(['pasien.satuSehatMapping', 'dokter', 'poliklinik', 'antrian'])
            ->whereDate('tgl_registrasi', date('Y-m-d'))
            ->where('stts', 'Belum')
            ->when($kd_dokter, function($q) use ($kd_dokter) {
                $q->where('kd_dokter', $kd_dokter);
            })
            ->orderBy('jam_reg', 'asc');

        if ($search) {
            $query->whereHas('pasien', function($q) use ($search) {
                $q->where('nm_pasien', 'like', "%$search%")
                  ->orWhere('no_rkm_medis', 'like', "%$search%");
            });
        }

        $queue = $query->get();
        return response()->json($queue);
    }

    public function getMedicalHistory(Request $request)
    {
        $noRkmMedis = $request->get('no_rkm_medis');
        $excludeNoRawat = $request->get('exclude'); // current visit

        $visits = RegPeriksa::with(['dokter', 'poliklinik'])
            ->where('no_rkm_medis', $noRkmMedis)
            ->where('stts', '!=', 'Belum')
            ->when($excludeNoRawat, fn($q) => $q->where('no_rawat', '!=', $excludeNoRawat))
            ->orderBy('tgl_registrasi', 'desc')
            ->limit(10)
            ->get()
            ->map(function($visit) {
                $diagnoses = DiagnosaPasien::where('no_rawat', $visit->no_rawat)
                    ->get(['kd_penyakit', 'nm_penyakit']);

                $procedures = RawatJlDr::where('no_rawat', $visit->no_rawat)
                    ->get(['kd_jenis_prw', 'nm_perawatan']);

                $soap = PemeriksaanRalan::where('no_rawat', $visit->no_rawat)->first();

                $resep = ResepObat::with('detail')->where('no_rawat', $visit->no_rawat)->first();

                return [
                    'no_rawat'   => $visit->no_rawat,
                    'tgl'        => $visit->tgl_registrasi,
                    'dokter'     => $visit->dokter->nm_dokter ?? '-',
                    'poli'       => $visit->poliklinik->nm_poli ?? '-',
                    'keluhan'    => $soap->keluhan ?? null,
                    'penilaian'  => $soap->penilaian ?? null,
                    'diagnosa'   => $diagnoses,
                    'prosedur'   => $procedures,
                    'obat'       => $resep ? $resep->detail->map(fn($d) => [
                        'nama' => $d->nama_obat ?? $d->kd_brng,
                        'jumlah' => $d->jumlah,
                    ]) : [],
                ];
            });

        return response()->json($visits);
    }

    public function searchDiagnosis(Request $request)
    {
        $search = $request->get('q');
        $data = Penyakit::where('kd_penyakit', 'like', "%$search%")
            ->orWhere('nm_penyakit', 'like', "%$search%")
            ->limit(20)
            ->get();
        return response()->json($data);
    }

    public function searchProcedures(Request $request)
    {
        $search = $request->get('q');
        $data = JnsPerawatan::where('kd_jenis_prw', 'like', "%$search%")
            ->orWhere('nm_perawatan', 'like', "%$search%")
            ->limit(20)
            ->get();
        return response()->json($data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'no_rawat' => 'required|exists:reg_periksa,no_rawat',
            'keluhan' => 'required',
            'penilaian' => 'required',
            'instruksi' => 'nullable',
        ]);

        DB::beginTransaction();
        try {
            Log::info('Saving Pemeriksaan Data', $request->all());

            $reg = RegPeriksa::where('no_rawat', $request->no_rawat)->first();

            PemeriksaanRalan::updateOrCreate(
                ['no_rawat' => $request->no_rawat],
                [
                    'tgl_perawatan' => date('Y-m-d'),
                    'jam_rawat' => date('H:i:s'),
                    'keluhan' => $request->keluhan,
                    'pemeriksaan' => $request->pemeriksaan ?? '-',
                    'penilaian' => $request->penilaian,
                    'instruksi' => $request->instruksi,
                    'rtl' => $request->instruksi,
                    'suhu_tubuh' => $request->suhu_tubuh ?? '-',
                    'tensi' => $request->tensi ?? '-',
                    'nip' => (!empty($request->kd_dokter)) ? $request->kd_dokter : $reg->kd_dokter
                ]
            );

            // Save Audiology Data
            $audiologi = $request->input('audiologi');
            if ($audiologi && is_array($audiologi)) {
                PemeriksaanAudiologi::updateOrCreate(
                    ['no_rawat' => $request->no_rawat],
                    [
                        'tipe_gangguan_kanan' => $audiologi['tipe_gangguan_kanan'] ?? null,
                        'ambang_dengar_kanan' => $audiologi['ambang_dengar_kanan'] ?? null,
                        'tipe_gangguan_kiri' => $audiologi['tipe_gangguan_kiri'] ?? null,
                        'ambang_dengar_kiri' => $audiologi['ambang_dengar_kiri'] ?? null,
                    ]
                );
            }

            if ($request->has('diagnosa')) {
                foreach ($request->diagnosa as $diag) {
                    DiagnosaPasien::firstOrCreate(
                        [
                            'no_rawat' => $request->no_rawat,
                            'kd_penyakit' => $diag['code']
                        ],
                        [
                            'status' => 'Ralan',
                            'prioritas' => 1,
                            'status_penyakit' => 'Baru'
                        ]
                    );

                    // Save Laterality
                    if (isset($diag['laterality'])) {
                         PemeriksaanRalanLaterality::updateOrCreate(
                            [
                                'no_rawat' => $request->no_rawat,
                                'kode_brng' => $diag['code'],
                                'jenis' => 'Diagnosis'
                            ],
                            ['sisi' => $diag['laterality']]
                        );
                    }
                }
            }

            if ($request->has('procedures')) {
                foreach ($request->procedures as $proc) {
                    $procMaster = JnsPerawatan::find($proc['code']);
                    if ($procMaster) {
                         RawatJlDr::create([
                            'no_rawat' => $request->no_rawat,
                            'kd_jenis_prw' => $proc['code'],
                            'kd_dokter' => $reg->kd_dokter,
                            'tgl_perawatan' => date('Y-m-d'),
                            'jam_rawat' => date('H:i:s'),
                            'material' => $procMaster->material,
                            'bhp' => $procMaster->bhp,
                            'tarif_tindakandr' => $procMaster->tarif_tindakandr,
                            'kso' => $procMaster->kso,
                            'menejemen' => $procMaster->menejemen,
                            'biaya_rawat' => $procMaster->total_byr
                        ]);
                    }
                    
                     // Save Laterality
                    if (isset($proc['laterality'])) {
                         PemeriksaanRalanLaterality::updateOrCreate(
                            [
                                'no_rawat' => $request->no_rawat,
                                'kode_brng' => $proc['code'],
                                'jenis' => 'Procedure'
                            ],
                            ['sisi' => $proc['laterality']]
                        );
                    }
                }
            }

            // Save Prescription (Resep Obat)
            if ($request->has('resep') && is_array($request->resep) && count($request->resep) > 0) {
                // auto-increment no_resep: RSP-YYYYMMDD-NNN
                $today = date('Ymd');
                $lastResep = ResepObat::where('no_resep', 'like', "RSP-{$today}-%")
                    ->orderBy('no_resep', 'desc')
                    ->first();
                $seq = $lastResep 
                    ? (intval(substr($lastResep->no_resep, -3)) + 1) 
                    : 1;
                $noResep = "RSP-{$today}-" . str_pad($seq, 3, '0', STR_PAD_LEFT);

                $resep = ResepObat::create([
                    'no_resep'  => $noResep,
                    'no_rawat'  => $request->no_rawat,
                    'kd_dokter' => (!empty($request->kd_dokter)) ? $request->kd_dokter : $reg->kd_dokter,
                    'tgl_resep' => date('Y-m-d'),
                    'jam_resep' => date('H:i:s'),
                    'status'    => 'menunggu',
                ]);

                foreach ($request->resep as $item) {
                    DetailResepObat::create([
                        'no_resep'        => $noResep,
                        'kd_brng'         => $item['kd_brng'] ?? null,
                        'nm_obat_manual'  => $item['nm_brng'] ?? null,
                        'jumlah'          => $item['jumlah'] ?? 1,
                        'dosis'           => $item['dosis'] ?? null,
                        'frekuensi'       => $item['frekuensi'] ?? null,
                        'instruksi'       => $item['instruksi'] ?? null,
                    ]);
                }
            }

            $reg->update(['stts' => 'Sudah']);
            DB::commit();

            // --- AUTO BRIDGE TO SATUSEHAT ---
            try {
                $bridgingService = new \App\Services\SatuSehatBridgingService();
                $bridgeResult = $bridgingService->bridgeEncounter($request->no_rawat);
                
                if ($bridgeResult['success']) {
                    return response()->json([
                        'message' => 'Pemeriksaan Selesai & Terkirim ke SatuSehat!', 
                        'redirect' => route('pemeriksaan.index')
                    ]);
                } else {
                    return response()->json([
                        'message' => 'Pemeriksaan Selesai, tapi Gagal Bridging: ' . $bridgeResult['message'], 
                        'redirect' => route('pemeriksaan.index')
                    ]);
                }
            } catch (\Exception $ex) {
                return response()->json([
                    'message' => 'Pemeriksaan Selesai, Bridging Error: ' . $ex->getMessage(), 
                    'redirect' => route('pemeriksaan.index')
                ]);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving exam: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menyimpan: ' . $e->getMessage()], 500);
        }
    }
}
