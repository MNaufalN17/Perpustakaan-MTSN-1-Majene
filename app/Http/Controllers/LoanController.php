<?php

namespace App\Http\Controllers;

use App\Models\BookItem;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\Member;
use App\Models\StudentClass;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LoanController extends Controller
{
    public function index()
{
    $loans = Loan::with(['member', 'handler', 'loanItems.bookItem.book'])
        ->latest()
        ->get();

    return view('pustakawan.loans.index', compact('loans'));
}

    public function create()
    {
        $this->syncOverdueLoans();

        $members = Member::with('studentClass')
            ->where('status', 'aktif')
            ->orderBy('name')
            ->get();

        $bookItems = BookItem::with(['book', 'activeLoanItems'])
            ->where('status', 'tersedia')
            ->whereDoesntHave('activeLoanItems')
            ->orderBy('item_code')
            ->get();

        $classes = StudentClass::orderBy('level', 'asc')->get();

        return view('pustakawan.loans.create', compact('members', 'bookItems', 'classes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'member_id' => ['required', 'exists:members,id'],
            'book_item_ids' => ['required', 'array', 'min:1', 'max:3'],
            'book_item_ids.*' => ['nullable', 'exists:book_items,id'],
            'loan_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:loan_date'],
        ], [
            'member_id.required' => 'Anggota peminjam wajib dipilih.',
            'member_id.exists' => 'Anggota peminjam tidak ditemukan.',
            'book_item_ids.required' => 'Minimal satu buku wajib dipilih.',
            'book_item_ids.min' => 'Minimal satu buku wajib dipilih.',
            'book_item_ids.max' => 'Maksimal tiga buku dalam satu transaksi.',
            'book_item_ids.*.exists' => 'Salah satu buku yang dipilih tidak ditemukan.',
            'loan_date.required' => 'Tanggal pinjam wajib diisi.',
            'due_date.required' => 'Tanggal jatuh tempo wajib diisi.',
            'due_date.after_or_equal' => 'Tanggal jatuh tempo tidak boleh lebih awal dari tanggal pinjam.',
        ]);

        $bookItemIds = collect($validated['book_item_ids'])
            ->filter()
            ->unique()
            ->values();

        if ($bookItemIds->isEmpty()) {
            throw ValidationException::withMessages([
                'book_item_ids' => 'Minimal satu buku wajib dipilih.',
            ]);
        }

        if ($bookItemIds->count() !== collect($validated['book_item_ids'])->filter()->count()) {
            throw ValidationException::withMessages([
                'book_item_ids' => 'Buku yang sama tidak boleh dipilih lebih dari satu kali.',
            ]);
        }

        $loan = DB::transaction(function () use ($validated, $bookItemIds) {
            $bookItems = BookItem::with(['book', 'activeLoanItems'])
                ->whereIn('id', $bookItemIds)
                ->lockForUpdate()
                ->get();

            foreach ($bookItems as $bookItem) {
                if ($bookItem->activeLoanItems->isNotEmpty()) {
                    throw ValidationException::withMessages([
                        'book_item_ids' => 'Eksemplar "' . $bookItem->item_code . '" masih tercatat sedang dipinjam.',
                    ]);
                }

                if ($bookItem->status !== 'tersedia') {
                    throw ValidationException::withMessages([
                        'book_item_ids' => 'Eksemplar "' . $bookItem->item_code . '" tidak tersedia untuk dipinjam.',
                    ]);
                }
            }

            $loan = Loan::create([
                'loan_code' => $this->generateLoanCode(),
                'member_id' => $validated['member_id'],
                'loan_date' => $validated['loan_date'],
                'due_date' => $validated['due_date'],
                'status' => 'aktif',
                'handled_by' => Auth::id(),
            ]);

            foreach ($bookItems as $bookItem) {
                LoanItem::create([
                    'loan_id' => $loan->id,
                    'book_item_id' => $bookItem->id,
                    'status' => 'dipinjam',
                    'late_days' => 0,
                    'fine_amount' => 0,
                ]);

                $bookItem->update([
                    'status' => 'dipinjam',
                ]);
            }

            return $loan;
        });

        return redirect()
            ->route('loans.show', $loan)
            ->with('success_title', 'Peminjaman berhasil dibuat')
            ->with('success_message', 'Transaksi "' . $loan->loan_code . '" berhasil disimpan.')
            ->with('success_detail', 'Status stok buku otomatis berubah menjadi dipinjam.');
    }

    public function show(Loan $loan)
{
    $loan->load([
        'member.studentClass',
        'handler',
        'loanItems.bookItem.book',
    ]);

    return view('pustakawan.loans.show', compact('loan'));
}

    public function edit(Loan $loan)
    {
        $this->syncOverdueLoans();

        $loan->load(['member.studentClass', 'loanItems.bookItem.book', 'handler']);

        $activeItems = $loan->loanItems
            ->whereIn('status', ['dipinjam', 'terlambat'])
            ->values();

        return view('pustakawan.loans.edit', compact('loan', 'activeItems'));
    }

    public function update(Request $request, Loan $loan)
    {
        $this->syncOverdueLoans();

        $loan->load(['loanItems.bookItem.book']);

        $activeItems = $loan->loanItems
            ->whereIn('status', ['dipinjam', 'terlambat'])
            ->values();

        if ($activeItems->isEmpty()) {
            return redirect()
                ->route('loans.show', $loan)
                ->with('error_title', 'Pengembalian tidak bisa diproses')
                ->with('error_message', 'Transaksi ini sudah tidak memiliki buku yang sedang dipinjam.')
                ->with('error_detail', 'Kemungkinan semua buku pada transaksi ini sudah pernah dikembalikan.');
        }

        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.return_condition' => ['required', 'in:baik,rusak ringan,rusak berat,hilang'],
        ], [
            'items.required' => 'Kondisi buku yang dikembalikan wajib diisi.',
            'items.*.return_condition.required' => 'Kondisi setiap buku wajib dipilih.',
            'items.*.return_condition.in' => 'Kondisi buku yang dipilih tidak valid.',
        ]);

        $today = Carbon::now()->startOfDay();
        $dueDate = Carbon::parse($loan->due_date)->startOfDay();
        $lateDays = $today->gt($dueDate) ? (int) $dueDate->diffInDays($today) : 0;
        $finePerItem = $lateDays * Loan::FINE_PER_DAY;

        DB::transaction(function () use ($loan, $activeItems, $validated, $today, $lateDays, $finePerItem) {
            foreach ($activeItems as $loanItem) {
                $condition = $validated['items'][$loanItem->id]['return_condition'] ?? null;

                if (!$condition) {
                    throw ValidationException::withMessages([
                        "items.{$loanItem->id}.return_condition" => 'Kondisi buku "' . ($loanItem->bookItem->item_code ?? '-') . '" wajib dipilih.',
                    ]);
                }

                $bookItem = $loanItem->bookItem;

                if (!$bookItem) {
                    throw ValidationException::withMessages([
                        'items' => 'Salah satu data eksemplar pada transaksi ini tidak ditemukan.',
                    ]);
                }

                $loanItem->update([
                    'return_date' => $today->toDateString(),
                    'late_days' => $lateDays,
                    'fine_amount' => $finePerItem,
                    'return_condition' => $condition,
                    'status' => $condition === 'hilang' ? 'hilang' : 'dikembalikan',
                ]);

                $bookItem->update([
                    'status' => $condition === 'hilang' ? 'hilang' : 'tersedia',
                    'condition' => $condition,
                ]);
            }

            $stillActive = $loan->loanItems()
                ->whereIn('status', ['dipinjam', 'terlambat'])
                ->exists();

            $loan->update([
                'status' => $stillActive ? 'aktif' : 'selesai',
            ]);
        });

        return redirect()
            ->route('loans.index')
            ->with('success_title', 'Pengembalian berhasil diproses')
            ->with('success_message', 'Buku yang dikembalikan sudah diperbarui.')
            ->with('success_detail', 'Denda dan status stok buku sudah diperbarui otomatis.');
    }

    public function destroy(Loan $loan)
    {
        $loan->load('loanItems.bookItem');

        $hasActiveItems = $loan->loanItems()
            ->whereIn('status', ['dipinjam', 'terlambat'])
            ->exists();

        if (!$hasActiveItems) {
            return redirect()
                ->route('loans.index')
                ->with('error_title', 'Transaksi tidak bisa dibatalkan')
                ->with('error_message', 'Transaksi ini sudah selesai atau tidak memiliki buku aktif.');
        }

        DB::transaction(function () use ($loan) {
            foreach ($loan->loanItems as $loanItem) {
                if (in_array($loanItem->status, ['dipinjam', 'terlambat'])) {
                    $loanItem->update([
                        'status' => 'batal',
                    ]);

                    if ($loanItem->bookItem) {
                        $loanItem->bookItem->update([
                            'status' => 'tersedia',
                        ]);
                    }
                }
            }

            $loan->update([
                'status' => 'batal',
            ]);
        });

        return redirect()
            ->route('loans.index')
            ->with('success_title', 'Transaksi berhasil dibatalkan')
            ->with('success_message', 'Status buku sudah dikembalikan menjadi tersedia.');
    }

    private function syncOverdueLoans(): void
    {
        Loan::where('status', 'aktif')
            ->whereDate('due_date', '<', Carbon::now()->toDateString())
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
        $date = now()->format('Ymd');

        $lastNumber = Loan::whereDate('created_at', now()->toDateString())
            ->count() + 1;

        return 'TRX-' . $date . '-' . str_pad($lastNumber, 3, '0', STR_PAD_LEFT);
    }
}