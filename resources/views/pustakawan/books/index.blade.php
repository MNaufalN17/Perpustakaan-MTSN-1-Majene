<x-app-layout>
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
                    Kelola data utama buku seperti judul, penulis, penerbit, kategori, klasifikasi, dan jumlah copy fisik.
                </p>
            </div>

            <a href="{{ route('books.create') }}"
               class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800">
                <span class="material-symbols-outlined text-[18px]">add_circle</span>
                Tambah Buku Baru
            </a>
        </div>
    </x-slot>

    @php
        $bookCount = method_exists($books, 'total') ? $books->total() : $books->count();
    @endphp

    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-emerald-50/40 to-sky-50/40 py-10">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

            @if(session('success') || session('success_title') || session('success_message'))
                <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800 shadow-sm">
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                            <span class="material-symbols-outlined text-[20px]">check_circle</span>
                        </div>

                        <div>
                            <p class="text-sm font-bold">
                                {{ session('success_title', 'Berhasil') }}
                            </p>

                            <p class="mt-1 text-sm leading-6">
                                {{ session('success_message', session('success')) }}
                            </p>

                            @if(session('success_detail'))
                                <p class="mt-1 text-xs leading-5 text-emerald-700">
                                    {{ session('success_detail') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            @if(session('error') || session('error_title') || session('error_message'))
                <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-red-800 shadow-sm">
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-red-100 text-red-700">
                            <span class="material-symbols-outlined text-[20px]">error</span>
                        </div>

                        <div>
                            <p class="text-sm font-bold">
                                {{ session('error_title', 'Gagal') }}
                            </p>

                            <p class="mt-1 text-sm leading-6">
                                {{ session('error_message', session('error')) }}
                            </p>

                            @if(session('error_detail'))
                                <p class="mt-1 text-xs leading-5 text-red-700">
                                    {{ session('error_detail') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 shadow-sm">
                    <div class="mb-2 font-bold">Data belum bisa diproses:</div>

                    <ul class="list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/80 shadow-[0_18px_50px_rgba(15,23,42,0.06)] backdrop-blur-xl">

                <div class="relative overflow-hidden bg-gradient-to-r from-emerald-700 to-teal-500 p-6">
                    <div class="absolute -right-16 -top-20 h-52 w-52 rounded-full bg-white/10 blur-2xl"></div>
                    <div class="absolute -left-20 bottom-0 h-48 w-48 rounded-full bg-emerald-200/20 blur-2xl"></div>

                    <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-white">
                                Daftar Buku Induk
                            </h3>
                            <p class="mt-1 text-sm text-emerald-50">
                                Lihat data buku induk dan jumlah stok fisik dari masing-masing judul.
                            </p>
                        </div>

                        <div class="flex w-fit items-center gap-3 rounded-2xl border border-white/20 bg-white/15 px-4 py-3 text-white">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20">
                                <span class="material-symbols-outlined">library_books</span>
                            </div>

                            <div>
                                <p class="text-xs text-emerald-50">Total Buku Induk</p>
                                <p class="text-lg font-bold">
                                    {{ number_format($bookCount, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <div class="overflow-x-auto rounded-3xl border border-gray-100 bg-white">
                        <table class="min-w-[1240px] w-full divide-y divide-gray-100 text-left text-sm">
                            <thead class="bg-slate-50 text-xs uppercase tracking-wider text-gray-500">
                                <tr>
                                    <th class="w-[260px] px-5 py-4 font-bold">Judul</th>
                                    <th class="w-[150px] px-5 py-4 font-bold">Penulis</th>
                                    <th class="w-[150px] px-5 py-4 font-bold">Penerbit</th>
                                    <th class="w-[160px] px-5 py-4 font-bold">Kategori</th>
                                    <th class="w-[90px] px-5 py-4 text-center font-bold">DDC</th>
                                    <th class="w-[110px] px-5 py-4 text-center font-bold">Stok</th>
                                    <th class="w-[150px] px-5 py-4 text-center font-bold">Status</th>
                                    <th class="w-[230px] px-5 py-4 text-center font-bold">Aksi</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse($books as $book)
                                    <tr class="transition hover:bg-emerald-50/40">
                                        <td class="px-5 py-5 align-middle">
                                            <div class="max-w-[250px]">
                                                <p class="font-bold leading-5 text-gray-900">
                                                    {{ $book->title }}
                                                </p>

                                                <div class="mt-2 flex flex-wrap items-center gap-2">
                                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-600">
                                                        Tahun: {{ $book->publication_year ?? '-' }}
                                                    </span>

                                                    @if($book->author_code || $book->title_code)
                                                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 font-mono text-[11px] font-bold text-emerald-700">
                                                            {{ $book->author_code ?? '-' }}-{{ $book->title_code ?? '-' }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>

                                        <td class="px-5 py-5 align-middle">
                                            <div class="max-w-[140px] leading-5 text-gray-700">
                                                {{ $book->author ?? '-' }}
                                            </div>
                                        </td>

                                        <td class="px-5 py-5 align-middle">
                                            <div class="max-w-[140px] leading-5 text-gray-700">
                                                {{ $book->publisher ?? '-' }}
                                            </div>
                                        </td>

                                        <td class="px-5 py-5 align-middle">
                                            <span class="inline-flex max-w-[155px] items-center rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1.5 text-xs font-bold leading-4 text-emerald-700">
                                                {{ $book->category->name ?? '-' }}
                                            </span>
                                        </td>

                                        <td class="px-5 py-5 text-center align-middle">
                                            <span class="inline-flex items-center justify-center rounded-full border border-sky-100 bg-sky-50 px-3 py-1.5 font-mono text-xs font-bold text-sky-700">
                                                {{ $book->ddcClass->code ?? '-' }}
                                            </span>
                                        </td>

                                        <td class="px-5 py-5 text-center align-middle">
                                            <div class="inline-flex flex-col items-center justify-center rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-2">
                                                <span class="text-lg font-extrabold leading-none text-emerald-800">
                                                    {{ number_format($book->stock_count ?? 0, 0, ',', '.') }}
                                                </span>
                                                <span class="mt-1 text-[11px] font-bold uppercase tracking-wide text-emerald-700">
                                                    Copy
                                                </span>
                                            </div>
                                        </td>

                                        <td class="px-5 py-5 text-center align-middle">
                                            @if($book->is_borrowable)
                                                <span class="inline-flex items-center justify-center gap-1.5 whitespace-nowrap rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">
                                                    <span class="material-symbols-outlined text-[14px]">check_circle</span>
                                                    Bisa Dipinjam
                                                </span>
                                            @else
                                                <span class="inline-flex items-center justify-center gap-1.5 whitespace-nowrap rounded-full border border-amber-100 bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700">
                                                    <span class="material-symbols-outlined text-[14px]">visibility</span>
                                                    Baca di Tempat
                                                </span>
                                            @endif
                                        </td>

                                        <td class="px-5 py-5 align-middle">
                                            <div class="mx-auto w-[205px] rounded-2xl border border-gray-100 bg-slate-50 p-2 shadow-sm">
                                                <div class="grid grid-cols-2 gap-2">
                                                    <a href="{{ route('books.show', $book) }}"
                                                       class="inline-flex h-9 items-center justify-center gap-1.5 rounded-xl border border-emerald-200 bg-white px-3 text-xs font-bold text-emerald-700 transition hover:bg-emerald-50">
                                                        <span class="material-symbols-outlined text-[15px]">visibility</span>
                                                        Lihat
                                                    </a>

                                                    <a href="{{ route('books.edit', $book) }}"
                                                       class="inline-flex h-9 items-center justify-center gap-1.5 rounded-xl border border-teal-200 bg-white px-3 text-xs font-bold text-teal-700 transition hover:bg-teal-50">
                                                        <span class="material-symbols-outlined text-[15px]">edit</span>
                                                        Edit
                                                    </a>

                                                    <form
                                                        method="POST"
                                                        action="{{ route('books.destroy', $book) }}"
                                                        class="col-span-2"
                                                        onsubmit="return confirm('Hapus buku ini? Pastikan buku tidak memiliki data eksemplar yang masih digunakan.')"
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
                                                </div>
                                            </div>
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

                                            <p class="mt-1 text-xs text-gray-500">
                                                Klik tombol Tambah Buku Baru untuk mulai menambahkan koleksi.
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