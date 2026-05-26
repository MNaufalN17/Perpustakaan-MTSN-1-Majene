<?php

namespace App\Http\Controllers;

use App\Models\BookItem;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\Member;

class KepalaSekolahDashboardController extends Controller
{
    public function index()
    {
        if ((int) auth()->user()->role_id !== 2) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $totalMembers = Member::where('status', 'aktif')->count();

        $totalBookItems = BookItem::count();

        $borrowedBooks = BookItem::where('status', 'dipinjam')->count();

        $problematicBooks = BookItem::where(function ($query) {
                $query->whereIn('status', ['rusak', 'hilang', 'nonaktif'])
                    ->orWhereIn('condition', ['rusak ringan', 'rusak berat', 'hilang']);
            })
            ->count();

        $activeLoans = Loan::whereIn('status', ['aktif', 'terlambat'])->count();

        $overdueItems = LoanItem::whereIn('status', ['dipinjam', 'terlambat'])
            ->whereHas('loan', function ($query) {
                $query->whereIn('status', ['aktif', 'terlambat'])
                    ->whereDate('due_date', '<', today());
            })
            ->count();

        return view('kepala_sekolah.dashboard', compact(
            'totalMembers',
            'totalBookItems',
            'borrowedBooks',
            'activeLoans',
            'problematicBooks',
            'overdueItems'
        ));
    }
}