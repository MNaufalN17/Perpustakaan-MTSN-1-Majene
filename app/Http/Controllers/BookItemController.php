<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BookItemController extends Controller
{
    public function index()
    {
        $books = Book::with(['category', 'ddcClass'])
            ->withCount('bookItems')
            ->latest()
            ->paginate(10);

        return view('pustakawan.book_items.index', compact('books'));
    }

    public function create()
    {
        $books = Book::with(['category', 'ddcClass'])
            ->orderBy('title')
            ->get();

        $itemMaxCopies = BookItem::select('book_id', DB::raw('MAX(copy_number) as max_copy'))
            ->groupBy('book_id')
            ->pluck('max_copy', 'book_id');

        $booksData = $books->map(function ($book) use ($itemMaxCopies) {
            $lastCopyNumber = (int) ($itemMaxCopies[$book->id] ?? 0);

            return [
                'id' => $book->id,
                'title' => $book->title,
                'author' => $book->author,
                'author_code' => $book->author_code ?: $this->makeAuthorCode($book->author),
                'title_code' => $book->title_code ?: $this->makeTitleCode($book->title),
                'publisher' => $book->publisher,
                'publication_year' => $book->publication_year,
                'category' => $book->category->name ?? '-',
                'ddc_code' => $book->ddcClass->code ?? '000',
                'next_index' => $lastCopyNumber + 1,
            ];
        })->values();

        return view('pustakawan.book_items.create', compact('books', 'booksData'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'book_id' => ['required', 'exists:books,id'],
            'items' => ['required', 'array', 'min:1', 'max:200'],
            'items.*.copy_number' => ['required', 'integer', 'min:1'],
            'items.*.status' => ['required', Rule::in(['tersedia', 'dipinjam', 'hilang', 'nonaktif'])],
            'items.*.condition' => ['required', Rule::in(['baik', 'rusak ringan', 'rusak berat', 'hilang'])],
        ], $this->validationMessages(), $this->validationAttributes());

        $this->validateItemStatusCondition($validated['items']);

        $book = Book::with('ddcClass')->findOrFail($validated['book_id']);

        $classificationCode = $book->ddcClass->code ?? '000';
        $authorCode = $book->author_code ?: $this->makeAuthorCode($book->author);
        $titleCode = $book->title_code ?: $this->makeTitleCode($book->title);

        $rows = collect($validated['items'])->map(function ($item) use ($classificationCode, $authorCode, $titleCode) {
            $copyNumber = (int) $item['copy_number'];

            return [
                'copy_number' => $copyNumber,
                'item_code' => $this->buildItemCode($classificationCode, $authorCode, $titleCode, $copyNumber),
                'status' => $item['status'],
                'condition' => $item['condition'],
            ];
        })->values();

        $seenCodes = [];

        foreach ($rows as $index => $row) {
            if (in_array($row['item_code'], $seenCodes)) {
                throw ValidationException::withMessages([
                    "items.$index.copy_number" => 'Baris ke-' . ($index + 1) . ': nomor copy menghasilkan kode eksemplar yang sama dengan baris lain.',
                ]);
            }

            $seenCodes[] = $row['item_code'];

            if (BookItem::where('item_code', $row['item_code'])->exists()) {
                throw ValidationException::withMessages([
                    "items.$index.copy_number" => 'Baris ke-' . ($index + 1) . ': kode eksemplar "' . $row['item_code'] . '" sudah digunakan.',
                ]);
            }
        }

        DB::transaction(function () use ($book, $rows, $classificationCode, $authorCode, $titleCode) {
            foreach ($rows as $row) {
                BookItem::create([
                    'book_id' => $book->id,
                    'classification_code' => $classificationCode,
                    'author_code' => $authorCode,
                    'title_code' => $titleCode,
                    'title_initial' => $titleCode,
                    'copy_number' => $row['copy_number'],
                    'item_code' => $row['item_code'],
                    'status' => $row['status'],
                    'condition' => $row['condition'],
                ]);
            }
        });

        return redirect()
            ->route('book_items.index')
            ->with('success_title', 'Eksemplar berhasil ditambahkan')
            ->with('success_message', $rows->count() . ' eksemplar buku "' . $book->title . '" berhasil ditambahkan.')
            ->with('success_detail', 'Kode eksemplar mengikuti Buku Induk. Status dan kondisi disimpan untuk masing-masing copy.');
    }

    public function show(BookItem $bookItem)
    {
        $bookItem->load(['book.category', 'book.ddcClass', 'activeLoanItem.loan.member']);

        return view('pustakawan.book_items.show', compact('bookItem'));
    }

    public function edit(BookItem $bookItem)
    {
        $bookItem->load(['book.category', 'book.ddcClass', 'activeLoanItem.loan.member']);

        return view('pustakawan.book_items.edit', compact('bookItem'));
    }

    public function update(Request $request, BookItem $bookItem)
    {
        $bookItem->load(['book.ddcClass', 'activeLoanItem.loan.member']);

        $hasActiveLoan = $bookItem->activeLoanItem !== null;

        $validated = $request->validate([
            'copy_number' => ['required', 'integer', 'min:1'],
            'status' => ['required', Rule::in(['tersedia', 'dipinjam', 'hilang', 'nonaktif'])],
            'condition' => ['required', Rule::in(['baik', 'rusak ringan', 'rusak berat', 'hilang'])],
        ], $this->validationMessages(), $this->validationAttributes());

        if ($hasActiveLoan) {
            if ($validated['status'] !== 'dipinjam') {
                return back()
                    ->withInput()
                    ->with('error_title', 'Status tidak bisa diubah dari stok buku')
                    ->with('error_message', 'Eksemplar ini masih tercatat sedang dipinjam.')
                    ->with('error_detail', 'Selesaikan pengembaliannya melalui halaman Peminjaman agar data transaksi dan stok buku tetap sesuai.');
            }

            if ((int) $validated['copy_number'] !== (int) $bookItem->copy_number) {
                return back()
                    ->withInput()
                    ->with('error_title', 'Nomor copy tidak bisa diubah')
                    ->with('error_message', 'Eksemplar ini masih berada dalam transaksi peminjaman aktif.')
                    ->with('error_detail', 'Nomor copy dapat diubah setelah buku dikembalikan.');
            }
        }

        $this->validateSingleStatusCondition($validated['status'], $validated['condition']);

        $book = $bookItem->book;

        $classificationCode = $book->ddcClass->code ?? '000';
        $authorCode = $book->author_code ?: $this->makeAuthorCode($book->author);
        $titleCode = $book->title_code ?: $this->makeTitleCode($book->title);
        $copyNumber = (int) $validated['copy_number'];

        $itemCode = $this->buildItemCode($classificationCode, $authorCode, $titleCode, $copyNumber);

        $exists = BookItem::where('item_code', $itemCode)
            ->where('id', '!=', $bookItem->id)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'copy_number' => 'Nomor copy ini menghasilkan kode eksemplar "' . $itemCode . '" yang sudah digunakan.',
            ]);
        }

        $bookItem->update([
            'classification_code' => $classificationCode,
            'author_code' => $authorCode,
            'title_code' => $titleCode,
            'title_initial' => $titleCode,
            'copy_number' => $copyNumber,
            'item_code' => $itemCode,
            'status' => $validated['status'],
            'condition' => $validated['condition'],
        ]);

        return redirect()
            ->route('book_items.index')
            ->with('success_title', 'Eksemplar berhasil diperbarui')
            ->with('success_message', 'Data eksemplar "' . $bookItem->fresh()->item_code . '" berhasil diperbarui.')
            ->with('success_detail', 'Data stok buku sudah diperbarui.');
    }

    public function destroy(BookItem $bookItem)
    {
        if ($bookItem->hasActiveLoan()) {
            return redirect()
                ->route('book_items.index')
                ->with('error_title', 'Eksemplar tidak bisa dihapus')
                ->with('error_message', 'Eksemplar "' . $bookItem->item_code . '" masih berada dalam transaksi peminjaman aktif.')
                ->with('error_detail', 'Selesaikan pengembalian terlebih dahulu sebelum menghapus eksemplar.');
        }

        $itemCode = $bookItem->item_code;

        $bookItem->delete();

        return redirect()
            ->route('book_items.index')
            ->with('success_title', 'Eksemplar berhasil dihapus')
            ->with('success_message', 'Eksemplar "' . $itemCode . '" berhasil dihapus dari sistem.')
            ->with('success_detail', 'Data tersebut tidak akan tampil lagi pada stok fisik buku.');
    }

    private function validateItemStatusCondition(array $items): void
    {
        $messages = [];

        foreach ($items as $index => $item) {
            $row = $index + 1;
            $status = $item['status'] ?? null;
            $condition = $item['condition'] ?? null;

            if ($condition === 'hilang' && $status !== 'hilang') {
                $messages["items.$index.status"] = "Baris ke-$row: jika kondisi fisik hilang, status eksemplar harus hilang.";
            }

            if ($status === 'hilang' && $condition !== 'hilang') {
                $messages["items.$index.condition"] = "Baris ke-$row: jika status hilang, kondisi fisik juga harus hilang.";
            }
        }

        if (!empty($messages)) {
            throw ValidationException::withMessages($messages);
        }
    }

    private function validateSingleStatusCondition(string $status, string $condition): void
    {
        $messages = [];

        if ($condition === 'hilang' && $status !== 'hilang') {
            $messages['status'] = 'Jika kondisi fisik hilang, status eksemplar harus hilang.';
        }

        if ($status === 'hilang' && $condition !== 'hilang') {
            $messages['condition'] = 'Jika status hilang, kondisi fisik juga harus hilang.';
        }

        if (!empty($messages)) {
            throw ValidationException::withMessages($messages);
        }
    }

    private function buildItemCode(string $classificationCode, string $authorCode, string $titleCode, int $copyNumber): string
    {
        return $classificationCode . '-' . $authorCode . '-' . $titleCode . '-' . str_pad($copyNumber, 3, '0', STR_PAD_LEFT);
    }

    private function makeAuthorCode(?string $author): string
    {
        $letters = preg_replace('/[^a-zA-Z]/', '', $author ?? '');

        return $letters ? ucfirst(strtolower(substr($letters, 0, 3))) : 'Pen';
    }

    private function makeTitleCode(?string $title): string
    {
        $letters = preg_replace('/[^a-zA-Z0-9]/', '', $title ?? '');

        return $letters ? strtolower(substr($letters, 0, 1)) : 'b';
    }

    private function validationMessages(): array
    {
        return [
            'book_id.required' => 'Judul buku induk wajib dipilih.',
            'book_id.exists' => 'Buku induk yang dipilih tidak tersedia.',

            'items.required' => 'Minimal satu data eksemplar wajib dibuat.',
            'items.array' => 'Format data eksemplar tidak valid.',
            'items.min' => 'Minimal satu data eksemplar wajib dibuat.',
            'items.max' => 'Maksimal 200 eksemplar dalam satu kali simpan.',

            'items.*.copy_number.required' => 'Nomor copy wajib diisi.',
            'items.*.copy_number.integer' => 'Nomor copy harus berupa angka.',
            'items.*.copy_number.min' => 'Nomor copy minimal 1.',

            'items.*.status.required' => 'Status eksemplar wajib dipilih.',
            'items.*.status.in' => 'Status eksemplar yang dipilih tidak valid.',

            'items.*.condition.required' => 'Kondisi fisik wajib dipilih.',
            'items.*.condition.in' => 'Kondisi fisik yang dipilih tidak valid.',

            'copy_number.required' => 'Nomor copy wajib diisi.',
            'copy_number.integer' => 'Nomor copy harus berupa angka.',
            'copy_number.min' => 'Nomor copy minimal 1.',

            'status.required' => 'Status eksemplar wajib dipilih.',
            'status.in' => 'Status eksemplar yang dipilih tidak valid.',

            'condition.required' => 'Kondisi fisik wajib dipilih.',
            'condition.in' => 'Kondisi fisik yang dipilih tidak valid.',
        ];
    }

    private function validationAttributes(): array
    {
        return [
            'book_id' => 'Buku induk',
            'copy_number' => 'Nomor copy',
            'status' => 'Status eksemplar',
            'condition' => 'Kondisi fisik',
            'items.*.copy_number' => 'Nomor copy',
            'items.*.status' => 'Status eksemplar',
            'items.*.condition' => 'Kondisi fisik',
        ];
    }
}