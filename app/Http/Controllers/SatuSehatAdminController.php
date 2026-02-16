<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\SatuSehatController;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SatuSehatAdminController extends Controller
{
    public function index()
    {
        $practitioners = \App\Models\Dokter::with('satuSehatMapping')->paginate(10);
        return view('satusehat.practitioner.index', compact('practitioners'));
    }

    public function search(Request $request)
    {
        $request->validate([
            'search_type' => 'required|in:name,nik',
            'search_query' => 'required|string|min:3',
        ]);

        $ssController = new SatuSehatController();
        $result = $ssController->searchPractitioner($request->search_type, $request->search_query);
        
        return response()->json($result);
    }

    public function storePractitioner(Request $request)
    {
        $request->validate([
            'kd_dokter' => 'required|string|max:20|unique:dokter,kd_dokter',
            'nm_dokter' => 'required|string|max:50',
            'nik' => 'required|string|size:16', 
            'ihs_id' => 'required|string|max:36',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        try {
            DB::beginTransaction();

            $dokter = \App\Models\Dokter::create([
                'kd_dokter' => $request->kd_dokter,
                'nm_dokter' => $request->nm_dokter,
                'no_ktp' => $request->nik,
                'status' => '1', 
            ]);

            \App\Models\SatuSehatMappingDokter::updateOrCreate(
                ['kd_dokter' => $request->kd_dokter],
                ['ihs_practitioner_id' => $request->ihs_id]
            );
            User::create([
                'name' => $request->nm_dokter,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'dokter',
                'kd_dokter' => $request->kd_dokter,
            ]);

            DB::commit();

            return redirect()->route('satusehat.practitioner')
                             ->with('success', 'Praktisi berhasil ditambahkan: ' . $request->nm_dokter);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage())->withInput();
        }
    }

    public function updatePractitioner(Request $request, $kd_dokter)
    {
        $request->validate([
            'nm_dokter' => 'required|string|max:50',
            'nik' => 'required|string|size:16',
            'ihs_id' => 'required|string|max:36',
            'email' => 'required|email|unique:users,email,' . $kd_dokter . ',kd_dokter', // Unique ignore current
        ]);

        try {
            DB::beginTransaction();
            
            $dokter = \App\Models\Dokter::findOrFail($kd_dokter);
            $dokter->update([
                'nm_dokter' => $request->nm_dokter,
                'no_ktp' => $request->nik,
            ]);

            \App\Models\SatuSehatMappingDokter::updateOrCreate(
                ['kd_dokter' => $kd_dokter],
                ['ihs_practitioner_id' => $request->ihs_id]
            );

            $user = User::where('kd_dokter', $kd_dokter)->first();
            if ($user) {
                $user->update([
                    'name' => $request->nm_dokter,
                    'email' => $request->email,
                ]);
                
                if ($request->filled('password')) {
                    $user->update(['password' => Hash::make($request->password)]);
                }
            }

            DB::commit();
            return redirect()->route('satusehat.practitioner')
                             ->with('success', 'Data praktisi berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui: ' . $e->getMessage());
        }
    }

    public function destroyPractitioner($kd_dokter)
    {
        try {
            DB::beginTransaction();
            User::where('kd_dokter', $kd_dokter)->delete();
            \App\Models\SatuSehatMappingDokter::where('kd_dokter', $kd_dokter)->delete();
            \App\Models\Dokter::where('kd_dokter', $kd_dokter)->delete();

            DB::commit();
            return redirect()->route('satusehat.practitioner')
                             ->with('success', 'Praktisi berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }
}
