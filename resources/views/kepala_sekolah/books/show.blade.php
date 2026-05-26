<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
                    Data Koleksi
                </p>
                <h2 class="mt-1 text-xl font-bold text-gray-900">
                    Detail Buku Induk
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Halaman ini bersifat read-only untuk Kepala Sekolah/Kepala Perpustakaan.
                </p>
            </div>

            <a href="{{ route('books.index') }}"
               class="inline-flex items-center justify-center gap-2 rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-600 transition hover:bg-gray-50">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Kembali
            </a>
        </div>
    </x-slot>

    @php
        $stockCount = $book->book_items_count ?? $book->bookItems->count();
        $availableCount = $book->bookItems->where('status', 'tersedia')->count();
        $borrowedCount = $book->bookItems->where('status', 'dipinjam')->count();
        $problematicCount = $book->bookItems
            ->whereIn('status', ['rusak', 'hilang', 'nonaktif'])
            ->count();
    @endphp

    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-emerald-50/40 to-sky-50/40 py-10">
        <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">

            <div class="overflow-hidden rounded-[2rem] border border-white/70 bg-white shadow-[0_20px_60px_rgba(15,23,42,0.08)]">
                <div class="relative overflow-hidden bg-gradient-to-r from-emerald-700 to-teal-500 p-6 text-white">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/20 text-white">
                            <span class="material-symbols-outlined text-[26px]">menu_book</span>
                        </div>

                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-50">
                                Informasi Buku
                            </p>

                            <h3 class="mt-2 text-2xl font-extrabold leading-tight text-white">
                                {{ $book->title }}
                            </h3>

                            <p class="mt-1 text-sm text-emerald-50">
                                Penulis: {{ $book->author ?? '-' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="space-y-6 p-6">

                    <section class="grid gap-4 md:grid-cols-4">
                        <div class="rounded-3xl border border-emerald-100 bg-emerald-50 p-5">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-emerald-700">
                                Total Stok
                            </p>
                            <p class="mt-2 text-2xl font-extrabold text-emerald-800">
                                {{ number_format($stockCount, 0, ',', '.') }}
                            </p>
                        </div>

                        <div class="rounded-3xl border border-sky-100 bg-sky-50 p-5">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-sky-700">
                                Tersedia
                            </p>
                            <p class="mt-2 text-2xl font-extrabold text-sky-800">
                                {{ number_format($availableCount, 0, ',', '.') }}
                            </p>
                        </div>

                        <div class="rounded-3xl border border-amber-100 bg-amber-50 p-5">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-amber-700">
                                Dipinjam
                            </p>
                            <p class="mt-2 text-2xl font-extrabold text-amber-800">
                                {{ number_format($borrowedCount, 0, ',', '.') }}
                            </p>
                        </div>

                        <div class="rounded-3xl border border-red-100 bg-red-50 p-5">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-red-700">
                                Bermasalah
                            </p>
                            <p class="mt-2 text-2xl font-extrabold text-red-700">
                                {{ number_format($problematicCount, 0, ',', '.') }}
                            </p>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm">
                        <h4 class="font-bold text-gray-900">
                            Detail Bibliografi
                        </h4>

                        <div class="mt-5 grid gap-4 md:grid-cols-2">
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-400">Judul</p>
                                <p class="mt-2 font-bold text-gray-900">{{ $book->title ?? '-' }}</p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-400">Penulis</p>
                                <p class="mt-2 font-bold text-gray-900">{{ $book->author ?? '-' }}</p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-400">Penerbit</p>
                                <p class="mt-2 font-bold text-gray-900">{{ $book->publisher ?? '-' }}</p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-400">Tahun Terbit</p>
                                <p class="mt-2 font-bold text-gray-900">{{ $book->publication_year ?? '-' }}</p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-400">Kategori</p>
                                <p class="mt-2 font-bold text-gray-900">{{ $book->category->name ?? '-' }}</p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-400">DDC</p>
                                <p class="mt-2 font-bold text-gray-900">{{ $book->ddcClass->code ?? '-' }}</p>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm">
                        <h4 class="font-bold text-gray-900">
                            Eksemplar Buku
                        </h4>

                        <div class="mt-5 overflow-x-auto rounded-2xl border border-gray-100">
                            <table class="w-full min-w-[800px] text-left text-sm">
                                <thead class="bg-slate-50 text-xs uppercase tracking-wider text-gray-500">
                                    <tr>
                                        <th class="px-4 py-3">Kode Eksemplar</th>
                                        <th class="px-4 py-3 text-center">Copy</th>
                                        <th class="px-4 py-3 text-center">Status</th>
                                        <th class="px-4 py-3 text-center">Kondisi</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-100">
                                    @forelse($book->bookItems as $item)
                                        <tr>
                                            <td class="px-4 py-3 font-mono text-xs font-bold text-gray-900">
                                                {{ $item->item_code ?? '-' }}
                                            </td>

                                            <td class="px-4 py-3 text-center">
                                                {{ $item->copy_number ?? '-' }}
                                            </td>

                                            <td class="px-4 py-3 text-center">
                                                {{ ucfirst($item->status ?? '-') }}
                                            </td>

                                            <td class="px-4 py-3 text-center">
                                                {{ ucwords($item->condition ?? '-') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-4 py-10 text-center text-gray-500">
                                                Belum ada eksemplar untuk buku ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <div class="border-t border-gray-100 pt-5 text-right">
                        <a href="{{ route('books.index') }}"
                           class="inline-flex items-center justify-center gap-2 rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-600 transition hover:bg-gray-50">
                            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                            Kembali
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>