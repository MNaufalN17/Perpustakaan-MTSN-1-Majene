<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookItem;
use App\Models\Loan;
use App\Models\Member;
use App\Models\SystemSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class KepalaSekolahReportController extends Controller
{
    public function index(Request $request)
    {
        if ((int) auth()->user()->role_id !== 2) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $data = $this->loanReportData($request);

        return view('kepala_sekolah.reports.index', $data);
    }

    public function downloadPdf(Request $request)
    {
        if ((int) auth()->user()->role_id !== 2) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $data = $this->loanReportData($request);

        $fileName = 'laporan-peminjaman-' . $data['startDate'] . '-sampai-' . $data['endDate'] . '.pdf';

        $pdf = Pdf::loadView('kepala_sekolah.reports.pdf', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->download($fileName);
    }

    public function collections(Request $request)
    {
        if ((int) auth()->user()->role_id !== 2) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $data = $this->collectionReportData($request);

        return view('kepala_sekolah.reports.collections.index', $data);
    }

    public function downloadCollectionsPdf(Request $request)
    {
        if ((int) auth()->user()->role_id !== 2) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $data = $this->collectionReportData($request);

        $fileName = 'laporan-koleksi-buku-' . now()->format('Y-m-d-His') . '.pdf';

        $pdf = Pdf::loadView('kepala_sekolah.reports.collections.pdf', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->download($fileName);
    }

    public function members(Request $request)
    {
        if ((int) auth()->user()->role_id !== 2) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $data = $this->memberReportData($request);

        return view('kepala_sekolah.reports.members.index', $data);
    }

    public function downloadMembersPdf(Request $request)
    {
        if ((int) auth()->user()->role_id !== 2) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $data = $this->memberReportData($request);

        $fileName = 'laporan-anggota-' . now()->format('Y-m-d-His') . '.pdf';

        $pdf = Pdf::loadView('kepala_sekolah.reports.members.pdf', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->download($fileName);
    }

    public function damagedLost(Request $request)
    {
        if ((int) auth()->user()->role_id !== 2) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $data = $this->damagedLostReportData($request);

        return view('kepala_sekolah.reports.damaged_lost.index', $data);
    }

    public function downloadDamagedLostPdf(Request $request)
    {
        if ((int) auth()->user()->role_id !== 2) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $data = $this->damagedLostReportData($request);

        $fileName = 'laporan-buku-rusak-hilang-' . now()->format('Y-m-d-His') . '.pdf';

        $pdf = Pdf::loadView('kepala_sekolah.reports.damaged_lost.pdf', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->download($fileName);
    }

    private function loanReportData(Request $request): array
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $schoolName = SystemSetting::getValue('school_name', 'MTsN 1 Majene');
        $libraryName = SystemSetting::getValue('library_name', 'Sistem Informasi Perpustakaan');

        $finePerDay = (int) SystemSetting::getValue('fine_per_day', 500);

        $loans = Loan::with([
                'member.studentClass',
                'loanItems.bookItem.book',
            ])
            ->whereDate('loan_date', '>=', $startDate)
            ->whereDate('loan_date', '<=', $endDate)
            ->latest()
            ->get();

        $totalLoans = $loans->count();

        $activeLoans = $loans->whereIn('status', ['aktif', 'terlambat'])->count();

        $completedLoans = $loans->where('status', 'selesai')->count();

        $overdueLoans = $loans->filter(function ($loan) {
            return in_array($loan->status, ['aktif', 'terlambat'], true)
                && $loan->due_date
                && Carbon::parse($loan->due_date)->startOfDay()->lt(today());
        })->count();

        $totalLoanItems = $loans->sum(function ($loan) {
            return $loan->loanItems->count();
        });

        $totalFines = $loans->sum(function ($loan) use ($finePerDay) {
            $storedFine = (int) $loan->loanItems->sum('fine_amount');

            if (!in_array($loan->status, ['aktif', 'terlambat'], true) || !$loan->due_date) {
                return $storedFine;
            }

            $dueDate = Carbon::parse($loan->due_date)->startOfDay();

            if (!$dueDate->lt(today())) {
                return $storedFine;
            }

            $lateDays = (int) $dueDate->diffInDays(today());

            $activeItemCount = $loan->loanItems
                ->whereIn('status', ['dipinjam', 'terlambat'])
                ->count();

            return $storedFine + ($lateDays * $finePerDay * $activeItemCount);
        });

        $totalMembers = Member::where('status', 'aktif')->count();

        $totalBooks = Book::count();

        $totalBookItems = BookItem::count();

        $availableBooks = BookItem::where('status', 'tersedia')->count();

        $borrowedBooks = BookItem::where('status', 'dipinjam')->count();

        $problematicBooks = BookItem::where(function ($query) {
                $query->whereIn('status', ['rusak', 'hilang', 'nonaktif'])
                    ->orWhereIn('condition', ['rusak ringan', 'rusak berat', 'hilang']);
            })
            ->count();

        return compact(
            'schoolName',
            'libraryName',
            'startDate',
            'endDate',
            'finePerDay',
            'loans',
            'totalLoans',
            'activeLoans',
            'completedLoans',
            'overdueLoans',
            'totalLoanItems',
            'totalFines',
            'totalMembers',
            'totalBooks',
            'totalBookItems',
            'availableBooks',
            'borrowedBooks',
            'problematicBooks'
        );
    }

    private function collectionReportData(Request $request): array
    {
        $schoolName = SystemSetting::getValue('school_name', 'MTsN 1 Majene');
        $libraryName = SystemSetting::getValue('library_name', 'Sistem Informasi Perpustakaan');

        $keyword = trim((string) $request->input('keyword', ''));

        $books = Book::with(['category', 'ddcClass'])
            ->withCount([
                'bookItems as total_items_count',
                'bookItems as available_items_count' => function ($query) {
                    $query->where('status', 'tersedia');
                },
                'bookItems as borrowed_items_count' => function ($query) {
                    $query->where('status', 'dipinjam');
                },
                'bookItems as damaged_items_count' => function ($query) {
                    $query->where(function ($subQuery) {
                        $subQuery->where('status', 'rusak')
                            ->orWhereIn('condition', ['rusak ringan', 'rusak berat']);
                    });
                },
                'bookItems as lost_items_count' => function ($query) {
                    $query->where(function ($subQuery) {
                        $subQuery->where('status', 'hilang')
                            ->orWhere('condition', 'hilang');
                    });
                },
            ])
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($subQuery) use ($keyword) {
                    $subQuery->where('title', 'like', "%{$keyword}%")
                        ->orWhere('author', 'like', "%{$keyword}%")
                        ->orWhere('publisher', 'like', "%{$keyword}%")
                        ->orWhereHas('category', function ($categoryQuery) use ($keyword) {
                            $categoryQuery->where('name', 'like', "%{$keyword}%");
                        })
                        ->orWhereHas('ddcClass', function ($ddcQuery) use ($keyword) {
                            $ddcQuery->where('code', 'like', "%{$keyword}%")
                                ->orWhere('name', 'like', "%{$keyword}%");
                        });
                });
            })
            ->orderBy('title')
            ->get();

        $totalBooks = $books->count();
        $totalBookItems = $books->sum('total_items_count');
        $availableBooks = $books->sum('available_items_count');
        $borrowedBooks = $books->sum('borrowed_items_count');
        $damagedBooks = $books->sum('damaged_items_count');
        $lostBooks = $books->sum('lost_items_count');

        return compact(
            'schoolName',
            'libraryName',
            'keyword',
            'books',
            'totalBooks',
            'totalBookItems',
            'availableBooks',
            'borrowedBooks',
            'damagedBooks',
            'lostBooks'
        );
    }

    private function memberReportData(Request $request): array
    {
        $schoolName = SystemSetting::getValue('school_name', 'MTsN 1 Majene');
        $libraryName = SystemSetting::getValue('library_name', 'Sistem Informasi Perpustakaan');

        $keyword = trim((string) $request->input('keyword', ''));
        $memberType = trim((string) $request->input('member_type', ''));
        $status = trim((string) $request->input('status', ''));
        $classFilter = trim((string) $request->input('class_filter', ''));

        $classOptions = Member::with('studentClass')
            ->get()
            ->map(function ($member) {
                return $member->studentClass->class_name ?? 'Guru/Staff';
            })
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $members = Member::with('studentClass')
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($subQuery) use ($keyword) {
                    $subQuery->where('name', 'like', "%{$keyword}%")
                        ->orWhere('member_code', 'like', "%{$keyword}%")
                        ->orWhere('nis_nip', 'like', "%{$keyword}%")
                        ->orWhere('phone', 'like', "%{$keyword}%")
                        ->orWhereHas('studentClass', function ($classQuery) use ($keyword) {
                            $classQuery->where('class_name', 'like', "%{$keyword}%");
                        });
                });
            })
            ->when($memberType !== '', function ($query) use ($memberType) {
                $query->where('member_type', $memberType);
            })
            ->when($status !== '', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderBy('name')
            ->get();

        if ($classFilter !== '') {
            $members = $members->filter(function ($member) use ($classFilter) {
                $className = $member->studentClass->class_name ?? 'Guru/Staff';

                return $className === $classFilter;
            })->values();
        }

        $totalMembers = $members->count();
        $activeMembers = $members->where('status', 'aktif')->count();
        $inactiveMembers = $members->where('status', 'nonaktif')->count();
        $studentMembers = $members->where('member_type', 'siswa')->count();
        $teacherMembers = $members->where('member_type', 'guru')->count();

        $classRecaps = $members
            ->groupBy(function ($member) {
                return $member->studentClass->class_name ?? 'Guru/Staff';
            })
            ->map(function ($items, $className) {
                return [
                    'class_name' => $className,
                    'total' => $items->count(),
                    'active' => $items->where('status', 'aktif')->count(),
                    'inactive' => $items->where('status', 'nonaktif')->count(),
                    'students' => $items->where('member_type', 'siswa')->count(),
                    'teachers' => $items->where('member_type', 'guru')->count(),
                ];
            })
            ->sortBy('class_name')
            ->values();

        return compact(
            'schoolName',
            'libraryName',
            'keyword',
            'memberType',
            'status',
            'classFilter',
            'classOptions',
            'members',
            'totalMembers',
            'activeMembers',
            'inactiveMembers',
            'studentMembers',
            'teacherMembers',
            'classRecaps'
        );
    }

    private function damagedLostReportData(Request $request): array
    {
        $schoolName = SystemSetting::getValue('school_name', 'MTsN 1 Majene');
        $libraryName = SystemSetting::getValue('library_name', 'Sistem Informasi Perpustakaan');

        $keyword = trim((string) $request->input('keyword', ''));
        $status = trim((string) $request->input('status', ''));
        $condition = trim((string) $request->input('condition', ''));

        $items = BookItem::with(['book.category', 'book.ddcClass'])
            ->where(function ($query) {
                $query->whereIn('status', ['rusak', 'hilang', 'nonaktif'])
                    ->orWhereIn('condition', ['rusak ringan', 'rusak berat', 'hilang']);
            })
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($subQuery) use ($keyword) {
                    $subQuery->where('item_code', 'like', "%{$keyword}%")
                        ->orWhere('classification_code', 'like', "%{$keyword}%")
                        ->orWhere('author_code', 'like', "%{$keyword}%")
                        ->orWhere('title_code', 'like', "%{$keyword}%")
                        ->orWhereHas('book', function ($bookQuery) use ($keyword) {
                            $bookQuery->where('title', 'like', "%{$keyword}%")
                                ->orWhere('author', 'like', "%{$keyword}%")
                                ->orWhere('publisher', 'like', "%{$keyword}%");
                        });
                });
            })
            ->when($status !== '', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($condition !== '', function ($query) use ($condition) {
                $query->where('condition', $condition);
            })
            ->latest()
            ->get();

        $totalProblemItems = $items->count();

        $lightDamagedItems = $items->where('condition', 'rusak ringan')->count();

        $heavyDamagedItems = $items->where('condition', 'rusak berat')->count();

        $lostItems = $items->filter(function ($item) {
            return $item->status === 'hilang' || $item->condition === 'hilang';
        })->count();

        $inactiveItems = $items->where('status', 'nonaktif')->count();

        return compact(
            'schoolName',
            'libraryName',
            'keyword',
            'status',
            'condition',
            'items',
            'totalProblemItems',
            'lightDamagedItems',
            'heavyDamagedItems',
            'lostItems',
            'inactiveItems'
        );
    }
}