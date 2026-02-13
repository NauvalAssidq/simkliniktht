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
            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'error', 'message' => 'Queue entry not found'], 404);
    }
}
