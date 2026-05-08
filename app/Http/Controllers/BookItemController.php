<?php

namespace App\Http\Controllers;

use App\Models\BookItem;
use App\Models\Book;
use Illuminate\Http\Request;

class BookItemController extends Controller
{
    /**
     * Menampilkan daftar semua copy fisik/eksemplar buku.
     */
    public function index()
    {
        // Mengambil semua data fisik buku dan memuat data judul bukunya (relasi)
        $bookItems = BookItem::with('book')->latest()->get();
        
        return view('pustakawan.book_items.index', compact('bookItems'));
    }

    /**
     * Menampilkan form untuk menambah eksemplar fisik baru.
     */
    public function create()
    {
        // Mengambil semua judul buku untuk ditampilkan di pilihan dropdown
        $books = Book::all();
        
        return view('pustakawan.book_items.create', compact('books'));
    }

    /**
     * Menyimpan data eksemplar fisik baru ke dalam database.
     */
    public function store(Request $request)
    {
        // 1. Validasi inputan dari form
        $validatedData = $request->validate([
            'book_id' => 'required|exists:books,id',
            'item_code' => 'required|string|unique:book_items,item_code', // Kode harus unik
            'classification_code' => 'required|string|max:50',
            'author_code' => 'required|string|max:50',
            'title_initial' => 'required|string|max:10',
            'copy_number' => 'required|integer|min:1',
            'status' => 'required|in:tersedia,dipinjam,rusak,hilang,nonaktif',
            'condition' => 'required|in:baik,rusak ringan,rusak berat,hilang',
            'location' => 'nullable|string|max:255',
            'acquisition_date' => 'nullable|date',
        ]);

        // 2. Simpan data fisik buku ke database
        BookItem::create($validatedData);

        // 3. Kembalikan ke halaman daftar dengan pesan sukses
        return redirect()->route('book_items.index')->with('success', 'Data fisik buku berhasil ditambahkan!');
    }

    /**
     * Menampilkan detail satu eksemplar fisik buku.
     */
    public function show(BookItem $bookItem)
    {
        // Opsional: Muat relasi ke judul buku
        $bookItem->load('book');
        return view('pustakawan.book_items.show', compact('bookItem'));
    }

    /**
     * Menampilkan form untuk mengedit data eksemplar fisik.
     */
    public function edit(BookItem $bookItem)
    {
        $books = Book::all();
        return view('pustakawan.book_items.edit', compact('bookItem', 'books'));
    }

    /**
     * Memperbarui data eksemplar fisik di database.
     */
    public function update(Request $request, BookItem $bookItem)
    {
        // Validasi, pastikan item_code unik kecuali untuk id buku ini sendiri
        $validatedData = $request->validate([
            'book_id' => 'required|exists:books,id',
            'item_code' => 'required|string|unique:book_items,item_code,' . $bookItem->id,
            'classification_code' => 'required|string|max:50',
            'author_code' => 'required|string|max:50',
            'title_initial' => 'required|string|max:10',
            'copy_number' => 'required|integer|min:1',
            'status' => 'required|in:tersedia,dipinjam,rusak,hilang,nonaktif',
            'condition' => 'required|in:baik,rusak ringan,rusak berat,hilang',
            'location' => 'nullable|string|max:255',
            'acquisition_date' => 'nullable|date',
        ]);

        $bookItem->update($validatedData);

        return redirect()->route('book_items.index')->with('success', 'Data fisik buku berhasil diperbarui!');
    }

    /**
     * Menghapus (atau mendata sebagai hilang) eksemplar fisik.
     */
    public function destroy(BookItem $bookItem)
    {
        // Hapus data fisik buku
        $bookItem->delete();

        return redirect()->route('book_items.index')->with('success', 'Data fisik buku berhasil dihapus!');
    }
}