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

        return response()->json([
            'soap' => $soap,
            'audiologi' => $audiologi,
            'diagnosa' => $diagnosa,
            'procedures' => $procedures
        ]);
    }

    public function index()
    {
        $queue = RegPeriksa::with(['pasien', 'dokter', 'poliklinik'])
            ->whereDate('tgl_registrasi', date('Y-m-d'))
            ->orderBy('jam_reg', 'asc') // FIFO
            ->get();
            
        return view('tht.examination', compact('queue'));
    }

    public function getQueue(Request $request)
    {
        $search = $request->get('q');
        $query = RegPeriksa::with(['pasien', 'dokter', 'poliklinik'])
            ->whereDate('tgl_registrasi', date('Y-m-d'))
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
            'instruksi' => 'required',
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
                    'nip' => $reg->kd_dokter ?? '-'
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

            $reg->update(['stts' => 'Sudah']);
            DB::commit();

            return response()->json(['message' => 'Data berhasil disimpan', 'redirect' => route('pemeriksaan.index')]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving exam: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menyimpan: ' . $e->getMessage()], 500);
        }
    }
}
