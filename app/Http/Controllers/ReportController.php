<?php

namespace App\Http\Controllers;

use App\Models\BookItem;
use App\Models\Member;
use App\Models\Loan;
use App\Models\LoanItem;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Menampilkan Dashboard Kepala Sekolah dengan ringkasan statistik.
     */
    public function dashboard()
    {
        // 1. Menghitung Total Anggota Aktif
        $totalMembers = Member::where('status', 'aktif')->count();

        // 2. Menghitung Total Eksemplar/Fisik Buku
        $totalBookItems = BookItem::count();

        // 3. Menghitung Total Buku yang sedang dipinjam saat ini
        $borrowedBooks = BookItem::where('status', 'dipinjam')->count();

        // 4. Menghitung Total Buku yang bermasalah (rusak atau hilang)
        $problematicBooks = BookItem::whereIn('status', ['rusak', 'hilang'])->count();

        // 5. Menghitung Transaksi Peminjaman yang sedang berjalan (aktif)
        $activeLoans = Loan::where('status', 'aktif')->count();

        // 6. Menghitung Buku yang terlambat dikembalikan (Jatuh tempo terlewati)
        $overdueItems = LoanItem::where('status', 'dipinjam')
            ->whereHas('loan', function($query) {
                // Cek apakah tanggal jatuh tempo lebih kecil (sudah lewat) dari hari ini
                $query->where('due_date', '<', Carbon::now()->toDateString());
            })->count();

        // Mengirimkan semua perhitungan data di atas ke halaman tampilan (view)
        return view('kepala_sekolah.dashboard', compact(
            'totalMembers', 
            'totalBookItems', 
            'borrowedBooks', 
            'problematicBooks', 
            'activeLoans', 
            'overdueItems'
        ));
    }
}