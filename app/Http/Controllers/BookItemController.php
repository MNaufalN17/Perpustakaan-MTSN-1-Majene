<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookItem;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BookItemController extends Controller
{
    private array $activeLoanStatuses = ['aktif', 'terlambat'];

    private array $activeLoanItemStatuses = ['dipinjam', 'terlambat'];

    public function index(Request $request)
    {
        if (!auth()->check() || (int) auth()->user()->role_id !== 1) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $keyword = trim((string) $request->input('keyword', ''));
        $status = trim((string) $request->input('status', ''));
        $condition = trim((string) $request->input('condition', ''));

        $bookItems = BookItem::with([
                'book.category',
                'book.ddcClass',
                'activeLoanItem.loan.member',
            ])
            ->withCount('loanItems')
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($subQuery) use ($keyword) {
                    $subQuery->where('item_code', 'like', "%{$keyword}%")
                        ->orWhere('classification_code', 'like', "%{$keyword}%")
                        ->orWhere('author_code', 'like', "%{$keyword}%")
                        ->orWhere('title_code', 'like', "%{$keyword}%")
                        ->orWhere('location', 'like', "%{$keyword}%")
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
            ->orderBy('item_code')
            ->paginate(10)
            ->withQueryString();

        return view('pustakawan.book_items.index', compact(
            'bookItems',
            'keyword',
            'status',
            'condition'
        ));
    }

    public function create()
    {
        if (!auth()->check() || (int) auth()->user()->role_id !== 1) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $books = Book::with(['category', 'ddcClass'])
            ->orderBy('title')
            ->get();

        $statusOptions = $this->statusOptions();
        $conditionOptions = $this->conditionOptions();

        return view('pustakawan.book_items.create', compact(
            'books',
            'statusOptions',
            'conditionOptions'
        ));
    }

    public function store(Request $request)
    {
        if (!auth()->check() || (int) auth()->user()->role_id !== 1) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $validated = $request->validate([
            'book_id' => ['required', 'integer', 'exists:books,id'],
            'item_code' => ['nullable', 'string', 'max:100', 'unique:book_items,item_code'],
            'classification_code' => ['nullable', 'string', 'max:100'],
            'author_code' => ['nullable', 'string', 'max:100'],
            'title_code' => ['nullable', 'string', 'max:100'],
            'title_initial' => ['nullable', 'string', 'max:20'],
            'copy_number' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'string', Rule::in(['tersedia', 'dipinjam', 'terlambat', 'rusak', 'hilang', 'nonaktif'])],
            'condition' => ['nullable', 'string', Rule::in(['baik', 'rusak ringan', 'rusak berat', 'hilang'])],
            'location' => ['nullable', 'string', 'max:255'],
            'acquisition_date' => ['nullable', 'date'],
        ], [
            'book_id.required' => 'Buku induk wajib dipilih.',
            'book_id.exists' => 'Buku induk yang dipilih tidak ditemukan.',
            'item_code.unique' => 'Kode eksemplar sudah digunakan.',
            'copy_number.integer' => 'Nomor copy harus berupa angka.',
        ]);

        $book = Book::with(['ddcClass'])->findOrFail($validated['book_id']);

        $copyNumber = $validated['copy_number'] ?? $this->nextCopyNumber((int) $validated['book_id']);

        $itemCode = trim((string) ($validated['item_code'] ?? ''));

        if ($itemCode === '') {
            $itemCode = $this->generateItemCode($book, $copyNumber, $validated);
        }

        if (BookItem::where('item_code', $itemCode)->exists()) {
            return back()
                ->withInput()
                ->withErrors([
                    'item_code' => 'Kode eksemplar "' . $itemCode . '" sudah digunakan.',
                ]);
        }

        $condition = $validated['condition'] ?? 'baik';
        $status = $validated['status'] ?? $this->statusFromCondition($condition);

        $bookItem = BookItem::create($this->filterColumns('book_items', [
            'book_id' => $validated['book_id'],
            'item_code' => $itemCode,
            'classification_code' => $validated['classification_code'] ?? null,
            'author_code' => $validated['author_code'] ?? null,
            'title_code' => $validated['title_code'] ?? null,
            'title_initial' => $validated['title_initial'] ?? null,
            'copy_number' => $copyNumber,
            'status' => $status,
            'condition' => $condition,
            'location' => $validated['location'] ?? null,
            'acquisition_date' => $validated['acquisition_date'] ?? null,
        ]));

        return redirect()
            ->route('book_items.index')
            ->with('success_title', 'Eksemplar berhasil ditambahkan')
            ->with('success_message', 'Copy "' . ($bookItem->item_code ?? '-') . '" berhasil ditambahkan.')
            ->with('success_detail', 'Eksemplar baru sudah masuk ke stok perpustakaan.');
    }

    public function show(BookItem $bookItem)
    {
        if (!auth()->check() || (int) auth()->user()->role_id !== 1) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $bookItem->load([
            'book.category',
            'book.ddcClass',
            'loanItems.loan.member.studentClass',
        ]);

        return view('pustakawan.book_items.show', compact('bookItem'));
    }

    public function edit(BookItem $bookItem)
    {
        if (!auth()->check() || (int) auth()->user()->role_id !== 1) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $bookItem->load(['book.category', 'book.ddcClass']);

        $books = Book::with(['category', 'ddcClass'])
            ->orderBy('title')
            ->get();

        $statusOptions = $this->statusOptions();
        $conditionOptions = $this->conditionOptions();

        return view('pustakawan.book_items.edit', compact(
            'bookItem',
            'books',
            'statusOptions',
            'conditionOptions'
        ));
    }

    public function update(Request $request, BookItem $bookItem)
    {
        if (!auth()->check() || (int) auth()->user()->role_id !== 1) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $validated = $request->validate([
            'book_id' => ['required', 'integer', 'exists:books,id'],
            'item_code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('book_items', 'item_code')->ignore($bookItem->id),
            ],
            'classification_code' => ['nullable', 'string', 'max:100'],
            'author_code' => ['nullable', 'string', 'max:100'],
            'title_code' => ['nullable', 'string', 'max:100'],
            'title_initial' => ['nullable', 'string', 'max:20'],
            'copy_number' => ['nullable', 'integer', 'min:1'],
            'status' => ['required', 'string', Rule::in(['tersedia', 'dipinjam', 'terlambat', 'rusak', 'hilang', 'nonaktif'])],
            'condition' => ['required', 'string', Rule::in(['baik', 'rusak ringan', 'rusak berat', 'hilang'])],
            'location' => ['nullable', 'string', 'max:255'],
            'acquisition_date' => ['nullable', 'date'],
        ], [
            'book_id.required' => 'Buku induk wajib dipilih.',
            'item_code.required' => 'Kode eksemplar wajib diisi.',
            'item_code.unique' => 'Kode eksemplar sudah digunakan.',
        ]);

        if ($this->hasActiveLoan($bookItem) && !in_array($validated['status'], ['dipinjam', 'terlambat'], true)) {
            return back()
                ->withInput()
                ->with('error_title', 'Status tidak bisa diubah')
                ->with('error_message', 'Eksemplar ini sedang dipinjam.')
                ->with('error_detail', 'Selesaikan pengembalian terlebih dahulu sebelum mengubah status eksemplar.');
        }

        $bookItem->update($this->filterColumns('book_items', [
            'book_id' => $validated['book_id'],
            'item_code' => $validated['item_code'],
            'classification_code' => $validated['classification_code'] ?? null,
            'author_code' => $validated['author_code'] ?? null,
            'title_code' => $validated['title_code'] ?? null,
            'title_initial' => $validated['title_initial'] ?? null,
            'copy_number' => $validated['copy_number'] ?? null,
            'status' => $validated['status'],
            'condition' => $validated['condition'],
            'location' => $validated['location'] ?? null,
            'acquisition_date' => $validated['acquisition_date'] ?? null,
        ]));

        return redirect()
            ->route('book_items.index')
            ->with('success_title', 'Eksemplar berhasil diperbarui')
            ->with('success_message', 'Copy "' . ($bookItem->item_code ?? '-') . '" berhasil diperbarui.')
            ->with('success_detail', 'Data eksemplar sudah tersimpan.');
    }

    public function destroy(BookItem $bookItem)
    {
        if (!auth()->check() || (int) auth()->user()->role_id !== 1) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $bookItem->load([
            'book',
            'loanItems.loan',
        ]);

        $itemCode = $bookItem->item_code ?? 'Eksemplar';

        if ($this->hasActiveLoan($bookItem)) {
            return redirect()
                ->route('book_items.index')
                ->with('error_title', 'Eksemplar tidak bisa diproses')
                ->with('error_message', 'Copy "' . $itemCode . '" sedang dipinjam.')
                ->with('error_detail', 'Selesaikan pengembalian terlebih dahulu sebelum copy ini dihapus atau dikeluarkan dari stok.');
        }

        $hasLoanHistory = $this->hasLoanHistory($bookItem);

        if ($bookItem->status === 'nonaktif' && $hasLoanHistory) {
            return redirect()
                ->route('book_items.index')
                ->with('error_title', 'Eksemplar sudah keluar dari stok')
                ->with('error_message', 'Copy "' . $itemCode . '" sudah dikeluarkan dari stok.')
                ->with('error_detail', 'Data copy tetap disimpan untuk menjaga riwayat transaksi.');
        }

        try {
            if ($hasLoanHistory) {
                $bookItem->update($this->filterColumns('book_items', [
                    'status' => 'nonaktif',
                ]));

                return redirect()
                    ->route('book_items.index')
                    ->with('success_title', 'Eksemplar dikeluarkan dari stok')
                    ->with('success_message', 'Copy "' . $itemCode . '" berhasil dikeluarkan dari stok.')
                    ->with('success_detail', 'Copy ini tidak dihapus permanen karena pernah memiliki riwayat peminjaman.');
            }

            $bookItem->delete();

            return redirect()
                ->route('book_items.index')
                ->with('success_title', 'Eksemplar berhasil dihapus')
                ->with('success_message', 'Copy "' . $itemCode . '" berhasil dihapus permanen.')
                ->with('success_detail', 'Copy ini belum memiliki riwayat peminjaman, sehingga aman dihapus.');
        } catch (QueryException $exception) {
            return redirect()
                ->route('book_items.index')
                ->with('error_title', 'Eksemplar gagal diproses')
                ->with('error_message', 'Copy "' . $itemCode . '" masih terhubung dengan data lain.')
                ->with('error_detail', 'Periksa kembali riwayat transaksi atau data terkait eksemplar ini.');
        }
    }

    public function bulkDestroy(Request $request)
    {
        if (!auth()->check() || (int) auth()->user()->role_id !== 1) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $bookItemIds = collect($request->input('book_item_ids', []))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($bookItemIds->isEmpty()) {
            return back()
                ->withErrors([
                    'book_item_ids' => 'Pilih minimal satu eksemplar yang ingin diproses.',
                ])
                ->withInput();
        }

        $bookItems = BookItem::with([
                'book',
                'loanItems.loan',
            ])
            ->whereIn('id', $bookItemIds)
            ->get();

        if ($bookItems->isEmpty()) {
            return back()
                ->with('error_title', 'Tidak ada eksemplar diproses')
                ->with('error_message', 'Eksemplar yang dipilih tidak ditemukan.')
                ->with('error_detail', 'Silakan refresh halaman lalu pilih kembali eksemplar.');
        }

        $blockedItems = $bookItems->filter(function ($bookItem) {
            return $this->hasActiveLoan($bookItem);
        });

        if ($blockedItems->isNotEmpty()) {
            return back()
                ->with('error_title', 'Sebagian eksemplar tidak bisa diproses')
                ->with('error_message', 'Ada eksemplar yang masih sedang dipinjam.')
                ->with('error_detail', 'Selesaikan pengembalian terlebih dahulu sebelum eksemplar tersebut diproses.');
        }

        $deletedCount = 0;
        $outOfStockCount = 0;
        $skippedCount = 0;

        DB::transaction(function () use ($bookItems, &$deletedCount, &$outOfStockCount, &$skippedCount) {
            foreach ($bookItems as $bookItem) {
                $hasLoanHistory = $this->hasLoanHistory($bookItem);

                if ($bookItem->status === 'nonaktif' && $hasLoanHistory) {
                    $skippedCount++;
                    continue;
                }

                if ($hasLoanHistory) {
                    $bookItem->update($this->filterColumns('book_items', [
                        'status' => 'nonaktif',
                    ]));

                    $outOfStockCount++;
                    continue;
                }

                $bookItem->delete();
                $deletedCount++;
            }
        });

        $messageParts = [];

        if ($deletedCount > 0) {
            $messageParts[] = $deletedCount . ' eksemplar dihapus permanen';
        }

        if ($outOfStockCount > 0) {
            $messageParts[] = $outOfStockCount . ' eksemplar dikeluarkan dari stok';
        }

        if ($skippedCount > 0) {
            $messageParts[] = $skippedCount . ' eksemplar dilewati karena sudah keluar dari stok';
        }

        if (empty($messageParts)) {
            return redirect()
                ->route('book_items.index')
                ->with('error_title', 'Tidak ada eksemplar diproses')
                ->with('error_message', 'Tidak ada perubahan pada eksemplar yang dipilih.')
                ->with('error_detail', 'Pilih eksemplar lain yang masih bisa diproses.');
        }

        return redirect()
            ->route('book_items.index')
            ->with('success_title', 'Eksemplar berhasil diproses')
            ->with('success_message', implode(', ', $messageParts) . '.')
            ->with('success_detail', 'Eksemplar yang pernah memiliki riwayat peminjaman tidak dihapus permanen agar riwayat transaksi tetap aman.');
    }

    private function hasActiveLoan(BookItem $bookItem): bool
    {
        return $bookItem->loanItems()
            ->whereIn('status', $this->activeLoanItemStatuses)
            ->whereHas('loan', function ($query) {
                $query->whereIn('status', $this->activeLoanStatuses);
            })
            ->exists();
    }

    private function hasLoanHistory(BookItem $bookItem): bool
    {
        if ($bookItem->relationLoaded('loanItems')) {
            return $bookItem->loanItems->isNotEmpty();
        }

        return $bookItem->loanItems()->exists();
    }

    private function nextCopyNumber(int $bookId): int
    {
        $lastCopyNumber = (int) BookItem::where('book_id', $bookId)
            ->max('copy_number');

        return $lastCopyNumber + 1;
    }

    private function generateItemCode(Book $book, int $copyNumber, array $payload = []): string
    {
        $classificationCode = $payload['classification_code']
            ?? $book->ddcClass?->code
            ?? $book->classification_code
            ?? 'BK';

        $authorCode = $payload['author_code']
            ?? Str::title(Str::substr(preg_replace('/[^A-Za-z0-9]/', '', (string) ($book->author ?? 'UNK')), 0, 4));

        $titleCode = $payload['title_code']
            ?? Str::lower(Str::substr(preg_replace('/[^A-Za-z0-9]/', '', (string) ($book->title ?? 'B')), 0, 1));

        $classificationCode = $this->cleanCode($classificationCode, 'BK');
        $authorCode = $this->cleanCode($authorCode, 'UNK');
        $titleCode = $this->cleanCode($titleCode, 'b');

        $baseCode = $classificationCode . '-' . $authorCode . '-' . $titleCode;

        $itemCode = $baseCode . '-' . str_pad((string) $copyNumber, 3, '0', STR_PAD_LEFT);

        while (BookItem::where('item_code', $itemCode)->exists()) {
            $copyNumber++;
            $itemCode = $baseCode . '-' . str_pad((string) $copyNumber, 3, '0', STR_PAD_LEFT);
        }

        return $itemCode;
    }

    private function cleanCode(?string $value, string $fallback): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return $fallback;
        }

        $value = preg_replace('/\s+/', '', $value);
        $value = preg_replace('/[^A-Za-z0-9\-]/', '', $value);

        return $value !== '' ? $value : $fallback;
    }

    private function statusFromCondition(string $condition): string
    {
        return match ($condition) {
            'hilang' => 'hilang',
            'rusak ringan', 'rusak berat' => 'rusak',
            default => 'tersedia',
        };
    }

    private function filterColumns(string $table, array $payload): array
    {
        return collect($payload)
            ->filter(function ($value, $column) use ($table) {
                return Schema::hasColumn($table, $column);
            })
            ->toArray();
    }

    private function statusOptions(): array
    {
        return [
            'tersedia' => 'Tersedia',
            'dipinjam' => 'Dipinjam',
            'terlambat' => 'Terlambat',
            'rusak' => 'Rusak',
            'hilang' => 'Hilang',
            'nonaktif' => 'Dikeluarkan dari Stok',
        ];
    }

    private function conditionOptions(): array
    {
        return [
            'baik' => 'Baik',
            'rusak ringan' => 'Rusak Ringan',
            'rusak berat' => 'Rusak Berat',
            'hilang' => 'Hilang',
        ];
    }
}