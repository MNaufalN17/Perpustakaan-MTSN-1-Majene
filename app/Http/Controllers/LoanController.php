<?php

namespace App\Http\Controllers;

use App\Models\BookItem;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\Member;
use App\Models\StudentClass;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class LoanController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->check() || (int) auth()->user()->role_id !== 1) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $this->syncOverdueLoans();

        $keyword = trim((string) $request->input('keyword', ''));
        $status = trim((string) $request->input('status', ''));

        $loans = Loan::with([
                'member.studentClass',
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
        if (!auth()->check() || (int) auth()->user()->role_id !== 1) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $this->syncOverdueLoans();

        $members = Member::with('studentClass')
            ->where('status', 'aktif')
            ->orderBy('name')
            ->get();

        $bookItems = BookItem::with('book')
            ->orderBy('item_code')
            ->get();

        $borrowedBookItemIds = LoanItem::whereIn('status', ['dipinjam', 'terlambat'])
            ->whereHas('loan', function ($query) {
                $query->whereIn('status', ['aktif', 'terlambat']);
            })
            ->pluck('book_item_id')
            ->toArray();

        $studentClasses = StudentClass::orderBy('level')
            ->orderBy('class_name')
            ->get();

        $classes = $studentClasses;

        return view('pustakawan.loans.create', compact(
            'members',
            'bookItems',
            'borrowedBookItemIds',
            'studentClasses',
            'classes'
        ));
    }

    public function store(Request $request)
    {
        if (!auth()->check() || (int) auth()->user()->role_id !== 1) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $validated = $request->validate([
            'member_id' => ['required', 'exists:members,id'],
            'book_item_ids' => ['required', 'array', 'min:1', 'max:3'],
            'book_item_ids.*' => ['required', 'integer', 'distinct', 'exists:book_items,id'],
            'loan_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:loan_date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'member_id.required' => 'Anggota wajib dipilih.',
            'member_id.exists' => 'Anggota yang dipilih tidak tersedia.',
            'book_item_ids.required' => 'Minimal pilih satu eksemplar buku.',
            'book_item_ids.min' => 'Minimal pilih satu eksemplar buku.',
            'book_item_ids.max' => 'Maksimal 3 buku dalam satu transaksi.',
            'book_item_ids.*.distinct' => 'Eksemplar buku tidak boleh sama.',
            'book_item_ids.*.exists' => 'Ada eksemplar buku yang tidak tersedia.',
            'loan_date.required' => 'Tanggal pinjam wajib diisi.',
            'due_date.required' => 'Batas kembali wajib diisi.',
            'due_date.after_or_equal' => 'Batas kembali tidak boleh sebelum tanggal pinjam.',
        ]);

        $member = Member::findOrFail($validated['member_id']);

        if ($member->status !== 'aktif') {
            throw ValidationException::withMessages([
                'member_id' => 'Anggota yang dipilih tidak aktif.',
            ]);
        }

        $bookItems = BookItem::with('book')
            ->whereIn('id', $validated['book_item_ids'])
            ->get();

        if ($bookItems->count() !== count($validated['book_item_ids'])) {
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

        $loan = DB::transaction(function () use ($validated, $bookItems) {
            $loanPayload = [
                'loan_code' => $this->generateLoanCode(),
                'member_id' => $validated['member_id'],
                'loan_date' => $validated['loan_date'],
                'due_date' => $validated['due_date'],
                'status' => 'aktif',
                'handled_by' => auth()->id(),
                'notes' => $validated['notes'] ?? null,
            ];

            $loan = Loan::create($this->filterColumns('loans', $loanPayload));

            foreach ($bookItems as $bookItem) {
                $loanItemPayload = [
                    'loan_id' => $loan->id,
                    'book_item_id' => $bookItem->id,
                    'status' => 'dipinjam',
                    'return_date' => null,
                    'late_days' => 0,
                    'fine_amount' => 0,
                    'return_condition' => null,
                    'notes' => null,
                ];

                LoanItem::create($this->filterColumns('loan_items', $loanItemPayload));

                $this->safeUpdateBookItemStatus($bookItem, 'dipinjam');
            }

            return $loan;
        });

        return redirect()
            ->route('loans.show', $loan)
            ->with('success_title', 'Peminjaman berhasil dibuat')
            ->with('success_message', 'Transaksi "' . ($loan->loan_code ?? 'TRX-' . $loan->id) . '" berhasil dibuat.')
            ->with('success_detail', 'Status eksemplar yang dipinjam sudah berubah menjadi dipinjam.');
    }

    public function show(Loan $loan)
    {
        if (!auth()->check() || (int) auth()->user()->role_id !== 1) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $this->syncOverdueLoans();

        $loan->load([
            'member.studentClass',
            'loanItems.bookItem.book',
        ]);

        return view('pustakawan.loans.show', compact('loan'));
    }

    public function edit(Loan $loan)
    {
        if (!auth()->check() || (int) auth()->user()->role_id !== 1) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $this->syncOverdueLoans();

        $loan->load([
            'member.studentClass',
            'loanItems.bookItem.book',
        ]);

        return view('pustakawan.loans.show', compact('loan'));
    }

    public function update(Request $request, Loan $loan)
    {
        if (!auth()->check() || (int) auth()->user()->role_id !== 1) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $this->syncOverdueLoans();

        $loan->refresh();

        $loan->load([
            'loanItems.bookItem.book',
            'member',
        ]);

        $loanCode = $loan->loan_code ?? ('TRX-' . $loan->id);

        if (!in_array($loan->status, ['aktif', 'terlambat'], true)) {
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
            'loan_item_ids.*.exists' => 'Ada item peminjaman yang tidak ditemukan.',
        ]);

        $returnDate = Carbon::parse($validated['return_date'])->format('Y-m-d');

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
                ->with('error_detail', 'Pilih hanya eksemplar yang masih berstatus dipinjam atau terlambat pada transaksi ini.');
        }

        if ($selectedActiveLoanItems->isEmpty()) {
            return back()
                ->with('error_title', 'Tidak ada item yang bisa diproses')
                ->with('error_message', 'Tidak ada eksemplar aktif yang dipilih untuk dikembalikan.')
                ->with('error_detail', 'Pastikan transaksi masih memiliki item dengan status dipinjam atau terlambat.');
        }

        $finePerDay = $this->getFinePerDay();

        DB::transaction(function () use ($loan, $selectedActiveLoanItems, $returnDate, $finePerDay, $request) {
            foreach ($selectedActiveLoanItems as $loanItem) {
                $bookItem = $loanItem->bookItem;

                $returnCondition = $request->input('return_conditions.' . $loanItem->id, 'baik');

                $returnCondition = trim((string) $returnCondition);
                $returnCondition = $returnCondition !== '' ? strtolower($returnCondition) : 'baik';

                if (!in_array($returnCondition, ['baik', 'rusak ringan', 'rusak berat', 'hilang'], true)) {
                    $returnCondition = 'baik';
                }

                $lateDays = 0;

                if ($loan->due_date) {
                    $dueDate = Carbon::parse($loan->due_date)->startOfDay();
                    $returnedAt = Carbon::parse($returnDate)->startOfDay();

                    if ($returnedAt->gt($dueDate)) {
                        $lateDays = (int) $dueDate->diffInDays($returnedAt);
                    }
                }

                $fineAmount = $lateDays * $finePerDay;

                $loanItemPayload = [
                    'return_date' => $returnDate,
                    'late_days' => $lateDays,
                    'fine_amount' => $fineAmount,
                    'return_condition' => $returnCondition,
                    'status' => 'dikembalikan',
                    'notes' => $request->input('notes'),
                ];

                $loanItem->update($this->filterColumns('loan_items', $loanItemPayload));

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

                    $bookItemPayload = [
                        'status' => $bookItemStatus,
                        'condition' => $bookItemCondition,
                    ];

                    $bookItem->update($this->filterColumns('book_items', $bookItemPayload));
                }
            }

            $remainingActiveItems = $loan->loanItems()
                ->whereIn('status', ['dipinjam', 'terlambat'])
                ->count();

            if ($remainingActiveItems === 0) {
                $loanPayload = [
                    'status' => 'selesai',
                    'return_date' => $returnDate,
                    'notes' => $request->input('notes'),
                ];

                $loan->update($this->filterColumns('loans', $loanPayload));
            } else {
                $newLoanStatus = $loan->due_date && Carbon::parse($loan->due_date)->startOfDay()->lt(today())
                    ? 'terlambat'
                    : 'aktif';

                $loanPayload = [
                    'status' => $newLoanStatus,
                    'notes' => $request->input('notes'),
                ];

                $loan->update($this->filterColumns('loans', $loanPayload));
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
        if (!auth()->check() || (int) auth()->user()->role_id !== 1) {
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
            'cancel_reason.max' => 'Catatan pembatalan maksimal 500 karakter.',
            'cancel_agreement.accepted' => 'Centang persetujuan pembatalan terlebih dahulu.',
        ]);

        if (trim((string) $request->cancel_confirmation) !== $loanCode) {
            return back()
                ->with('error_title', 'Konfirmasi pembatalan salah')
                ->with('error_message', 'Kode transaksi yang diketik tidak sesuai.')
                ->with('error_detail', 'Pembatalan dibatalkan oleh sistem untuk mencegah transaksi terhapus tidak sengaja.');
        }

        $activeLoanStatuses = ['aktif', 'terlambat'];
        $activeLoanItemStatuses = ['dipinjam', 'terlambat'];

        if (!in_array($loan->status, $activeLoanStatuses, true)) {
            return back()
                ->with('error_title', 'Transaksi tidak bisa dibatalkan')
                ->with('error_message', 'Transaksi "' . $loanCode . '" tidak berada pada status aktif atau terlambat.')
                ->with('error_detail', 'Transaksi yang sudah selesai tidak boleh dibatalkan. Gunakan pembatalan hanya untuk transaksi salah input atau tidak jadi dipinjam.');
        }

        $hasProcessedItem = $loan->loanItems->contains(function ($loanItem) use ($activeLoanItemStatuses) {
            return !in_array($loanItem->status, $activeLoanItemStatuses, true);
        });

        if ($hasProcessedItem) {
            return back()
                ->with('error_title', 'Transaksi tidak bisa dibatalkan')
                ->with('error_message', 'Transaksi "' . $loanCode . '" sudah memiliki item yang pernah diproses.')
                ->with('error_detail', 'Jika buku sudah dikembalikan, transaksi tidak boleh dibatalkan. Gunakan fitur pengembalian.');
        }

        DB::transaction(function () use ($loan, $activeLoanStatuses, $activeLoanItemStatuses) {
            $loan->load([
                'loanItems.bookItem',
            ]);

            foreach ($loan->loanItems as $loanItem) {
                $bookItem = $loanItem->bookItem;

                if (!$bookItem) {
                    continue;
                }

                $hasOtherActiveLoan = $bookItem->loanItems()
                    ->where('loan_id', '!=', $loan->id)
                    ->whereIn('status', $activeLoanItemStatuses)
                    ->whereHas('loan', function ($query) use ($activeLoanStatuses) {
                        $query->whereIn('status', $activeLoanStatuses);
                    })
                    ->exists();

                if ($hasOtherActiveLoan) {
                    continue;
                }

                $newBookItemStatus = match ($bookItem->condition) {
                    'hilang' => 'hilang',
                    'rusak berat' => 'rusak',
                    default => 'tersedia',
                };

                $this->safeUpdateBookItemStatus($bookItem, $newBookItemStatus);
            }

            $loan->loanItems()->delete();

            $loan->delete();
        });

        return redirect()
            ->route('loans.index')
            ->with('success_title', 'Transaksi berhasil dibatalkan')
            ->with('success_message', 'Transaksi "' . $loanCode . '" berhasil dibatalkan.')
            ->with('success_detail', 'Eksemplar buku dikembalikan ke stok. Pembatalan ini hanya untuk transaksi salah input atau tidak jadi dipinjam.');
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

    private function getFinePerDay(): int
    {
        if (class_exists(\App\Models\SystemSetting::class) && method_exists(\App\Models\SystemSetting::class, 'getValue')) {
            return (int) \App\Models\SystemSetting::getValue('fine_per_day', 500);
        }

        return 500;
    }

    private function filterColumns(string $table, array $payload): array
    {
        return collect($payload)
            ->filter(function ($value, $column) use ($table) {
                return Schema::hasColumn($table, $column);
            })
            ->toArray();
    }

    private function safeUpdateBookItemStatus(BookItem $bookItem, string $status): void
    {
        if (!Schema::hasColumn('book_items', 'status')) {
            return;
        }

        $bookItem->update([
            'status' => $status,
        ]);
    }
}