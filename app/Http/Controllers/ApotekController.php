<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ResepObat;
use App\Models\DetailResepObat;
use App\Models\Databarang;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApotekController extends Controller
{

    public function index(Request $request)
    {
        $status = $request->input('status', 'menunggu');
        
        $query = ResepObat::with([
            'detail.barang',
            'regPeriksa.pasien',
            'dokter',
        ]);

        if ($status && $status !== 'semua') {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('no_resep', 'like', "%{$search}%")
                  ->orWhereHas('regPeriksa.pasien', function ($q2) use ($search) {
                      $q2->where('nm_pasien', 'like', "%{$search}%");
                  });
            });
        }

        $prescriptions = $query->orderByRaw("FIELD(status, 'menunggu', 'diberikan', 'batal')")
            ->orderBy('tgl_resep', 'desc')
            ->orderBy('jam_resep', 'desc')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'menunggu'  => ResepObat::where('status', 'menunggu')->count(),
            'diberikan' => ResepObat::where('status', 'diberikan')
                ->whereDate('tgl_resep', today())->count(),
            'total'     => ResepObat::whereDate('tgl_resep', today())->count(),
        ];

        return view('apotek.index', compact('prescriptions', 'stats', 'status'));
    }

    public function show($no_resep)
    {
        $resep = ResepObat::with([
            'detail.barang',
            'regPeriksa.pasien',
            'dokter',
        ])->findOrFail($no_resep);

        return response()->json([
            'no_resep'   => $resep->no_resep,
            'tgl_resep'  => $resep->tgl_resep,
            'jam_resep'  => $resep->jam_resep,
            'status'     => $resep->status,
            'dokter'     => $resep->dokter->nm_dokter ?? '-',
            'pasien'     => $resep->regPeriksa->pasien->nm_pasien ?? '-',
            'no_rawat'   => $resep->no_rawat,
            'items'      => $resep->detail->map(fn($d) => [
                'id'          => $d->id,
                'kd_brng'     => $d->kd_brng,
                'nama_obat'   => $d->nama_obat,
                'jumlah'      => $d->jumlah,
                'dosis'       => $d->dosis,
                'frekuensi'   => $d->frekuensi,
                'instruksi'   => $d->instruksi,
                'satuan'      => $d->barang->satuan ?? '-',
                'stok'        => $d->barang->stok ?? 0,
            ]),
        ]);
    }

    public function dispense(Request $request, $no_resep)
    {
        $resep = ResepObat::with('detail.barang')->findOrFail($no_resep);

        if ($resep->status !== 'menunggu') {
            return response()->json([
                'success' => false,
                'message' => 'Resep ini sudah diproses sebelumnya.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            foreach ($resep->detail as $item) {
                if ($item->kd_brng && $item->barang) {
                    $item->barang->decrement('stok', max(0, $item->jumlah));
                }
            }

            $resep->update(['status' => 'diberikan']);

            DB::commit();

            Log::info("Resep {$no_resep} diserahkan.", [
                'items' => $resep->detail->count(),
            ]);

            // Bridge to SatuSehat
            try {
                $bridging = new \App\Services\SatuSehatBridgingService();
                $bridging->bridgeMedicationDispense($no_resep);
            } catch (\Exception $e) {
                Log::error("Bridging SatuSehat (Dispense) Failed: " . $e->getMessage());
                // Don't fail the local transaction if bridging fails, just log it.
            }

            return response()->json([
                'success' => true,
                'message' => "Resep {$no_resep} berhasil diserahkan."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal menyerahkan resep {$no_resep}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyerahkan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cancel(Request $request, $no_resep)
    {
        $resep = ResepObat::findOrFail($no_resep);

        if ($resep->status !== 'menunggu') {
            return response()->json([
                'success' => false,
                'message' => 'Resep ini sudah diproses sebelumnya.'
            ], 422);
        }

        $resep->update(['status' => 'batal']);

        return response()->json([
            'success' => true,
            'message' => "Resep {$no_resep} dibatalkan."
        ]);
    }
}
