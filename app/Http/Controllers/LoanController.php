<?php

namespace App\Http\Controllers;

use App\Models\BookItem;
use App\Models\FinePayment;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\Member;
use App\Models\StudentClass;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class LoanController extends Controller
{
    public function index(Request $request)
    {
        if (! auth()->check() || (int) auth()->user()->role_id !== 1) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $this->syncOverdueLoans();

        $keyword = trim((string) $request->input('keyword', ''));
        $status = trim((string) $request->input('status', ''));

        $loans = Loan::with([
                'member.studentClass',
                'studentClass',
                'loanItems.bookItem.book',
            ])
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($subQuery) use ($keyword) {
                    $subQuery->where('loan_code', 'like', "%{$keyword}%")
                        ->orWhereHas('member', function ($memberQuery) use ($keyword) {
                            $memberQuery->where('name', 'like', "%{$keyword}%")
                                ->orWhere('nis_nip', 'like', "%{$keyword}%")
                                ->orWhere('member_code', 'like', "%{$keyword}%");
                        })
                        ->orWhereHas('loanItems.bookItem.book', function ($bookQuery) use ($keyword) {
                            $bookQuery->where('title', 'like', "%{$keyword}%")
                                ->orWhere('author', 'like', "%{$keyword}%");
                        });
                });
            })
            ->when($status !== '', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pustakawan.loans.index', compact(
            'loans',
            'keyword',
            'status'
        ));
    }

    public function create()
    {
        if (! auth()->check() || (int) auth()->user()->role_id !== 1) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $this->syncOverdueLoans();

        $members = Member::with('studentClass')
            ->where('status', 'aktif')
            ->orderBy('name')
            ->get();

        $borrowedBookItemIds = $this->activeBorrowedBookItemIds();

        $bookItems = BookItem::with('book')
            ->orderBy('item_code')
            ->get();

        $studentClasses = StudentClass::orderBy('level')
            ->orderBy('class_name')
            ->get();

        $classes = $studentClasses;

        $normalMaxLoanItems = $this->settingInt('max_normal_loan_items', 3, 1, 200);
        $loanDurationDays = $this->settingInt('loan_duration_days', 7, 1, 365);

        return view('pustakawan.loans.create', compact(
            'members',
            'bookItems',
            'borrowedBookItemIds',
            'studentClasses',
            'classes',
            'normalMaxLoanItems',
            'loanDurationDays'
        ));
    }

    public function downloadReport(Request $request)
    {
        if (! auth()->check() || (int) auth()->user()->role_id !== 1) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'status' => ['nullable', 'in:aktif,terlambat,selesai,batal'],
            'loan_type' => ['nullable', 'in:regular,class_bulk'],
        ], [
            'start_date.date' => 'Tanggal awal laporan tidak valid.',
            'end_date.date' => 'Tanggal akhir laporan tidak valid.',
            'status.in' => 'Status laporan tidak valid.',
            'loan_type.in' => 'Jenis peminjaman laporan tidak valid.',
        ]);

        $this->syncOverdueLoans();

        $startDate = $validated['start_date'] ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $validated['end_date'] ?? now()->format('Y-m-d');

        if (Carbon::parse($startDate)->startOfDay()->gt(Carbon::parse($endDate)->startOfDay())) {
            throw ValidationException::withMessages([
                'start_date' => 'Tanggal awal laporan tidak boleh setelah tanggal akhir.',
            ]);
        }

        $loans = Loan::with([
                'member.studentClass',
                'studentClass',
                'handler',
                'loanItems.bookItem.book',
            ])
            ->whereDate('loan_date', '>=', $startDate)
            ->whereDate('loan_date', '<=', $endDate)
            ->when(! empty($validated['status']), function ($query) use ($validated) {
                $query->where('status', $validated['status']);
            })
            ->when(! empty($validated['loan_type']), function ($query) use ($validated) {
                $query->where('loan_type', $validated['loan_type']);
            })
            ->oldest('loan_date')
            ->orderBy('loan_code')
            ->get();

        $fileName = 'laporan-peminjaman-staff-' . $startDate . '-sampai-' . $endDate . '.csv';

        return response()->streamDownload(function () use ($loans) {
            $output = fopen('php://output', 'w');

            fwrite($output, "\xEF\xBB\xBF");

            fputcsv($output, [
                'No',
                'Kode Transaksi',
                'Jenis Peminjaman',
                'Status Transaksi',
                'Tanggal Pinjam',
                'Batas Kembali',
                'Tanggal Kembali',
                'Nama Anggota',
                'NIS/NIP',
                'Kelas',
                'Judul Buku',
                'ISBN',
                'Kode Eksemplar',
                'Status Eksemplar',
                'Denda',
                'Petugas',
                'Catatan',
            ], ';');

            $rowNumber = 1;

            foreach ($loans as $loan) {
                $loanItems = $loan->loanItems->isNotEmpty()
                    ? $loan->loanItems
                    : collect([null]);

                foreach ($loanItems as $loanItem) {
                    $bookItem = $loanItem?->bookItem;
                    $book = $bookItem?->book;

                    fputcsv($output, [
                        $rowNumber++,
                        $loan->loan_code ?? ('TRX-' . $loan->id),
                        $loan->loan_type_label,
                        ucfirst((string) ($loan->status ?? '-')),
                        $this->csvDate($loan->loan_date),
                        $this->csvDate($loan->due_date),
                        $this->csvDate($loan->return_date),
                        $loan->member?->name ?? '-',
                        $loan->member?->nis_nip ?? $loan->member?->member_code ?? '-',
                        $loan->studentClass?->class_name ?? $loan->member?->studentClass?->class_name ?? 'Guru/Staff',
                        $book?->title ?? '-',
                        $book?->isbn ?? '-',
                        $bookItem?->item_code ?? '-',
                        $loanItem?->status ? ucwords((string) $loanItem->status) : '-',
                        (int) ($loanItem?->fine_amount ?? 0),
                        $loan->handler?->name ?? '-',
                        $loan->notes ?? '-',
                    ], ';');
                }
            }

            fclose($output);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }

    public function store(Request $request)
    {
        if (! auth()->check() || (int) auth()->user()->role_id !== 1) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $maxNormalLoanItems = $this->settingInt('max_normal_loan_items', 3, 1, 200);

        $validated = $request->validate([
            'member_id' => ['required', 'exists:members,id'],
            'book_item_ids' => ['required', 'array', 'min:1', 'max:' . $maxNormalLoanItems],
            'book_item_ids.*' => ['required', 'integer', 'distinct', 'exists:book_items,id'],
            'loan_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:loan_date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'member_id.required' => 'Anggota wajib dipilih.',
            'book_item_ids.required' => 'Minimal pilih satu eksemplar buku.',
            'book_item_ids.min' => 'Minimal pilih satu eksemplar buku.',
            'book_item_ids.max' => 'Maksimal buku untuk peminjaman biasa adalah ' . $maxNormalLoanItems . ' eksemplar. Batas ini dapat diubah oleh Admin IT.',
            'book_item_ids.*.distinct' => 'Eksemplar buku tidak boleh sama.',
            'due_date.after_or_equal' => 'Batas kembali tidak boleh sebelum tanggal pinjam.',
        ]);

        $member = Member::findOrFail($validated['member_id']);

        if ($member->status !== 'aktif') {
            throw ValidationException::withMessages([
                'member_id' => 'Anggota yang dipilih tidak aktif.',
            ]);
        }

        $this->validateLoanSchedule($validated['loan_date'], $validated['due_date'], 'due_date');
        $this->validateRegularLoanQuota($member, count($validated['book_item_ids']), $maxNormalLoanItems);

        $bookItems = BookItem::with('book')
            ->whereIn('id', $validated['book_item_ids'])
            ->get();

        $this->validateBorrowableItems($bookItems, $validated['book_item_ids']);

        $loan = $this->createLoanTransaction(
            member: $member,
            bookItems: $bookItems,
            loanDate: $validated['loan_date'],
            dueDate: $validated['due_date'],
            notes: $validated['notes'] ?? null,
            loanType: 'regular',
            studentClassId: null
        );

        return redirect()
            ->route('loans.show', $loan)
            ->with('success_title', 'Peminjaman berhasil dibuat')
            ->with('success_message', 'Transaksi "' . ($loan->loan_code ?? 'TRX-' . $loan->id) . '" berhasil dibuat.')
            ->with('success_detail', 'Status eksemplar yang dipinjam sudah berubah menjadi dipinjam.');
    }

    public function classBulkCreate()
    {
        if (! auth()->check() || (int) auth()->user()->role_id !== 1) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $this->syncOverdueLoans();

        $members = Member::with('studentClass')
            ->where('status', 'aktif')
            ->orderBy('name')
            ->get();

        $studentClasses = StudentClass::orderBy('level')
            ->orderBy('class_name')
            ->get();

        $borrowedBookItemIds = $this->activeBorrowedBookItemIds();

        $bookItems = BookItem::with('book')
            ->where('status', 'tersedia')
            ->whereNotIn('id', $borrowedBookItemIds)
            ->whereHas('book', function ($query) {
                if (Schema::hasColumn('books', 'is_borrowable')) {
                    $query->where('is_borrowable', 1);
                }
            })
            ->orderBy('item_code')
            ->get();

        $maxClassLoanItems = $this->settingInt('max_class_loan_items', 40, 1, 500);
        $loanDurationDays = $this->settingInt('loan_duration_days', 7, 1, 365);

        return view('pustakawan.loans.class_bulk_create', compact(
            'members',
            'studentClasses',
            'bookItems',
            'maxClassLoanItems',
            'loanDurationDays'
        ));
    }

    public function classBulkStore(Request $request)
    {
        if (! auth()->check() || (int) auth()->user()->role_id !== 1) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $maxClassLoanItems = $this->settingInt('max_class_loan_items', 40, 1, 500);

        $validated = $request->validate([
            'member_id' => ['required', 'exists:members,id'],
            'student_class_id' => ['nullable', 'exists:classes,id'],
            'book_id' => ['required', 'exists:books,id'],
            'book_item_ids' => ['required', 'array', 'min:1', 'max:' . $maxClassLoanItems],
            'book_item_ids.*' => ['required', 'integer', 'distinct', 'exists:book_items,id'],
            'loan_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:loan_date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'member_id.required' => 'Perwakilan peminjam wajib dipilih.',
            'book_id.required' => 'Jenis buku wajib dipilih.',
            'book_item_ids.required' => 'Minimal pilih satu eksemplar buku.',
            'book_item_ids.max' => 'Maksimal eksemplar untuk peminjaman kelas adalah ' . $maxClassLoanItems . '. Batas ini dapat diubah oleh Admin IT.',
        ]);

        $member = Member::with('studentClass')->findOrFail($validated['member_id']);

        if ($member->status !== 'aktif') {
            throw ValidationException::withMessages([
                'member_id' => 'Anggota yang dipilih tidak aktif.',
            ]);
        }

        $this->validateLoanSchedule($validated['loan_date'], $validated['due_date'], 'due_date');

        $bookItems = BookItem::with('book')
            ->whereIn('id', $validated['book_item_ids'])
            ->where('book_id', $validated['book_id'])
            ->get();

        if ($bookItems->count() !== count($validated['book_item_ids'])) {
            throw ValidationException::withMessages([
                'book_item_ids' => 'Ada eksemplar yang tidak sesuai dengan jenis buku yang dipilih.',
            ]);
        }

        $this->validateBorrowableItems($bookItems, $validated['book_item_ids']);

        $studentClassId = ! empty($validated['student_class_id'])
            ? (int) $validated['student_class_id']
            : (int) ($member->student_class_id ?? 0);

        $studentClass = $studentClassId > 0
            ? StudentClass::find($studentClassId)
            : null;

        $studentClassId = $studentClass?->id;

        $bookTitle = $bookItems->first()?->book?->title ?? 'Buku';

        $notes = trim((string) ($validated['notes'] ?? ''));

        $autoNote = 'Peminjaman kelas/rombongan. Buku: ' . $bookTitle . '. Jumlah: ' . $bookItems->count() . ' eksemplar.';

        if ($studentClass) {
            $autoNote .= ' Kelas: ' . ($studentClass->class_name ?? $studentClass->name ?? '-');
        }

        if ($notes !== '') {
            $autoNote .= ' Catatan: ' . $notes;
        }

        $loan = $this->createLoanTransaction(
            member: $member,
            bookItems: $bookItems,
            loanDate: $validated['loan_date'],
            dueDate: $validated['due_date'],
            notes: $autoNote,
            loanType: 'class_bulk',
            studentClassId: $studentClassId
        );

        return redirect()
            ->route('loans.show', $loan)
            ->with('success_title', 'Peminjaman kelas berhasil dibuat')
            ->with('success_message', $bookItems->count() . ' eksemplar "' . $bookTitle . '" berhasil dipinjam atas nama ' . ($member->name ?? '-') . '.')
            ->with('success_detail', 'Transaksi ini cocok untuk peminjaman buku pelajaran oleh satu perwakilan kelas.');
    }

    public function show(Loan $loan)
    {
        if (! auth()->check() || (int) auth()->user()->role_id !== 1) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $this->syncOverdueLoans();

        $loan->load([
            'member.studentClass',
            'studentClass',
            'loanItems.bookItem.book',
        ]);

        return view('pustakawan.loans.show', compact('loan'));
    }

    public function edit(Loan $loan)
    {
        if (! auth()->check() || (int) auth()->user()->role_id !== 1) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $this->syncOverdueLoans();

        $loan->load([
            'member.studentClass',
            'studentClass',
            'loanItems.bookItem.book',
        ]);

        return view('pustakawan.loans.show', compact('loan'));
    }

    public function update(Request $request, Loan $loan)
    {
        if (! auth()->check() || (int) auth()->user()->role_id !== 1) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $this->syncOverdueLoans();

        $loan->refresh();

        $loan->load([
            'loanItems.bookItem.book',
            'member.studentClass',
            'studentClass',
        ]);

        $loanCode = $loan->loan_code ?? ('TRX-' . $loan->id);

        if (! in_array($loan->status, ['aktif', 'terlambat'], true)) {
            return back()
                ->with('error_title', 'Transaksi tidak bisa diproses')
                ->with('error_message', 'Transaksi "' . $loanCode . '" tidak berada pada status aktif atau terlambat.')
                ->with('error_detail', 'Transaksi yang sudah selesai tidak perlu diproses kembali.');
        }

        $validated = $request->validate([
            'return_date' => ['required', 'date'],
            'loan_item_ids' => ['required', 'array', 'min:1'],
            'loan_item_ids.*' => ['required', 'integer', 'exists:loan_items,id'],
            'return_conditions' => ['nullable', 'array'],
            'return_conditions.*' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'return_date.required' => 'Tanggal pengembalian wajib diisi.',
            'loan_item_ids.required' => 'Pilih minimal satu eksemplar yang ingin dikembalikan.',
            'loan_item_ids.min' => 'Pilih minimal satu eksemplar yang ingin dikembalikan.',
        ]);

        $returnDate = Carbon::parse($validated['return_date'])->format('Y-m-d');

        if ($loan->loan_date && Carbon::parse($returnDate)->startOfDay()->lt(Carbon::parse($loan->loan_date)->startOfDay())) {
            throw ValidationException::withMessages([
                'return_date' => 'Tanggal pengembalian tidak boleh sebelum tanggal pinjam.',
            ]);
        }

        $selectedLoanItemIds = collect($validated['loan_item_ids'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $activeLoanItems = $loan->loanItems
            ->filter(function ($loanItem) {
                return in_array($loanItem->status, ['dipinjam', 'terlambat'], true);
            });

        $selectedActiveLoanItems = $activeLoanItems
            ->filter(function ($loanItem) use ($selectedLoanItemIds) {
                return $selectedLoanItemIds->contains((int) $loanItem->id);
            })
            ->values();

        if ($selectedActiveLoanItems->count() !== $selectedLoanItemIds->count()) {
            return back()
                ->with('error_title', 'Item tidak valid')
                ->with('error_message', 'Ada item yang dipilih tetapi bukan bagian dari transaksi aktif ini.')
                ->with('error_detail', 'Pilih hanya eksemplar yang masih berstatus dipinjam atau terlambat.');
        }

        $finePerDay = $this->settingInt('fine_per_day', 500, 0, 1000000);

        DB::transaction(function () use ($loan, $selectedLoanItemIds, $returnDate, $finePerDay, $request) {
            $lockedLoan = Loan::whereKey($loan->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($lockedLoan->status, ['aktif', 'terlambat'], true)) {
                throw ValidationException::withMessages([
                    'loan_item_ids' => 'Transaksi ini sudah tidak aktif dan tidak bisa diproses kembali.',
                ]);
            }

            $selectedActiveLoanItems = LoanItem::with('bookItem')
                ->where('loan_id', $lockedLoan->id)
                ->whereIn('id', $selectedLoanItemIds->all())
                ->whereIn('status', ['dipinjam', 'terlambat'])
                ->lockForUpdate()
                ->get();

            if ($selectedActiveLoanItems->count() !== $selectedLoanItemIds->count()) {
                throw ValidationException::withMessages([
                    'loan_item_ids' => 'Ada item yang sudah berubah status. Muat ulang halaman lalu pilih kembali item yang masih aktif.',
                ]);
            }

            $bookItemsById = BookItem::whereIn('id', $selectedActiveLoanItems->pluck('book_item_id')->filter()->all())
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($selectedActiveLoanItems as $loanItem) {
                $bookItem = $bookItemsById->get($loanItem->book_item_id);

                $returnCondition = $request->input('return_conditions.' . $loanItem->id, 'baik');

                $returnCondition = trim((string) $returnCondition);
                $returnCondition = $returnCondition !== '' ? strtolower($returnCondition) : 'baik';

                if (! in_array($returnCondition, ['baik', 'rusak ringan', 'rusak berat', 'hilang'], true)) {
                    $returnCondition = 'baik';
                }

                $lateDays = 0;

                if ($lockedLoan->due_date) {
                    $dueDate = Carbon::parse($lockedLoan->due_date)->startOfDay();
                    $returnedAt = Carbon::parse($returnDate)->startOfDay();

                    if ($returnedAt->gt($dueDate)) {
                        $lateDays = (int) $dueDate->diffInDays($returnedAt);
                    }
                }

                $fineAmount = $lateDays * $finePerDay;

                $loanItem->update($this->filterColumns('loan_items', [
                    'return_date' => $returnDate,
                    'late_days' => $lateDays,
                    'fine_amount' => $fineAmount,
                    'return_condition' => $returnCondition,
                    'status' => 'dikembalikan',
                    'notes' => $request->input('notes'),
                ]));

                $this->syncFinePayment($loanItem, $fineAmount, $request->input('notes'));

                if ($bookItem) {
                    $bookItemStatus = 'tersedia';
                    $bookItemCondition = 'baik';

                    if ($returnCondition === 'hilang') {
                        $bookItemStatus = 'hilang';
                        $bookItemCondition = 'hilang';
                    } elseif ($returnCondition === 'rusak berat') {
                        $bookItemStatus = 'rusak';
                        $bookItemCondition = 'rusak berat';
                    } elseif ($returnCondition === 'rusak ringan') {
                        $bookItemStatus = 'rusak';
                        $bookItemCondition = 'rusak ringan';
                    }

                    $bookItem->update($this->filterColumns('book_items', [
                        'status' => $bookItemStatus,
                        'condition' => $bookItemCondition,
                    ]));
                }
            }

            $remainingActiveItems = $lockedLoan->loanItems()
                ->whereIn('status', ['dipinjam', 'terlambat'])
                ->count();

            if ($remainingActiveItems === 0) {
                $lockedLoan->update($this->filterColumns('loans', [
                    'status' => 'selesai',
                    'return_date' => $returnDate,
                    'return_notes' => $request->input('notes'),
                ]));
            } else {
                $newLoanStatus = $lockedLoan->due_date && Carbon::parse($lockedLoan->due_date)->startOfDay()->lt(today())
                    ? 'terlambat'
                    : 'aktif';

                $lockedLoan->update($this->filterColumns('loans', [
                    'status' => $newLoanStatus,
                    'return_notes' => $request->input('notes'),
                ]));
            }
        });

        return redirect()
            ->route('loans.show', $loan)
            ->with('success_title', 'Pengembalian berhasil diproses')
            ->with('success_message', 'Status peminjaman "' . $loanCode . '" berhasil diperbarui.')
            ->with('success_detail', 'Eksemplar yang dikembalikan sudah diperbarui status dan kondisinya.');
    }

    public function destroy(Request $request, Loan $loan)
    {
        if (! auth()->check() || (int) auth()->user()->role_id !== 1) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $loan->load([
            'loanItems.bookItem',
            'member',
        ]);

        $loanCode = $loan->loan_code ?? ('TRX-' . $loan->id);

        $request->validate([
            'cancel_confirmation' => ['required', 'string'],
            'cancel_reason' => ['nullable', 'string', 'max:500'],
            'cancel_agreement' => ['accepted'],
        ], [
            'cancel_confirmation.required' => 'Kode transaksi wajib diketik untuk membatalkan transaksi.',
            'cancel_agreement.accepted' => 'Centang persetujuan pembatalan terlebih dahulu.',
        ]);

        if (trim((string) $request->cancel_confirmation) !== $loanCode) {
            return back()
                ->with('error_title', 'Konfirmasi pembatalan salah')
                ->with('error_message', 'Kode transaksi yang diketik tidak sesuai.')
                ->with('error_detail', 'Pembatalan dibatalkan untuk mencegah transaksi terhapus tidak sengaja.');
        }

        if (! in_array($loan->status, ['aktif', 'terlambat'], true)) {
            return back()
                ->with('error_title', 'Transaksi tidak bisa dibatalkan')
                ->with('error_message', 'Transaksi "' . $loanCode . '" tidak berada pada status aktif atau terlambat.')
                ->with('error_detail', 'Transaksi yang sudah selesai tidak boleh dibatalkan.');
        }

        $hasProcessedItem = $loan->loanItems->contains(function ($loanItem) {
            return ! in_array($loanItem->status, ['dipinjam', 'terlambat'], true);
        });

        if ($hasProcessedItem) {
            return back()
                ->with('error_title', 'Transaksi tidak bisa dibatalkan')
                ->with('error_message', 'Transaksi "' . $loanCode . '" sudah memiliki item yang pernah diproses.')
                ->with('error_detail', 'Jika buku sudah dikembalikan, gunakan riwayat pengembalian.');
        }

        DB::transaction(function () use ($loan) {
            foreach ($loan->loanItems as $loanItem) {
                $bookItem = $loanItem->bookItem;

                if (! $bookItem) {
                    continue;
                }

                $hasOtherActiveLoan = $bookItem->loanItems()
                    ->where('loan_id', '!=', $loan->id)
                    ->whereIn('status', ['dipinjam', 'terlambat'])
                    ->whereHas('loan', function ($query) {
                        $query->whereIn('status', ['aktif', 'terlambat']);
                    })
                    ->exists();

                if ($hasOtherActiveLoan) {
                    continue;
                }

                $newBookItemStatus = match ($bookItem->condition) {
                    'hilang' => 'hilang',
                    'rusak berat', 'rusak ringan' => 'rusak',
                    default => 'tersedia',
                };

                $bookItem->update($this->filterColumns('book_items', [
                    'status' => $newBookItemStatus,
                ]));
            }

            $loan->loanItems()->delete();

            $loan->delete();
        });

        return redirect()
            ->route('loans.index')
            ->with('success_title', 'Transaksi berhasil dibatalkan')
            ->with('success_message', 'Transaksi "' . $loanCode . '" berhasil dibatalkan.')
            ->with('success_detail', 'Eksemplar buku dikembalikan ke stok.');
    }

    private function createLoanTransaction(
        Member $member,
        $bookItems,
        string $loanDate,
        string $dueDate,
        ?string $notes = null,
        string $loanType = 'regular',
        ?int $studentClassId = null
    ): Loan {
        return DB::transaction(function () use ($member, $bookItems, $loanDate, $dueDate, $notes, $loanType, $studentClassId) {
            $requestedIds = collect($bookItems)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();

            $lockedBookItems = BookItem::with('book')
                ->whereIn('id', $requestedIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $this->validateBorrowableItems($lockedBookItems, $requestedIds);

            $loanStatus = Carbon::parse($dueDate)->startOfDay()->lt(today())
                ? 'terlambat'
                : 'aktif';

            $loan = Loan::create($this->filterColumns('loans', [
                'loan_code' => $this->generateLoanCode(),
                'member_id' => $member->id,
                'student_class_id' => $studentClassId,
                'loan_date' => $loanDate,
                'due_date' => $dueDate,
                'return_date' => null,
                'status' => $loanStatus,
                'loan_type' => $loanType,
                'handled_by' => auth()->id(),
                'notes' => $notes,
                'return_notes' => null,
            ]));

            foreach ($lockedBookItems as $bookItem) {
                LoanItem::create($this->filterColumns('loan_items', [
                    'loan_id' => $loan->id,
                    'book_item_id' => $bookItem->id,
                    'status' => $loanStatus === 'terlambat' ? 'terlambat' : 'dipinjam',
                    'return_date' => null,
                    'late_days' => 0,
                    'fine_amount' => 0,
                    'return_condition' => null,
                    'notes' => null,
                ]));

                $bookItem->update($this->filterColumns('book_items', [
                    'status' => 'dipinjam',
                ]));
            }

            return $loan;
        });
    }

    private function validateBorrowableItems($bookItems, array $requestedIds): void
    {
        if ($bookItems->count() !== count($requestedIds)) {
            throw ValidationException::withMessages([
                'book_item_ids' => 'Ada eksemplar buku yang tidak ditemukan.',
            ]);
        }

        foreach ($bookItems as $bookItem) {
            $status = strtolower((string) $bookItem->status);

            if ($status !== 'tersedia') {
                throw ValidationException::withMessages([
                    'book_item_ids' => 'Eksemplar "' . ($bookItem->item_code ?? '-') . '" tidak tersedia untuk dipinjam.',
                ]);
            }

            if (Schema::hasColumn('book_items', 'condition')) {
                $condition = strtolower((string) ($bookItem->condition ?? 'baik'));

                if (in_array($condition, ['hilang', 'rusak berat'], true)) {
                    throw ValidationException::withMessages([
                        'book_item_ids' => 'Eksemplar "' . ($bookItem->item_code ?? '-') . '" berkondisi ' . $condition . ' sehingga tidak boleh dipinjam.',
                    ]);
                }
            }

            if ($bookItem->book && Schema::hasColumn('books', 'is_borrowable') && ! (bool) $bookItem->book->is_borrowable) {
                throw ValidationException::withMessages([
                    'book_item_ids' => 'Buku "' . ($bookItem->book->title ?? '-') . '" tidak diizinkan untuk dipinjam.',
                ]);
            }

            $hasActiveLoan = $bookItem->loanItems()
                ->whereIn('status', ['dipinjam', 'terlambat'])
                ->whereHas('loan', function ($query) {
                    $query->whereIn('status', ['aktif', 'terlambat']);
                })
                ->exists();

            if ($hasActiveLoan) {
                throw ValidationException::withMessages([
                    'book_item_ids' => 'Eksemplar "' . ($bookItem->item_code ?? '-') . '" masih berada dalam transaksi aktif.',
                ]);
            }
        }
    }

    private function activeBorrowedBookItemIds(): array
    {
        return LoanItem::whereIn('status', ['dipinjam', 'terlambat'])
            ->whereHas('loan', function ($query) {
                $query->whereIn('status', ['aktif', 'terlambat']);
            })
            ->pluck('book_item_id')
            ->map(fn ($id) => (int) $id)
            ->toArray();
    }

    private function validateLoanSchedule(string $loanDate, string $dueDate, string $field = 'due_date'): void
    {
        $loanDateValue = Carbon::parse($loanDate)->startOfDay();
        $dueDateValue = Carbon::parse($dueDate)->startOfDay();
        $today = today()->startOfDay();

        if ($loanDateValue->gt($today)) {
            throw ValidationException::withMessages([
                'loan_date' => 'Tanggal pinjam tidak boleh melebihi hari ini.',
            ]);
        }

        if ($dueDateValue->lt($loanDateValue)) {
            throw ValidationException::withMessages([
                $field => 'Batas kembali tidak boleh sebelum tanggal pinjam.',
            ]);
        }

        $loanDurationDays = $this->settingInt('loan_duration_days', 7, 1, 365);
        $maximumDueDate = $loanDateValue->copy()->addDays($loanDurationDays);

        if ($dueDateValue->gt($maximumDueDate)) {
            throw ValidationException::withMessages([
                $field => 'Batas kembali maksimal ' . $loanDurationDays . ' hari dari tanggal pinjam.',
            ]);
        }
    }

    private function validateRegularLoanQuota(Member $member, int $requestedItemCount, int $maxNormalLoanItems): void
    {
        $activeRegularItemCount = $this->activeRegularLoanItemsCount($member);

        if (($activeRegularItemCount + $requestedItemCount) <= $maxNormalLoanItems) {
            return;
        }

        throw ValidationException::withMessages([
            'book_item_ids' => 'Anggota ini masih memiliki ' . $activeRegularItemCount . ' eksemplar aktif pada peminjaman biasa. Maksimal total aktif adalah ' . $maxNormalLoanItems . ' eksemplar.',
        ]);
    }

    private function activeRegularLoanItemsCount(Member $member): int
    {
        return LoanItem::whereIn('status', ['dipinjam', 'terlambat'])
            ->whereHas('loan', function ($query) use ($member) {
                $query->where('member_id', $member->id)
                    ->whereIn('status', ['aktif', 'terlambat']);

                if (Schema::hasColumn('loans', 'loan_type')) {
                    $query->where(function ($typeQuery) {
                        $typeQuery->whereNull('loan_type')
                            ->orWhere('loan_type', Loan::TYPE_REGULAR);
                    });
                }
            })
            ->count();
    }

    private function syncFinePayment(LoanItem $loanItem, int $fineAmount, ?string $notes = null): void
    {
        if (! Schema::hasTable('fine_payments')) {
            return;
        }

        $existingPayment = FinePayment::where('loan_item_id', $loanItem->id)
            ->lockForUpdate()
            ->first();

        if ($fineAmount <= 0) {
            if ($existingPayment && $existingPayment->payment_status === 'belum dibayar') {
                $existingPayment->delete();
            }

            return;
        }

        $paymentNotes = trim((string) $notes);
        $paymentNotes = $paymentNotes !== ''
            ? $paymentNotes
            : 'Denda keterlambatan otomatis dari proses pengembalian.';

        if ($existingPayment) {
            $payload = [
                'amount' => $fineAmount,
                'notes' => $paymentNotes,
            ];

            if ($existingPayment->payment_status === 'belum dibayar') {
                $payload['payment_date'] = null;
                $payload['received_by'] = null;
            }

            $existingPayment->update($payload);

            return;
        }

        FinePayment::create([
            'loan_item_id' => $loanItem->id,
            'amount' => $fineAmount,
            'payment_date' => null,
            'payment_status' => 'belum dibayar',
            'received_by' => null,
            'notes' => $paymentNotes,
        ]);
    }

    private function syncOverdueLoans(): void
    {
        $today = today()->format('Y-m-d');

        Loan::where('status', 'aktif')
            ->whereDate('due_date', '<', $today)
            ->update([
                'status' => 'terlambat',
            ]);

        LoanItem::where('status', 'dipinjam')
            ->whereHas('loan', function ($query) {
                $query->where('status', 'terlambat');
            })
            ->update([
                'status' => 'terlambat',
            ]);
    }

    private function generateLoanCode(): string
    {
        $prefix = 'TRX-' . now()->format('Ymd') . '-';

        $lastLoan = Loan::where('loan_code', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('loan_code')
            ->first();

        $nextNumber = 1;

        if ($lastLoan && $lastLoan->loan_code) {
            $lastNumber = (int) substr($lastLoan->loan_code, -3);
            $nextNumber = $lastNumber + 1;
        }

        do {
            $loanCode = $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
            $nextNumber++;
        } while (Loan::where('loan_code', $loanCode)->exists());

        return $loanCode;
    }

    private function csvDate($date): string
    {
        return $date ? Carbon::parse($date)->format('Y-m-d') : '-';
    }

    private function settingInt(string $key, int $default, int $min, int $max): int
    {
        try {
            if (class_exists(SystemSetting::class) && method_exists(SystemSetting::class, 'intValue')) {
                $value = (int) SystemSetting::intValue($key, $default);
            } elseif (class_exists(SystemSetting::class) && method_exists(SystemSetting::class, 'getValue')) {
                $value = (int) SystemSetting::getValue($key, $default);
            } else {
                $value = $default;
            }
        } catch (\Throwable $e) {
            $value = $default;
        }

        return max($min, min($max, $value));
    }

    private function filterColumns(string $table, array $payload): array
    {
        return collect($payload)
            ->filter(function ($value, $column) use ($table) {
                return Schema::hasColumn($table, $column);
            })
            ->toArray();
    }
}
