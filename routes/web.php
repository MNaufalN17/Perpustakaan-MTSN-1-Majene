<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\SystemSettingController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\BookItemController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DdcClassController;
use App\Http\Controllers\KepalaSekolahDashboardController;
use App\Http\Controllers\KepalaSekolahReportController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentClassController;
use App\Http\Controllers\UserController;
use App\Models\Book;
use App\Models\BookItem;
use App\Models\FinePayment;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\Member;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', function () {
        $user = auth()->user();

        if ((int) $user->role_id === 3) {
            return redirect()->route('admin.dashboard');
        }

        if ((int) $user->role_id === 2) {
            return redirect()->route('kepala_sekolah.dashboard');
        }

        if ((int) $user->role_id === 1) {
            $totalBooks = BookItem::count();

            $activeMembers = Member::where('status', 'aktif')->count();

            $loansToday = Loan::whereDate('loan_date', today())->count();

            $activeLoans = Loan::whereIn('status', ['aktif', 'terlambat'])->count();

            $overdueLoansCount = Loan::whereIn('status', ['aktif', 'terlambat'])
                ->whereDate('due_date', '<', today())
                ->count();

            $finePerDay = SystemSetting::intValue('fine_per_day', 500);

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

            $popularBooks = Book::withCount([
                'bookItems as borrow_count' => function ($query) {
                    $query->join('loan_items', 'book_items.id', '=', 'loan_items.book_item_id');
                }
            ])
                ->orderByDesc('borrow_count')
                ->limit(4)
                ->get();

            if ($popularBooks->isEmpty()) {
                $popularBooks = Book::latest()
                    ->limit(4)
                    ->get();
            }

            $recentLoans = Loan::with(['member', 'loanItems.bookItem.book'])
                ->latest()
                ->limit(5)
                ->get();

            return view('pustakawan.dashboard', compact(
                'totalBooks',
                'activeMembers',
                'loansToday',
                'activeLoans',
                'estimatedFines',
                'overdueLoansCount',
                'popularBooks',
                'recentLoans'
            ));
        }

        abort(403, 'Role pengguna tidak memiliki akses dashboard.');
    })->name('dashboard');

    Route::middleware('role:3')->group(function () {
        Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
            ->name('admin.dashboard');

        Route::get('/admin/settings', [SystemSettingController::class, 'index'])
            ->name('admin.settings.index');

        Route::put('/admin/settings', [SystemSettingController::class, 'update'])
            ->name('admin.settings.update');

        Route::resource('users', UserController::class);
    });

    Route::middleware('role:2')->group(function () {
        Route::get('/kepala-sekolah/dashboard', [KepalaSekolahDashboardController::class, 'index'])
            ->name('kepala_sekolah.dashboard');

        Route::get('/kepala-sekolah/laporan', [KepalaSekolahReportController::class, 'index'])
            ->name('kepala_sekolah.reports.index');

        Route::get('/kepala-sekolah/laporan/download-pdf', [KepalaSekolahReportController::class, 'downloadPdf'])
            ->name('kepala_sekolah.reports.download');

        Route::get('/kepala-sekolah/laporan-koleksi', [KepalaSekolahReportController::class, 'collections'])
            ->name('kepala_sekolah.reports.collections');

        Route::get('/kepala-sekolah/laporan-koleksi/download-pdf', [KepalaSekolahReportController::class, 'downloadCollectionsPdf'])
            ->name('kepala_sekolah.reports.collections.download');

        Route::get('/kepala-sekolah/laporan-anggota', [KepalaSekolahReportController::class, 'members'])
            ->name('kepala_sekolah.reports.members');

        Route::get('/kepala-sekolah/laporan-anggota/download-pdf', [KepalaSekolahReportController::class, 'downloadMembersPdf'])
            ->name('kepala_sekolah.reports.members.download');

        Route::get('/kepala-sekolah/laporan-rusak-hilang', [KepalaSekolahReportController::class, 'damagedLost'])
            ->name('kepala_sekolah.reports.damaged_lost');

        Route::get('/kepala-sekolah/laporan-rusak-hilang/download-pdf', [KepalaSekolahReportController::class, 'downloadDamagedLostPdf'])
            ->name('kepala_sekolah.reports.damaged_lost.download');

        Route::get('/kepala/dashboard', function () {
            return redirect()->route('kepala_sekolah.dashboar   d');
        })->name('kepala.dashboard');
    });

   Route::middleware('role:1')->group(function () {
    Route::get('/pustakawan/dashboard', function () {
        return redirect()->route('dashboard');
    })->name('pustakawan.dashboard');

    Route::resource('loans', LoanController::class);

    Route::post('/members/quick-store', [MemberController::class, 'quickStore'])
        ->name('members.quick_store');

    Route::resource('books', BookController::class)
        ->except(['index', 'show']);

    Route::delete('/book_items/bulk-destroy', [BookItemController::class, 'bulkDestroy'])
        ->name('book_items.bulk_destroy');

    Route::resource('book_items', BookItemController::class)
        ->except(['index', 'show']);

    Route::resource('members', MemberController::class)
        ->except(['index', 'show']);

    Route::resource('classes', StudentClassController::class);

    Route::resource('categories', CategoryController::class);

    Route::resource('ddc', DdcClassController::class);
});
    Route::middleware('role:1,2')->group(function () {
        Route::resource('books', BookController::class)
            ->only(['index', 'show']);

        Route::resource('book_items', BookItemController::class)
            ->only(['index', 'show']);

        Route::resource('members', MemberController::class)
            ->only(['index', 'show']);
    });

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

require __DIR__ . '/auth.php';
