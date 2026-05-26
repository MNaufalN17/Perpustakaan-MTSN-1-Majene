<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
                    Data Koleksi
                </p>
                <h2 class="mt-1 text-xl font-bold text-gray-900">
                    Katalog Buku Induk
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Halaman ini bersifat read-only untuk Kepala Sekolah/Kepala Perpustakaan.
                </p>
            </div>

            <a href="{{ route('kepala_sekolah.dashboard') }}"
               class="inline-flex items-center justify-center gap-2 rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-600 transition hover:bg-gray-50">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Dashboard
            </a>
        </div>
    </x-slot>

    @php
        $bookCollection = method_exists($books, 'getCollection') ? $books->getCollection() : $books;
        $bookCount = method_exists($books, 'total') ? $books->total() : $bookCollection->count();
    @endphp

    <div class="py-10 bg-gradient-to-br from-slate-50 via-emerald-50/40 to-sky-50/40 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/80 shadow-[0_18px_50px_rgba(15,23,42,0.06)] backdrop-blur-xl">
                <div class="relative overflow-hidden bg-gradient-to-r from-emerald-700 to-teal-500 p-6">
                    <div class="absolute -right-16 -top-20 h-52 w-52 rounded-full bg-white/10 blur-2xl"></div>
                    <div class="absolute -left-20 bottom-0 h-48 w-48 rounded-full bg-emerald-200/20 blur-2xl"></div>

                    <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-white text-lg font-semibold">
                                Daftar Buku Induk
                            </h3>
                            <p class="text-emerald-50 mt-1 text-sm">
                                Kepala sekolah hanya dapat melihat data koleksi tanpa mengubah data.
                            </p>
                        </div>

                        <div class="flex items-center gap-3 rounded-2xl border border-white/20 bg-white/15 px-4 py-3 text-white">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20">
                                <span class="material-symbols-outlined">library_books</span>
                            </div>
                            <div>
                                <p class="text-xs text-emerald-50">Total Buku Induk</p>
                                <p class="text-lg font-bold">{{ number_format($bookCount, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <div class="overflow-x-auto rounded-3xl border border-gray-100 bg-white">
                        <table class="min-w-[1100px] w-full divide-y divide-gray-100 text-left text-sm">
                            <thead class="bg-slate-50 text-xs uppercase tracking-wider text-gray-500">
                                <tr>
                                    <th class="px-5 py-4 font-bold w-[280px]">Judul</th>
                                    <th class="px-5 py-4 font-bold w-[160px]">Penulis</th>
                                    <th class="px-5 py-4 font-bold w-[160px]">Penerbit</th>
                                    <th class="px-5 py-4 font-bold w-[170px]">Kategori</th>
                                    <th class="px-5 py-4 text-center font-bold w-[100px]">DDC</th>
                                    <th class="px-5 py-4 text-center font-bold w-[120px]">Stok</th>
                                    <th class="px-5 py-4 text-center font-bold w-[160px]">Status</th>
                                    <th class="px-5 py-4 text-center font-bold w-[120px]">Aksi</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse($books as $book)
                                    @php
                                        $stockCount = $book->book_items_count ?? $book->bookItems()->count();
                                    @endphp

                                    <tr class="transition hover:bg-emerald-50/40">
                                        <td class="px-5 py-5 align-middle">
                                            <div class="max-w-[260px]">
                                                <p class="font-bold leading-5 text-gray-900">
                                                    {{ $book->title }}
                                                </p>
                                                <p class="mt-1 text-xs text-gray-500">
                                                    Tahun Terbit: {{ $book->publication_year ?? '-' }}
                                                </p>
                                            </div>
                                        </td>

                                        <td class="px-5 py-5 align-middle text-gray-700">
                                            {{ $book->author ?? '-' }}
                                        </td>

                                        <td class="px-5 py-5 align-middle text-gray-700">
                                            {{ $book->publisher ?? '-' }}
                                        </td>

                                        <td class="px-5 py-5 align-middle">
                                            <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">
                                                {{ $book->category->name ?? '-' }}
                                            </span>
                                        </td>

                                        <td class="px-5 py-5 text-center align-middle">
                                            <span class="inline-flex rounded-full border border-sky-200 bg-sky-50 px-3 py-1.5 text-xs font-bold text-sky-700">
                                                {{ $book->ddcClass->code ?? '-' }}
                                            </span>
                                        </td>

                                        <td class="px-5 py-5 text-center align-middle">
                                            <span class="inline-flex flex-col items-center justify-center rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-2">
                                                <span class="text-lg font-extrabold text-emerald-800">
                                                    {{ number_format($stockCount, 0, ',', '.') }}
                                                </span>
                                                <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">
                                                    Copy
                                                </span>
                                            </span>
                                        </td>

                                        <td class="px-5 py-5 text-center align-middle">
                                            @if($book->is_borrowable)
                                                <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">
                                                    <span class="material-symbols-outlined text-[14px]">check_circle</span>
                                                    Bisa Dipinjam
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700">
                                                    <span class="material-symbols-outlined text-[14px]">visibility</span>
                                                    Baca di Tempat
                                                </span>
                                            @endif
                                        </td>

                                        <td class="px-5 py-5 text-center align-middle">
                                            <a href="{{ route('books.show', $book) }}"
                                               class="inline-flex h-9 items-center justify-center gap-1.5 rounded-xl border border-emerald-200 bg-white px-3 text-xs font-bold text-emerald-700 transition hover:bg-emerald-50">
                                                <span class="material-symbols-outlined text-[15px]">visibility</span>
                                                Lihat
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-14 text-center">
                                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                                                <span class="material-symbols-outlined">menu_book</span>
                                            </div>
                                            <p class="mt-4 text-sm font-semibold text-gray-700">
                                                Belum ada buku dalam katalog.
                                            </p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if(method_exists($books, 'links'))
                        <div class="mt-6">
                            {{ $books->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>