<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $role = $request->input('role', 'pendaftaran');
        
        // Only allow these two roles
        if (!in_array($role, ['pendaftaran', 'apotek'])) {
            $role = 'pendaftaran';
        }

        $staff = User::where('role', $role)
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('satusehat.staff.index', compact('staff', 'role'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role'     => 'required|in:pendaftaran,apotek',
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
        ]);

        $label = $request->role === 'pendaftaran' ? 'Resepsionis' : 'Apoteker';
        return redirect()->route('staff.index', ['role' => $request->role])
                         ->with('success', "$label {$request->name} berhasil ditambahkan.");
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'  => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $id,
            'role'  => 'required|in:pendaftaran,apotek',
        ]);

        $user->update([
            'name'  => $request->name,
            'email' => $request->email,
            'role'  => $request->role,
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->route('staff.index', ['role' => $request->role])
                         ->with('success', "Data {$request->name} berhasil diperbarui.");
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        if (in_array($user->role, ['admin', 'dokter'])) {
            return back()->with('error', 'Tidak dapat menghapus akun admin atau dokter dari sini.');
        }

        $name = $user->name;
        $role = $user->role;
        $user->delete();

        return redirect()->route('staff.index', ['role' => $role])
                         ->with('success', "$name berhasil dihapus.");
    }
}
