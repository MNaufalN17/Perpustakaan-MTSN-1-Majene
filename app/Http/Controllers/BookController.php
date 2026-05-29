<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookItem;
use App\Models\Category;
use App\Models\DdcClass;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BookController extends Controller
{
    public function index()
    {
        $books = \App\Models\Book::with(['category', 'ddcClass'])
            ->withCount('bookItems')
            ->latest()
            ->paginate(10);

        if ((int) auth()->user()->role_id === 2) {
            return view('kepala_sekolah.books.index', compact('books'));
        }

        return view('pustakawan.books.index', compact('books'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $ddcClasses = DdcClass::orderBy('code')->get();

        return view('pustakawan.books.create', compact('categories', 'ddcClasses'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'price' => $request->filled('price')
                ? str_replace(',', '.', $request->price)
                : null,
        ]);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:150'],
            'author_code' => ['required', 'string', 'max:50'],
            'title_code' => ['required', 'string', 'max:50'],
            'publisher' => ['required', 'string', 'max:150'],
            'publication_year' => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'price' => ['nullable', 'numeric', 'min:0'],
            'category_id' => ['required', 'exists:categories,id'],
            'ddc_class_id' => ['required', 'exists:ddc_classes,id'],
            'borrowing_status' => ['required', Rule::in(['bisa dipinjam', 'tidak bisa dipinjam'])],
            'description' => ['nullable', 'string', 'max:2000'],
        ], $this->validationMessages(), $this->validationAttributes());

        $book = Book::create([
            'title' => trim($validated['title']),
            'author' => trim($validated['author']),
            'author_code' => trim($validated['author_code']),
            'title_code' => trim($validated['title_code']),
            'publisher' => trim($validated['publisher']),
            'publication_year' => $validated['publication_year'] ?? null,
            'price' => $validated['price'] ?? null,
            'category_id' => $validated['category_id'],
            'ddc_class_id' => $validated['ddc_class_id'],
            'is_borrowable' => $validated['borrowing_status'] === 'bisa dipinjam' ? 1 : 0,
            'description' => !empty($validated['description']) ? trim($validated['description']) : null,
        ]);

        // Automatically create one physical copy (BookItem) so the book appears in loan selector
        try {
            $classificationCode = $book->ddcClass->code ?? '000';
            $authorCode = $book->author_code ?: $this->makeAuthorCode($book->author);
            $titleCode = $book->title_code ?: $this->makeTitleCode($book->title);

            $copyNumber = 1;
            $itemCode = $this->buildItemCode($classificationCode, $authorCode, $titleCode, $copyNumber);

            // Ensure unique item_code (unlikely for a new book, but safe)
            while (BookItem::where('item_code', $itemCode)->exists()) {
                $copyNumber++;
                $itemCode = $this->buildItemCode($classificationCode, $authorCode, $titleCode, $copyNumber);
            }

            BookItem::create([
                'book_id' => $book->id,
                'item_code' => $itemCode,
                'classification_code' => $classificationCode,
                'author_code' => $authorCode,
                'title_code' => $titleCode,
                'title_initial' => $titleCode,
                'copy_number' => $copyNumber,
                'status' => 'tersedia',
                'condition' => 'baik',
            ]);
        } catch (\Throwable $e) {
            // If auto-creation fails, continue silently — book was still created.
        }

        return redirect()
            ->route('books.show', $book)
            ->with('success_title', 'Buku induk berhasil ditambahkan')
            ->with('success_message', 'Buku "' . $book->title . '" berhasil ditambahkan.')
            ->with('success_detail', 'Kode penulis dan kode judul akan digunakan otomatis pada kode eksemplar.');
    }

    public function show(\App\Models\Book $book)
    {
        $book->load(['category', 'ddcClass', 'bookItems']);

        if ((int) auth()->user()->role_id === 2) {
            return view('kepala_sekolah.books.show', compact('book'));
        }

        return view('pustakawan.books.show', compact('book'));
    }

    public function edit(Book $book)
    {
        $categories = Category::orderBy('name')->get();
        $ddcClasses = DdcClass::orderBy('code')->get();

        $book->load(['category', 'ddcClass']);

        return view('pustakawan.books.edit', compact('book', 'categories', 'ddcClasses'));
    }

    public function update(Request $request, Book $book)
    {
        $request->merge([
            'price' => $request->filled('price')
                ? str_replace(',', '.', $request->price)
                : null,
        ]);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:150'],
            'author_code' => ['required', 'string', 'max:50'],
            'title_code' => ['required', 'string', 'max:50'],
            'publisher' => ['required', 'string', 'max:150'],
            'publication_year' => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'price' => ['nullable', 'numeric', 'min:0'],
            'category_id' => ['required', 'exists:categories,id'],
            'ddc_class_id' => ['required', 'exists:ddc_classes,id'],
            'borrowing_status' => ['required', Rule::in(['bisa dipinjam', 'tidak bisa dipinjam'])],
            'description' => ['nullable', 'string', 'max:2000'],
        ], $this->validationMessages(), $this->validationAttributes());

        $book->update([
            'title' => trim($validated['title']),
            'author' => trim($validated['author']),
            'author_code' => trim($validated['author_code']),
            'title_code' => trim($validated['title_code']),
            'publisher' => trim($validated['publisher']),
            'publication_year' => $validated['publication_year'] ?? null,
            'price' => $validated['price'] ?? null,
            'category_id' => $validated['category_id'],
            'ddc_class_id' => $validated['ddc_class_id'],
            'is_borrowable' => $validated['borrowing_status'] === 'bisa dipinjam' ? 1 : 0,
            'description' => !empty($validated['description']) ? trim($validated['description']) : null,
        ]);

        $syncResult = $this->syncBookItemsFromBook($book->fresh());

        return redirect()
            ->route('books.show', $book)
            ->with('success_title', 'Buku induk berhasil diperbarui')
            ->with('success_message', 'Data buku "' . $book->title . '" berhasil diperbarui.')
            ->with('success_detail', 'Sebanyak ' . $syncResult['updated'] . ' eksemplar berhasil disinkronkan. ' . $syncResult['skipped'] . ' eksemplar dilewati karena konflik kode.');
    }

    public function destroy(Book $book)
    {
        if ($book->bookItems()->count() > 0) {
            return redirect()
                ->route('books.index')
                ->with('error_title', 'Buku tidak bisa dihapus')
                ->with('error_message', 'Buku "' . $book->title . '" masih memiliki data eksemplar.')
                ->with('error_detail', 'Hapus eksemplar buku terlebih dahulu sebelum menghapus buku induk.');
        }

        $title = $book->title;

        $book->delete();

        return redirect()
            ->route('books.index')
            ->with('success_title', 'Buku induk berhasil dihapus')
            ->with('success_message', 'Buku "' . $title . '" berhasil dihapus dari katalog.')
            ->with('success_detail', 'Data buku tidak akan tampil lagi pada daftar buku induk.');
    }

    private function syncBookItemsFromBook(Book $book): array
    {
        $book->load(['ddcClass', 'bookItems']);

        $classificationCode = $book->ddcClass->code ?? '000';
        $authorCode = $book->author_code ?: $this->makeAuthorCode($book->author);
        $titleCode = $book->title_code ?: $this->makeTitleCode($book->title);

        $updated = 0;
        $skipped = 0;

        foreach ($book->bookItems as $item) {
            $copyNumber = (int) ($item->copy_number ?: 1);

            $newItemCode = $this->buildItemCode(
                $classificationCode,
                $authorCode,
                $titleCode,
                $copyNumber
            );

            $exists = BookItem::where('item_code', $newItemCode)
                ->where('id', '!=', $item->id)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            $item->update([
                'classification_code' => $classificationCode,
                'author_code' => $authorCode,
                'title_code' => $titleCode,
                'title_initial' => $titleCode,
                'item_code' => $newItemCode,
            ]);

            $updated++;
        }

        return [
            'updated' => $updated,
            'skipped' => $skipped,
        ];
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
            'title.required' => 'Judul buku wajib diisi.',
            'author.required' => 'Penulis wajib diisi.',
            'author_code.required' => 'Kode penulis wajib diisi.',
            'title_code.required' => 'Kode judul wajib diisi.',
            'publisher.required' => 'Penerbit wajib diisi.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori yang dipilih tidak tersedia.',
            'ddc_class_id.required' => 'Kelas DDC wajib dipilih.',
            'ddc_class_id.exists' => 'Kelas DDC yang dipilih tidak tersedia.',
            'borrowing_status.required' => 'Status peminjaman wajib dipilih.',
            'borrowing_status.in' => 'Status peminjaman yang dipilih tidak valid.',
            'publication_year.integer' => 'Tahun terbit harus berupa angka.',
            'price.numeric' => 'Harga buku harus berupa angka.',
        ];
    }

    private function validationAttributes(): array
    {
        return [
            'title' => 'Judul buku',
            'author' => 'Penulis',
            'author_code' => 'Kode penulis',
            'title_code' => 'Kode judul',
            'publisher' => 'Penerbit',
            'publication_year' => 'Tahun terbit',
            'price' => 'Harga buku',
            'category_id' => 'Kategori',
            'ddc_class_id' => 'Kelas DDC',
            'borrowing_status' => 'Status peminjaman',
            'description' => 'Deskripsi',
        ];
    }
}
