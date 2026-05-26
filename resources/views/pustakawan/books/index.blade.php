<x-app-layout>
    @php
        $canManage = (int) auth()->user()->role_id === 1;
    @endphp

    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
                    Manajemen Koleksi
                </p>
                <h2 class="mt-1 text-xl font-bold text-gray-900">
                    Katalog Buku Induk
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Kelola data utama buku seperti judul, penulis, penerbit, kategori, dan klasifikasi.
                </p>
            </div>

            @if($canManage)
                <a href="{{ route('books.create') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800">
                    <span class="material-symbols-outlined text-[18px]">add_circle</span>
                    Tambah Buku Baru
                </a>
            @endif
        </div>
    </x-slot>

    @php
        $bookCount = method_exists($books, 'total') ? $books->total() : $books->count();
    @endphp

    <div class="py-10 bg-gradient-to-br from-slate-50 via-emerald-50/40 to-sky-50/40 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800 flex items-center gap-2 shadow-sm">
                    <span class="material-symbols-outlined text-[20px]">check_circle</span>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 shadow-sm">
                    <div class="font-bold mb-2">Terjadi kesalahan:</div>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="overflow-hidden rounded-3xl bg-white/75 backdrop-blur-xl shadow-sm border border-white/70">

                <div class="relative overflow-hidden bg-gradient-to-r from-emerald-700 to-teal-500 p-6">
                    <div class="absolute -right-16 -top-20 h-52 w-52 rounded-full bg-white/10 blur-2xl"></div>
                    <div class="absolute -left-20 bottom-0 h-48 w-48 rounded-full bg-emerald-200/20 blur-2xl"></div>

                    <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-white text-lg font-semibold">
                                Daftar Buku Induk
                            </h3>
                            <p class="text-emerald-50 mt-1 text-sm">
                                Lihat dan kelola seluruh data buku utama yang tercatat di perpustakaan.
                            </p>
                        </div>

                        <div class="flex items-center gap-3 rounded-2xl border border-white/20 bg-white/15 px-4 py-3 text-white">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20">
                                <span class="material-symbols-outlined">library_books</span>
                            </div>
                            <div>
                                <p class="text-xs text-emerald-50">Total Buku</p>
                                <p class="text-lg font-bold">{{ number_format($bookCount, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <div class="overflow-x-auto rounded-3xl border border-emerald-100 bg-emerald-50/70 p-4">
                        <table class="min-w-[1100px] w-full divide-y divide-emerald-100 text-left text-sm">
                            <thead class="bg-white text-xs uppercase tracking-wider text-emerald-700">
                                <tr>
                                    <th class="px-5 py-3 font-bold w-[260px]">Judul</th>
                                    <th class="px-5 py-3 font-bold w-[150px]">Penulis</th>
                                    <th class="px-5 py-3 font-bold w-[150px]">Penerbit</th>
                                    <th class="px-5 py-3 font-bold w-[170px]">Kategori</th>
                                    <th class="px-5 py-3 font-bold w-[90px]">DDC</th>
                                    <th class="px-5 py-3 font-bold w-[150px]">Status</th>
                                    <th class="px-5 py-3 font-bold w-[220px]">Aksi</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-emerald-50 bg-white">
                                @forelse($books as $book)
                                    <tr class="transition-colors hover:bg-emerald-50/70">
                                        <td class="px-5 py-5 align-middle">
                                            <div class="max-w-[240px]">
                                                <p class="font-semibold leading-5 text-gray-900">
                                                    {{ $book->title }}
                                                </p>
                                                <p class="mt-1 text-xs text-gray-500">
                                                    Tahun Terbit: {{ $book->publication_year ?? '-' }}
                                                </p>
                                            </div>
                                        </td>

                                        <td class="px-5 py-5 align-middle text-gray-700">
                                            <div class="max-w-[140px] leading-5">
                                                {{ $book->author ?? '-' }}
                                            </div>
                                        </td>

                                        <td class="px-5 py-5 align-middle text-gray-700">
                                            <div class="max-w-[140px] leading-5">
                                                {{ $book->publisher ?? '-' }}
                                            </div>
                                        </td>

                                        <td class="px-5 py-5 align-middle">
                                            <span class="inline-flex max-w-[160px] items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold leading-4 text-emerald-700">
                                                {{ $book->category->name ?? '-' }}
                                            </span>
                                        </td>

                                        <td class="px-5 py-5 align-middle">
                                            <span class="inline-flex items-center rounded-full border border-sky-200 bg-sky-50 px-3 py-1.5 text-xs font-bold text-sky-700">
                                                {{ $book->ddcClass->code ?? '-' }}
                                            </span>
                                        </td>

                                        <td class="px-5 py-5 align-middle">
                                            @if($book->is_borrowable)
                                                <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">
                                                    <span class="material-symbols-outlined text-[14px]">check_circle</span>
                                                    Bisa Dipinjam
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700">
                                                    <span class="material-symbols-outlined text-[14px]">visibility</span>
                                                    Baca di Tempat
                                                </span>
                                            @endif
                                        </td>

                                        <td class="px-5 py-5 align-middle">
                                            <div class="mx-auto {{ $canManage ? 'w-[210px]' : 'w-[110px]' }} rounded-2xl border border-gray-100 bg-slate-50 p-2 shadow-sm">
                                                <div class="grid {{ $canManage ? 'grid-cols-2' : 'grid-cols-1' }} gap-2">
                                                    <a href="{{ route('books.show', $book) }}"
                                                       class="inline-flex h-9 items-center justify-center gap-1.5 rounded-xl border border-emerald-200 bg-white px-3 text-xs font-bold text-emerald-700 transition hover:bg-emerald-50">
                                                        <span class="material-symbols-outlined text-[15px]">visibility</span>
                                                        Lihat
                                                    </a>

                                                    @if($canManage)
                                                        <a href="{{ route('books.edit', $book) }}"
                                                           class="inline-flex h-9 items-center justify-center gap-1.5 rounded-xl border border-teal-200 bg-white px-3 text-xs font-bold text-teal-700 transition hover:bg-teal-50">
                                                            <span class="material-symbols-outlined text-[15px]">edit</span>
                                                            Edit
                                                        </a>

                                                        <form
                                                            method="POST"
                                                            action="{{ route('books.destroy', $book) }}"
                                                            class="col-span-2"
                                                            onsubmit="return confirm('Hapus buku induk ini? Semua data yang berkaitan perlu diperiksa kembali sebelum menghapus.')"
                                                        >
                                                            @csrf
                                                            @method('DELETE')

                                                            <button
                                                                type="submit"
                                                                class="inline-flex h-9 w-full items-center justify-center gap-1.5 rounded-xl border border-red-200 bg-red-50 px-3 text-xs font-bold text-red-700 transition hover:bg-red-100"
                                                            >
                                                                <span class="material-symbols-outlined text-[15px]">delete</span>
                                                                Hapus Buku
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-14 text-center">
                                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                                                <span class="material-symbols-outlined">menu_book</span>
                                            </div>
                                            <p class="mt-4 text-sm font-semibold text-gray-700">
                                                Belum ada buku dalam katalog.
                                            </p>
                                            <p class="mt-1 text-xs text-gray-500">
                                                @if($canManage)
                                                    Klik tombol Tambah Buku Baru untuk mulai menambahkan koleksi.
                                                @else
                                                    Belum ada data buku yang dapat ditampilkan.
                                                @endif
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