<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
   public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // --- KODE KUSTOM KITA DIMULAI DARI SINI ---
        // Cek role pengguna untuk mengarahkan ke dashboard yang tepat
        if ($request->user()->role_id == 1) {
            // Jika Pustakawan (role_id = 1)
            return redirect()->intended('/pustakawan/dashboard');
        } elseif ($request->user()->role_id == 2) {
            // Jika Kepala Sekolah (role_id = 2)
            return redirect()->intended('/kepala-sekolah/dashboard');
        }
        // --- BATAS KODE KUSTOM ---

        // Ini adalah *fallback* bawaan Breeze jika role tidak ditemukan
        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
