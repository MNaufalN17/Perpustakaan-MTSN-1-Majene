<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookItem;
use App\Models\FinePayment;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\Member;
use App\Models\SystemSetting;
use App\Models\User;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $finePerDay = (int) SystemSetting::getValue('fine_per_day', 500);

        $totalUsers = User::count();

        $activeUsers = User::where(function ($query) {
                $query->where('is_active', true)
                    ->orWhereNull('is_active');
            })
            ->count();

        $inactiveUsers = User::where('is_active', false)->count();

        $totalMembers = Member::count();

        $activeMembers = Member::where('status', 'aktif')->count();

        $totalBooks = Book::count();

        $totalBookItems = BookItem::count();

        $availableBookItems = BookItem::where('status', 'tersedia')->count();

        $borrowedBookItems = BookItem::where('status', 'dipinjam')->count();

        $problematicBookItems = BookItem::whereIn('status', ['rusak', 'hilang', 'nonaktif'])->count();

        $loansToday = Loan::whereDate('loan_date', today())->count();

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

        $estimatedActiveFines = $overdueLoanItems->sum(function ($item) use ($finePerDay) {
            if (!$item->loan || !$item->loan->due_date) {
                return 0;
            }

            $lateDays = Carbon::parse($item->loan->due_date)
                ->startOfDay()
                ->diffInDays(today());

            return $lateDays * $finePerDay;
        });

        $unpaidFines = class_exists(FinePayment::class)
            ? FinePayment::where('payment_status', 'belum dibayar')->sum('amount')
            : 0;

        $estimatedFines = $estimatedActiveFines + $unpaidFines;

        $adminUsers = User::where('role_id', 3)->count();

        $pustakawanUsers = User::where('role_id', 1)->count();

        $kepalaUsers = User::where('role_id', 2)->count();

        $recentLoans = Loan::with(['member', 'loanItems.bookItem.book'])
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'activeUsers',
            'inactiveUsers',
            'totalMembers',
            'activeMembers',
            'totalBooks',
            'totalBookItems',
            'availableBookItems',
            'borrowedBookItems',
            'problematicBookItems',
            'loansToday',
            'activeLoans',
            'overdueLoans',
            'estimatedFines',
            'adminUsers',
            'pustakawanUsers',
            'kepalaUsers',
            'recentLoans'
        ));
    }
}