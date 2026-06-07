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
        $this->authorizeViewer();

        $keyword = trim((string) $request->input('keyword', ''));
        $status = trim((string) $request->input('status', ''));
        $condition = trim((string) $request->input('condition', ''));

        $bookItems = BookItem::with([
                'book.category',
                'book.ddcClass',
                'loanItems.loan.member',
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
        $this->authorizePustakawan();

        $books = Book::with(['category', 'ddcClass'])
            ->orderBy('title')
            ->get();

        $booksData = $books->map(function (Book $book) {
            return [
                'id' => $book->id,
                'title' => $book->title,
                'author' => $book->author,
                'ddc_code' => $this->cleanCode($book->ddcClass?->code, 'BK'),
                'author_code' => $this->cleanCode($book->author_code ?: $this->makeAuthorCode($book->author), 'UNK'),
                'title_code' => $this->cleanCode($book->title_code ?: $this->makeTitleCode($book->title), 'b'),
                'next_index' => $this->nextCopyNumber((int) $book->id),
            ];
        })->values();

        $statusOptions = $this->statusOptions();
        $conditionOptions = $this->conditionOptions();

        return view('pustakawan.book_items.create', compact(
            'books',
            'booksData',
            'statusOptions',
            'conditionOptions'
        ));
    }

    public function store(Request $request)
    {
        $this->authorizePustakawan();

        if ($request->has('items')) {
            return $this->storeMany($request);
        }

        $validated = $request->validate([
            'book_id' => ['required', 'integer', 'exists:books,id'],
            'item_code' => ['nullable', 'string', 'max:100', 'unique:book_items,item_code'],
            'classification_code' => ['nullable', 'string', 'max:100'],
            'author_code' => ['nullable', 'string', 'max:100'],
            'title_code' => ['nullable', 'string', 'max:100'],
            'title_initial' => ['nullable', 'string', 'max:20'],
            'copy_number' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'string', Rule::in(['tersedia', 'rusak', 'hilang', 'nonaktif'])],
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
        $codeParts = $this->bookCodeParts($book);

        $copyNumber = (int) ($validated['copy_number'] ?? $this->nextCopyNumber((int) $validated['book_id']));

        if ($this->copyNumberExists((int) $validated['book_id'], $copyNumber)) {
            return back()
                ->withInput()
                ->withErrors([
                    'copy_number' => 'Nomor copy ' . $copyNumber . ' sudah tersedia untuk buku ini.',
                ]);
        }

        $itemCode = trim((string) ($validated['item_code'] ?? ''));

        if ($itemCode === '') {
            $itemCode = $this->generateItemCode($book, $copyNumber, $codeParts);
        }

        if (BookItem::where('item_code', $itemCode)->exists()) {
            return back()
                ->withInput()
                ->withErrors([
                    'item_code' => 'Kode eksemplar "' . $itemCode . '" sudah digunakan. Ubah kode klasifikasi/penulis/judul atau gunakan nomor copy lain.',
                ]);
        }

        $condition = $this->normalizeCondition($validated['condition'] ?? 'baik');
        $requestedStatus = strtolower(trim((string) ($validated['status'] ?? 'tersedia')));

        if (! $this->isAllowedStatusConditionPair($requestedStatus, $condition, false)) {
            return back()
                ->withInput()
                ->withErrors([
                    'condition' => 'Kombinasi status dan kondisi tidak valid. Tersedia hanya boleh Baik, Rusak hanya boleh Rusak Ringan/Rusak Berat, Hilang hanya boleh Hilang.',
                ]);
        }

        $status = $this->normalizeStatusForCondition($requestedStatus, $condition, false);

        $bookItem = BookItem::create($this->filterColumns('book_items', [
            'book_id' => $validated['book_id'],
            'item_code' => $itemCode,
            'classification_code' => $validated['classification_code'] ?? $codeParts['classification_code'],
            'author_code' => $validated['author_code'] ?? $codeParts['author_code'],
            'title_code' => $validated['title_code'] ?? $codeParts['title_code'],
            'title_initial' => $validated['title_initial'] ?? $codeParts['title_code'],
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

    private function storeMany(Request $request)
    {
        $validated = $request->validate([
            'book_id' => ['required', 'integer', 'exists:books,id'],
            'items' => ['required', 'array', 'min:1', 'max:200'],
            'items.*.copy_number' => ['required', 'integer', 'min:1', 'distinct'],
            'items.*.status' => ['required', 'string', Rule::in(['tersedia', 'rusak', 'hilang', 'nonaktif'])],
            'items.*.condition' => ['required', 'string', Rule::in(['baik', 'rusak ringan', 'rusak berat', 'hilang'])],
        ], [
            'book_id.required' => 'Buku induk wajib dipilih.',
            'book_id.exists' => 'Buku induk yang dipilih tidak ditemukan.',
            'items.required' => 'Minimal satu eksemplar wajib dibuat.',
            'items.min' => 'Minimal satu eksemplar wajib dibuat.',
            'items.max' => 'Maksimal 200 eksemplar dalam satu kali simpan.',
            'items.*.copy_number.required' => 'Nomor copy wajib diisi.',
            'items.*.copy_number.distinct' => 'Nomor copy tidak boleh sama dalam satu kali simpan.',
        ]);

        $book = Book::with(['ddcClass'])->findOrFail($validated['book_id']);
        $codeParts = $this->bookCodeParts($book);
        $rows = collect($validated['items'])->values();

        foreach ($rows as $index => $row) {
            $requestedStatus = strtolower(trim((string) ($row['status'] ?? 'tersedia')));
            $condition = $this->normalizeCondition($row['condition'] ?? 'baik');

            if (! $this->isAllowedStatusConditionPair($requestedStatus, $condition, false)) {
                return back()
                    ->withInput()
                    ->withErrors([
                        "items.{$index}.condition" => 'Kombinasi status dan kondisi pada baris ke-' . ($index + 1) . ' tidak valid.',
                    ]);
            }
        }

        $existingCopyNumbers = BookItem::where('book_id', $book->id)
            ->whereIn('copy_number', $rows->pluck('copy_number')->all())
            ->pluck('copy_number')
            ->map(fn ($copyNumber) => (int) $copyNumber)
            ->all();

        if (! empty($existingCopyNumbers)) {
            return back()
                ->withInput()
                ->withErrors([
                    'items' => 'Nomor copy ' . implode(', ', $existingCopyNumbers) . ' sudah tersedia untuk buku ini.',
                ]);
        }

        $generatedItemCodes = $rows
            ->map(function ($row) use ($book, $codeParts) {
                return $this->generateItemCode($book, (int) $row['copy_number'], $codeParts);
            })
            ->values();

        $duplicateItemCodes = $generatedItemCodes
            ->duplicates()
            ->values()
            ->all();

        if (! empty($duplicateItemCodes)) {
            return back()
                ->withInput()
                ->withErrors([
                    'items' => 'Ada kode eksemplar yang duplikat dalam input: ' . implode(', ', $duplicateItemCodes) . '.',
                ]);
        }

        $existingItemCodes = BookItem::whereIn('item_code', $generatedItemCodes->all())
            ->pluck('item_code')
            ->all();

        if (! empty($existingItemCodes)) {
            return back()
                ->withInput()
                ->withErrors([
                    'items' => 'Kode eksemplar ' . implode(', ', $existingItemCodes) . ' sudah digunakan. Item code tidak akan dinaikkan otomatis agar tetap selaras dengan nomor copy.',
                ]);
        }

        $createdCount = 0;
        $firstItemCode = null;

        DB::transaction(function () use ($book, $codeParts, $rows, &$createdCount, &$firstItemCode) {
            foreach ($rows as $row) {
                $copyNumber = (int) $row['copy_number'];
                $condition = $this->normalizeCondition($row['condition']);
                $requestedStatus = strtolower(trim((string) ($row['status'] ?? 'tersedia')));
                $status = $this->normalizeStatusForCondition($requestedStatus, $condition, false);
                $itemCode = $this->generateItemCode($book, $copyNumber, $codeParts);

                $bookItem = BookItem::create($this->filterColumns('book_items', [
                    'book_id' => $book->id,
                    'item_code' => $itemCode,
                    'classification_code' => $codeParts['classification_code'],
                    'author_code' => $codeParts['author_code'],
                    'title_code' => $codeParts['title_code'],
                    'title_initial' => $codeParts['title_code'],
                    'copy_number' => $copyNumber,
                    'status' => $status,
                    'condition' => $condition,
                    'location' => null,
                    'acquisition_date' => null,
                ]));

                $createdCount++;
                $firstItemCode ??= $bookItem->item_code;
            }
        });

        return redirect()
            ->route('book_items.index')
            ->with('success_title', 'Eksemplar berhasil ditambahkan')
            ->with('success_message', $createdCount . ' eksemplar untuk buku "' . $book->title . '" berhasil ditambahkan.')
            ->with('success_detail', 'Kode pertama: ' . ($firstItemCode ?? '-') . '. Stok fisik buku sudah diperbarui.');
    }

    public function show(BookItem $bookItem)
    {
        $this->authorizeViewer();

        $bookItem->load([
            'book.category',
            'book.ddcClass',
            'loanItems.loan.member.studentClass',
        ]);

        return view('pustakawan.book_items.show', compact('bookItem'));
    }

    public function edit(BookItem $bookItem)
    {
        $this->authorizePustakawan();

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
        $this->authorizePustakawan();

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
            'status' => ['required', 'string', Rule::in(['tersedia', 'dipinjam', 'rusak', 'hilang', 'nonaktif'])],
            'condition' => ['required', 'string', Rule::in(['baik', 'rusak ringan', 'rusak berat', 'hilang'])],
            'location' => ['nullable', 'string', 'max:255'],
            'acquisition_date' => ['nullable', 'date'],
        ], [
            'book_id.required' => 'Buku induk wajib dipilih.',
            'item_code.required' => 'Kode eksemplar wajib diisi.',
            'item_code.unique' => 'Kode eksemplar sudah digunakan.',
        ]);

        $copyNumber = $validated['copy_number'] !== null
            ? (int) $validated['copy_number']
            : null;

        if ($copyNumber !== null && $this->copyNumberExists((int) $validated['book_id'], $copyNumber, (int) $bookItem->id)) {
            return back()
                ->withInput()
                ->withErrors([
                    'copy_number' => 'Nomor copy ' . $copyNumber . ' sudah tersedia untuk buku ini.',
                ]);
        }

        $hasActiveLoan = $this->hasActiveLoan($bookItem);
        $condition = $this->normalizeCondition($validated['condition']);
        $requestedStatus = strtolower(trim((string) $validated['status']));

        if ($hasActiveLoan && $requestedStatus !== 'dipinjam') {
            return back()
                ->withInput()
                ->with('error_title', 'Status tidak bisa diubah')
                ->with('error_message', 'Eksemplar ini sedang dipinjam.')
                ->with('error_detail', 'Selesaikan pengembalian terlebih dahulu sebelum mengubah status eksemplar.');
        }

        if ($hasActiveLoan && $condition !== $this->normalizeCondition((string) ($bookItem->condition ?? 'baik'))) {
            return back()
                ->withInput()
                ->with('error_title', 'Kondisi tidak bisa diubah')
                ->with('error_message', 'Eksemplar ini sedang dipinjam.')
                ->with('error_detail', 'Kondisi eksemplar yang sedang dipinjam harus diperbarui lewat proses pengembalian.');
        }

        if (! $this->isAllowedStatusConditionPair($requestedStatus, $condition, $hasActiveLoan)) {
            return back()
                ->withInput()
                ->withErrors([
                    'condition' => 'Kombinasi status dan kondisi tidak valid.',
                ]);
        }

        $status = $this->normalizeStatusForCondition($requestedStatus, $condition, $hasActiveLoan);

        $bookItem->update($this->filterColumns('book_items', [
            'book_id' => $validated['book_id'],
            'item_code' => $validated['item_code'],
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
            ->with('success_title', 'Eksemplar berhasil diperbarui')
            ->with('success_message', 'Copy "' . ($bookItem->item_code ?? '-') . '" berhasil diperbarui.')
            ->with('success_detail', 'Data eksemplar sudah tersimpan. Status sudah diselaraskan dengan kondisi eksemplar.');
    }

    public function destroy(BookItem $bookItem)
    {
        $this->authorizePustakawan();

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
        $this->authorizePustakawan();

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

    public function restoreToStock(Request $request, BookItem $bookItem)
{
    if (!auth()->check() || (int) auth()->user()->role_id !== 1) {
        abort(403, 'Anda tidak memiliki akses.');
    }

    $bookItem->load(['book', 'loanItems.loan']);

    $itemCode = $bookItem->item_code ?? 'Eksemplar';

    if ($this->hasActiveLoan($bookItem)) {
        return redirect()
            ->route('book_items.index')
            ->with('error_title', 'Eksemplar sedang dipinjam')
            ->with('error_message', 'Copy "' . $itemCode . '" belum bisa dimasukkan kembali ke stok.')
            ->with('error_detail', 'Selesaikan pengembalian terlebih dahulu.');
    }

    if ($bookItem->status !== 'nonaktif') {
        return redirect()
            ->route('book_items.index')
            ->with('error_title', 'Eksemplar sudah berada di stok')
            ->with('error_message', 'Copy "' . $itemCode . '" tidak sedang dikeluarkan dari stok.')
            ->with('error_detail', 'Tidak ada perubahan yang perlu dilakukan.');
    }

    if (in_array($bookItem->condition, ['hilang', 'rusak berat'], true)) {
        return redirect()
            ->route('book_items.index')
            ->with('error_title', 'Kondisi belum layak')
            ->with('error_message', 'Copy "' . $itemCode . '" belum bisa dimasukkan kembali ke stok.')
            ->with('error_detail', 'Ubah kondisi eksemplar melalui menu Edit menjadi Baik atau Rusak Ringan terlebih dahulu.');
    }

    if (!in_array($bookItem->condition, ['baik', 'rusak ringan'], true)) {
        return redirect()
            ->route('book_items.index')
            ->with('error_title', 'Kondisi tidak valid')
            ->with('error_message', 'Copy "' . $itemCode . '" belum bisa dimasukkan kembali ke stok.')
            ->with('error_detail', 'Pastikan kondisi eksemplar sudah benar.');
    }

    $bookItem->update($this->filterColumns('book_items', [
        'status' => 'tersedia',
    ]));

    return redirect()
        ->route('book_items.index')
        ->with('success_title', 'Eksemplar masuk kembali ke stok')
        ->with('success_message', 'Copy "' . $itemCode . '" berhasil dimasukkan kembali ke stok.')
        ->with('success_detail', 'Eksemplar sekarang berstatus tersedia dan dapat digunakan kembali.');
}

    private function authorizePustakawan(): void
    {
        if (! auth()->check() || (int) auth()->user()->role_id !== 1) {
            abort(403, 'Anda tidak memiliki akses.');
        }
    }

    private function authorizeViewer(): void
    {
        if (! auth()->check() || ! in_array((int) auth()->user()->role_id, [1, 2], true)) {
            abort(403, 'Anda tidak memiliki akses.');
        }
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

    private function copyNumberExists(int $bookId, int $copyNumber, ?int $ignoreBookItemId = null): bool
    {
        $query = BookItem::where('book_id', $bookId)
            ->where('copy_number', $copyNumber);

        if ($ignoreBookItemId) {
            $query->where('id', '!=', $ignoreBookItemId);
        }

        return $query->exists();
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

        return $classificationCode . '-' . $authorCode . '-' . $titleCode . '-' . str_pad((string) $copyNumber, 3, '0', STR_PAD_LEFT);
    }

    private function bookCodeParts(Book $book): array
    {
        return [
            'classification_code' => $this->cleanCode($book->ddcClass?->code, 'BK'),
            'author_code' => $this->cleanCode($book->author_code ?: $this->makeAuthorCode($book->author), 'UNK'),
            'title_code' => $this->cleanCode($book->title_code ?: $this->makeTitleCode($book->title), 'b'),
        ];
    }

    private function makeAuthorCode(?string $author): string
    {
        $letters = preg_replace('/[^A-Za-z0-9]/', '', (string) $author);

        return $letters !== ''
            ? Str::title(Str::substr($letters, 0, 4))
            : 'UNK';
    }

    private function makeTitleCode(?string $title): string
    {
        $letters = preg_replace('/[^A-Za-z0-9]/', '', (string) $title);

        return $letters !== ''
            ? Str::lower(Str::substr($letters, 0, 1))
            : 'b';
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

    private function normalizeCondition(?string $condition): string
    {
        $condition = strtolower(trim((string) $condition));

        return in_array($condition, ['baik', 'rusak ringan', 'rusak berat', 'hilang'], true)
            ? $condition
            : 'baik';
    }

    private function normalizeStatusForCondition(?string $requestedStatus, string $condition, bool $hasActiveLoan = false): string
    {
        $requestedStatus = strtolower(trim((string) $requestedStatus));
        $condition = $this->normalizeCondition($condition);

        if ($hasActiveLoan) {
            return 'dipinjam';
        }

        if ($requestedStatus === 'nonaktif') {
            return 'nonaktif';
        }

        return match ($condition) {
            'hilang' => 'hilang',
            'rusak ringan', 'rusak berat' => 'rusak',
            default => 'tersedia',
        };
    }

    private function isAllowedStatusConditionPair(?string $status, ?string $condition, bool $hasActiveLoan = false): bool
    {
        $status = strtolower(trim((string) $status));
        $condition = $this->normalizeCondition($condition);

        if ($hasActiveLoan) {
            return $status === 'dipinjam';
        }

        return match ($status) {
            'tersedia' => $condition === 'baik',
            'rusak' => in_array($condition, ['rusak ringan', 'rusak berat'], true),
            'hilang' => $condition === 'hilang',
            'nonaktif' => in_array($condition, ['baik', 'rusak ringan', 'rusak berat', 'hilang'], true),
            default => false,
        };
    }

    private function statusFromCondition(string $condition): string
    {
        return match ($this->normalizeCondition($condition)) {
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
