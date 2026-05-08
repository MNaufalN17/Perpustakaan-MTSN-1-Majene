<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Menampilkan halaman form login
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Memproses data login
    public function login(Request $request)
    {
        // 1. Validasi inputan (wajib diisi)
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // 2. Coba melakukan login
        if (Auth::attempt($credentials)) {
            // Jika sukses, buat sesi baru agar aman
            $request->session()->regenerate();

            // 3. Cek role pengguna untuk mengarahkan ke dashboard yang tepat
            // Asumsi: role_id 1 = pustakawan, role_id 2 = kepala_sekolah
            if (Auth::user()->role_id == 1) {
                return redirect()->intended('/pustakawan/dashboard');
            } elseif (Auth::user()->role_id == 2) {
                return redirect()->intended('/kepala-sekolah/dashboard');
            }
        }

        // Jika gagal login, kembalikan ke halaman login dengan pesan error
        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    // Memproses logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}