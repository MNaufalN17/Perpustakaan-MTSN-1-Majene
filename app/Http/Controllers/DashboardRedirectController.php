<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardRedirectController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($user->role === 'pustakawan') {
            return redirect()->route('pustakawan.dashboard');
        }

        if ($user->role === 'kepala_perpustakaan') {
            return redirect()->route('pustakawan.dashboard');
        }

        return redirect()->route('pustakawan.dashboard');
    }
}