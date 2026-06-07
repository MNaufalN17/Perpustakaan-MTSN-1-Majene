<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Menampilkan daftar semua kategori.
     */
    public function index(Request $request)
{
    if (!auth()->check() || (int) auth()->user()->role_id !== 1) {
        abort(403, 'Anda tidak memiliki akses.');
    }

    $keyword = trim((string) (
        $request->input('keyword')
        ?? $request->input('search')
        ?? $request->input('q')
        ?? ''
    ));

    $categories = \App\Models\Category::query()
        ->when($keyword !== '', function ($query) use ($keyword) {
            $query->where(function ($subQuery) use ($keyword) {
                $subQuery->where('name', 'like', "%{$keyword}%");

                if (\Illuminate\Support\Facades\Schema::hasColumn('categories', 'description')) {
                    $subQuery->orWhere('description', 'like', "%{$keyword}%");
                }
            });
        })
        ->orderBy('name')
        ->paginate(10)
        ->withQueryString();

    return view('pustakawan.categories.index', compact(
        'categories',
        'keyword'
    ));
}

    /**
     * Menampilkan form tambah kategori.
     */
    public function create()
    {
        return view('pustakawan.categories.create');
    }

    /**
     * Menyimpan kategori baru ke database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name',
            'description' => 'nullable|string|max:255',
        ], [
            'name.unique' => 'Nama kategori ini sudah ada di sistem.'
        ]);

        Category::create($validated);

        return redirect()->route('categories.index')->with('success', 'Kategori buku berhasil ditambahkan!');
    }

    /**
     * Menampilkan form edit kategori.
     */
    public function edit(Category $category)
    {
        return view('pustakawan.categories.edit', compact('category'));
    }

    /**
     * Menyimpan perubahan data kategori.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name,' . $category->id,
            'description' => 'nullable|string|max:255',
        ], [
            'name.unique' => 'Nama kategori ini sudah dipakai.'
        ]);

        $category->update($validated);

        return redirect()->route('categories.index')->with('success', 'Kategori buku berhasil diperbarui!');
    }

    /**
     * Menghapus kategori dari database.
     */
    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('categories.index')->with('success', 'Kategori buku berhasil dihapus!');
    }
}