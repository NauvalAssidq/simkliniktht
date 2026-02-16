<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AntriPoli;
use App\Models\RegPeriksa;
use Illuminate\Support\Facades\DB;

class AntrianController extends Controller
{
    public function display()
    {
        return view('tht.display-antrian');
    }

    public function getData()
    {
        // Get currently called patient
        $current = AntriPoli::where('kd_poli', 'U0001') // THT Code
            ->where('tgl_antrian', date('Y-m-d'))
            ->where('status', '1') // 1 = Sedang dipanggil (Adjust based on Khanza logic logic if needed, usually 1 or Panggil)
            ->orderBy('updated_at', 'desc')
            ->first();

        // Get waiting list
        $waiting = AntriPoli::where('kd_poli', 'U0001')
            ->where('tgl_antrian', date('Y-m-d'))
            ->where('status', '0') // 0 = Belum
            ->orderBy('no_antrian', 'asc')
            ->take(5)
            ->get();

        return response()->json([
            'current' => $current,
            'waiting' => $waiting
        ]);
    }

    public function callPatient(Request $request)
    {
        $no_rawat = $request->no_rawat;
        
        // Reset any currently-called patients back to waiting
        AntriPoli::where('kd_poli', 'U0001')
            ->where('tgl_antrian', date('Y-m-d'))
            ->where('status', '1')
            ->update(['status' => '0']);

        // Find and call the patient by no_rawat
        $updated = AntriPoli::where('no_rawat', $no_rawat)
            ->update([
                'status' => '1',
                'updated_at' => now()
            ]);

        if ($updated) {
            // Trigger SatuSehat Status Update (arrived -> in-progress)
            try {
                $bridgingService = new \App\Services\SatuSehatBridgingService();
                $bridgingService->startEncounter($no_rawat);
            } catch (\Exception $e) {
                \Log::error("Error starting SatuSehat encounter for $no_rawat: " . $e->getMessage());
                // Don't fail the UI based on bridging error
            }

            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'error', 'message' => 'Data antrian tidak ditemukan'], 404);
    }

    public function reset()
    {
        // Delete all queue for today
        AntriPoli::where('tgl_antrian', date('Y-m-d'))
            ->where('kd_poli', 'U0001')
            ->delete();

        return response()->json(['status' => 'success']);
    }
}
