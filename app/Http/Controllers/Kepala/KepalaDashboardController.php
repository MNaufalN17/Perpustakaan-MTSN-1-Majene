<?php

namespace App\Http\Controllers\Kepala;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookItem;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\Member;
use App\Models\SystemSetting;
use Carbon\Carbon;

class KepalaDashboardController extends Controller
{
    public function index()
    {
        if (!in_array((int) auth()->user()->role_id, [2, 3], true)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $finePerDay = (int) SystemSetting::getValue('fine_per_day', 500);

        $totalBooks = Book::count();
        $totalBookItems = BookItem::count();
        $availableBooks = BookItem::where('status', 'tersedia')->count();
        $borrowedBooks = BookItem::where('status', 'dipinjam')->count();

        $problematicBooks = BookItem::whereIn('status', ['rusak', 'hilang', 'nonaktif'])
            ->orWhereIn('condition', ['rusak ringan', 'rusak berat', 'hilang'])
            ->count();

        $activeMembers = Member::where('status', 'aktif')->count();

        $activeLoans = Loan::whereIn('status', ['aktif', 'terlambat'])->count();

        $overdueLoans = Loan::whereIn('status', ['aktif', 'terlambat'])
            ->whereDate('due_date', '<', today())
            ->count();

        $overdueLoanItems = LoanItem::with('loan')
            ->whereIn('status', ['dipinjam', 'terlambat'])
            ->whereHas('loan', function ($query) {
                $query->whereIn('status', ['aktif', 'terlambat'])
                    ->whereDate('due_date', '<', today());
            })
            ->get();

        $estimatedFines = $overdueLoanItems->sum(function ($item) use ($finePerDay) {
            if (!$item->loan || !$item->loan->due_date) {
                return 0;
            }

            $lateDays = Carbon::parse($item->loan->due_date)
                ->startOfDay()
                ->diffInDays(today());

            return $lateDays * $finePerDay;
        });

        $recentLoans = Loan::with(['member', 'loanItems.bookItem.book'])
            ->latest()
            ->limit(6)
            ->get();

        return view('kepala.dashboard', compact(
            'totalBooks',
            'totalBookItems',
            'availableBooks',
            'borrowedBooks',
            'problematicBooks',
            'activeMembers',
            'activeLoans',
            'overdueLoans',
            'estimatedFines',
            'recentLoans'
        ));
    }
}