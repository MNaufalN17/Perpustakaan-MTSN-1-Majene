<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
                    Manajemen Koleksi
                </p>
                <h2 class="mt-1 text-xl font-bold text-gray-900">
                    Detail Buku
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Lihat informasi lengkap buku yang tersimpan pada katalog induk perpustakaan.
                </p>
            </div>

            <a href="{{ route('books.index') }}"
               class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-white px-5 py-3 text-sm font-semibold text-emerald-700 shadow-sm transition hover:bg-emerald-50">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-10 bg-gradient-to-br from-slate-50 via-emerald-50/40 to-sky-50/40 min-h-screen">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            @if ($errors->any())
                <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 shadow-sm">
                    <div class="font-bold mb-2">Terjadi kesalahan:</div>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/75 backdrop-blur-xl shadow-[0_20px_60px_rgba(15,23,42,0.08)]">

                {{-- Header Card --}}
                <div class="relative overflow-hidden bg-gradient-to-r from-emerald-700 to-teal-500 p-6 text-white">
                    <div class="absolute -right-16 -top-20 h-52 w-52 rounded-full bg-white/10 blur-2xl"></div>
                    <div class="absolute -left-20 bottom-0 h-48 w-48 rounded-full bg-emerald-200/20 blur-2xl"></div>

                    <div class="relative flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                        <div class="flex items-start gap-4">
                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/20 text-white shadow-sm">
                                <span class="material-symbols-outlined text-[30px]" style="font-variation-settings: 'FILL' 1;">
                                    menu_book
                                </span>
                            </div>

                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-50">
                                    Informasi Buku
                                </p>
                                <h3 class="mt-2 max-w-2xl text-2xl font-bold leading-snug text-white">
                                    {{ $book->title }}
                                </h3>
                                <p class="mt-2 text-sm text-emerald-50">
                                    Data berikut digunakan sebagai informasi utama pada katalog buku induk perpustakaan.
                                </p>
                            </div>
                        </div>

                        <div>
                            @if($book->is_borrowable)
                                <span class="inline-flex items-center gap-2 rounded-full border border-white/25 bg-white/20 px-4 py-2 text-sm font-bold text-white">
                                    <span class="material-symbols-outlined text-[18px]">check_circle</span>
                                    Bisa Dipinjam
                                </span>
                            @else
                                <span class="inline-flex items-center gap-2 rounded-full border border-white/25 bg-white/20 px-4 py-2 text-sm font-bold text-white">
                                    <span class="material-symbols-outlined text-[18px]">visibility</span>
                                    Baca di Tempat
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="p-6 md:p-8 space-y-8">

                    {{-- Main Info --}}
                    <section class="rounded-3xl border border-emerald-100 bg-emerald-50/50 p-5 md:p-6">
                        <div class="mb-5 flex items-start gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                                <span class="material-symbols-outlined text-[20px]">info</span>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900">
                                    Informasi Utama
                                </h4>
                                <p class="mt-1 text-sm text-gray-500">
                                    Identitas dasar buku yang tercatat di sistem.
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div class="rounded-2xl border border-white/70 bg-white p-5 shadow-sm">
                                <p class="text-xs font-bold uppercase tracking-[0.14em] text-emerald-700">
                                    Judul Buku
                                </p>
                                <p class="mt-2 text-sm font-semibold leading-6 text-gray-900">
                                    {{ $book->title }}
                                </p>
                            </div>

                            <div class="rounded-2xl border border-white/70 bg-white p-5 shadow-sm">
                                <p class="text-xs font-bold uppercase tracking-[0.14em] text-emerald-700">
                                    Penulis
                                </p>
                                <p class="mt-2 text-sm font-semibold leading-6 text-gray-900">
                                    {{ $book->author ?? '-' }}
                                </p>
                            </div>

                            <div class="rounded-2xl border border-white/70 bg-white p-5 shadow-sm">
                                <p class="text-xs font-bold uppercase tracking-[0.14em] text-emerald-700">
                                    ISBN
                                </p>
                                <p class="mt-2 font-mono text-sm font-semibold leading-6 text-gray-900">
                                    {{ $book->isbn ?? '-' }}
                                </p>
                            </div>

                            <div class="rounded-2xl border border-white/70 bg-white p-5 shadow-sm">
                                <p class="text-xs font-bold uppercase tracking-[0.14em] text-emerald-700">
                                    Penerbit
                                </p>
                                <p class="mt-2 text-sm font-semibold leading-6 text-gray-900">
                                    {{ $book->publisher ?? '-' }}
                                </p>
                            </div>

                            <div class="rounded-2xl border border-white/70 bg-white p-5 shadow-sm">
                                <p class="text-xs font-bold uppercase tracking-[0.14em] text-emerald-700">
                                    Tahun Terbit
                                </p>
                                <p class="mt-2 text-sm font-semibold leading-6 text-gray-900">
                                    {{ $book->publication_year ?? '-' }}
                                </p>
                            </div>
                        </div>
                    </section>

                    {{-- Category Info --}}
                    <section class="rounded-3xl border border-gray-100 bg-white p-5 md:p-6 shadow-sm">
                        <div class="mb-5 flex items-start gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-teal-100 text-teal-700">
                                <span class="material-symbols-outlined text-[20px]">category</span>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900">
                                    Kategori dan Klasifikasi
                                </h4>
                                <p class="mt-1 text-sm text-gray-500">
                                    Informasi ini membantu pengelompokan dan pencarian buku.
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-3">
                            <div class="rounded-2xl border border-emerald-100 bg-emerald-50/60 p-5">
                                <p class="text-xs font-bold uppercase tracking-[0.14em] text-emerald-700">
                                    Kategori
                                </p>
                                <div class="mt-3">
                                    <span class="inline-flex rounded-full border border-emerald-200 bg-white px-3 py-1.5 text-xs font-bold text-emerald-700">
                                        {{ $book->category->name ?? '-' }}
                                    </span>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-sky-100 bg-sky-50/60 p-5">
                                <p class="text-xs font-bold uppercase tracking-[0.14em] text-sky-700">
                                    Kelas DDC
                                </p>
                                <div class="mt-3">
                                    <span class="inline-flex rounded-full border border-sky-200 bg-white px-3 py-1.5 text-xs font-bold text-sky-700">
                                        {{ $book->ddcClass->code ?? '-' }}
                                    </span>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-amber-100 bg-amber-50/60 p-5">
                                <p class="text-xs font-bold uppercase tracking-[0.14em] text-amber-700">
                                    Harga Buku
                                </p>
                                <p class="mt-3 text-sm font-bold text-gray-900">
                                    @if($book->price)
                                        Rp {{ number_format($book->price, 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </p>
                            </div>
                        </div>
                    </section>

                    {{-- Borrowing Rule --}}
                    <section class="rounded-3xl border border-gray-100 bg-white p-5 md:p-6 shadow-sm">
                        <div class="mb-5 flex items-start gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                                <span class="material-symbols-outlined text-[20px]">rule</span>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900">
                                    Aturan dan Catatan Buku
                                </h4>
                                <p class="mt-1 text-sm text-gray-500">
                                    Status peminjaman dan keterangan tambahan tentang buku.
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div class="rounded-2xl border border-gray-100 bg-gray-50 p-5">
                                <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-500">
                                    Status Peminjaman
                                </p>

                                <div class="mt-3">
                                    @if($book->is_borrowable)
                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">
                                            <span class="material-symbols-outlined text-[14px]">check_circle</span>
                                            Bisa Dipinjam Pulang
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700">
                                            <span class="material-symbols-outlined text-[14px]">visibility</span>
                                            Hanya Baca di Tempat
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="rounded-2xl border border-gray-100 bg-gray-50 p-5">
                                <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-500">
                                    Deskripsi / Catatan
                                </p>
                                <p class="mt-3 text-sm leading-6 text-gray-700">
                                    {{ $book->description ?? 'Belum ada deskripsi tambahan untuk buku ini.' }}
                                </p>
                            </div>
                        </div>
                    </section>

                    {{-- Action Buttons --}}
                    <div class="flex flex-col gap-3 border-t border-gray-100 pt-6 sm:flex-row sm:justify-end">
                        <a href="{{ route('books.edit', $book) }}"
                           class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-700/20 transition hover:bg-emerald-800">
                            <span class="material-symbols-outlined text-[18px]">edit</span>
                            Edit Buku
                        </a>

                        <form method="POST"
                              action="{{ route('books.destroy', $book) }}"
                              class="inline"
                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus buku ini?')">
                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-red-200 bg-white px-6 py-3 text-sm font-bold text-red-600 transition hover:bg-red-50 sm:w-auto">
                                <span class="material-symbols-outlined text-[18px]">delete</span>
                                Hapus Buku
                            </button>
                        </form>
                    </div>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>
