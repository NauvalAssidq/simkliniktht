<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return response()
            ->view('auth.login')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Redirect based on role if needed, or just intended
            // Redirect based on role
            $role = $request->user()->role;
            if ($role === 'admin') {
                return redirect()->route('satusehat.practitioner');
            } elseif ($role === 'dokter') {
                return redirect()->route('pemeriksaan.index');
            } elseif ($role === 'apotek') {
                return redirect()->route('apotek.index');
            } else {
                return redirect()->route('registration.index');
            } 
        }

        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/masuk');
    }
}
