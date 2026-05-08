<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Models\DdcClass;
use Illuminate\Http\Request;

class BookController extends Controller
{
    /**
     * Menampilkan daftar semua judul buku.
     */
    public function index()
    {
        // Mengambil data buku beserta relasinya ke tabel Kategori dan DDC Class
        // latest() untuk mengurutkan dari data yang paling baru ditambahkan
        $books = Book::with(['category', 'ddcClass'])->latest()->get();
        
        return view('pustakawan.books.index', compact('books'));
    }

    /**
     * Menampilkan form untuk menambah judul buku baru.
     */
    public function create()
    {
        // Mengambil semua data kategori dan DDC untuk ditampilkan di pilihan dropdown
        $categories = Category::all();
        $ddcClasses = DdcClass::all();
        
        return view('pustakawan.books.create', compact('categories', 'ddcClasses'));
    }

    /**
     * Menyimpan data buku baru ke dalam database.
     */
    public function store(Request $request)
    {
        // 1. Validasi inputan dari form
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'publisher' => 'required|string|max:255',
            'publication_year' => 'required|digits:4|integer|min:1900|max:' . (date('Y') + 1),
            'category_id' => 'nullable|exists:categories,id',
            'ddc_class_id' => 'nullable|exists:ddc_classes,id',
            'price' => 'nullable|numeric|min:0',
            'is_borrowable' => 'required|boolean', // 1 untuk boleh dipinjam, 0 untuk baca di tempat (referensi)
            'description' => 'nullable|string',
        ]);

        // 2. Simpan ke database
        Book::create($validatedData);

        // 3. Kembalikan ke halaman daftar buku dengan pesan sukses
        return redirect()->route('books.index')->with('success', 'Data judul buku berhasil ditambahkan!');
    }

    /**
     * Menampilkan detail satu judul buku.
     */
    public function show(Book $book)
    {
        // Memuat relasi bookItems agar saat melihat detail judul, 
        // kita juga bisa melihat daftar eksemplar fisik/copy dari buku tersebut
        $book->load('bookItems'); 
        
        return view('pustakawan.books.show', compact('book'));
    }

    /**
     * Menampilkan form untuk mengedit data buku.
     */
    public function edit(Book $book)
    {
        $categories = Category::all();
        $ddcClasses = DdcClass::all();
        
        return view('pustakawan.books.edit', compact('book', 'categories', 'ddcClasses'));
    }

    /**
     * Memperbarui data buku di database.
     */
    public function update(Request $request, Book $book)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'publisher' => 'required|string|max:255',
            'publication_year' => 'required|digits:4|integer|min:1900|max:' . (date('Y') + 1),
            'category_id' => 'nullable|exists:categories,id',
            'ddc_class_id' => 'nullable|exists:ddc_classes,id',
            'price' => 'nullable|numeric|min:0',
            'is_borrowable' => 'required|boolean',
            'description' => 'nullable|string',
        ]);

        $book->update($validatedData);

        return redirect()->route('books.index')->with('success', 'Data judul buku berhasil diperbarui!');
    }

    /**
     * Menghapus data buku.
     */
    public function destroy(Book $book)
    {
        $book->delete();

        return redirect()->route('books.index')->with('success', 'Data judul buku berhasil dihapus!');
    }
}