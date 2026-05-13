<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\BookItemController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\ReportController;

// 1. Saat web dibuka pertama kali, arahkan menurut status login
Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->role_id === 2
            ? redirect()->route('kepsek.dashboard')
            : redirect()->route('pustakawan.dashboard');
    }

    return redirect('/login');
});

// 2. Grup rute yang HANYA bisa diakses setelah pengguna berhasil login
Route::middleware('auth')->group(function () {
    
    // --- RUTE DASHBOARD KEPALA SEKOLAH ---
    Route::get('/kepala-sekolah/dashboard', [ReportController::class, 'dashboard'])
        ->name('kepsek.dashboard');

    // --- RUTE DASHBOARD PUSTAKAWAN ---
    Route::get('/pustakawan/dashboard', function () {
        return view('pustakawan.dashboard');
    })->name('pustakawan.dashboard');

    // --- RUTE RESOURCE (CRUD) UNTUK PUSTAKAWAN ---
    // Route::resource otomatis membuatkan rute untuk index, create, store, show, edit, update, dan destroy
    Route::resource('members', MemberController::class);
    Route::resource('books', BookController::class);
    Route::resource('book_items', BookItemController::class);
    Route::resource('loans', LoanController::class);
    
    // --- RUTE PROFIL BAWAAN BREEZE ---
    // Biarkan kode ini agar fitur ganti password dan profil dari Breeze tidak error
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// 3. Memuat rute autentikasi (Login/Logout) yang disediakan oleh Laravel Breeze
require __DIR__.'/auth.php';