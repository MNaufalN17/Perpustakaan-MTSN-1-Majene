<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
                    Laporan Kepala Sekolah
                </p>

                <h2 class="mt-1 text-xl font-bold text-gray-900">
                    Laporan Koleksi Buku
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    {{ $libraryName }} — {{ $schoolName }}
                </p>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row">
                <a href="{{ route('kepala_sekolah.reports.index') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-700 transition hover:bg-gray-50">
                    <span class="material-symbols-outlined text-[18px]">receipt_long</span>
                    Laporan Peminjaman
                </a>

                <a href="{{ route('kepala_sekolah.reports.collections.download', request()->only(['keyword'])) }}"
                   class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-700/20 transition hover:bg-emerald-800">
                    <span class="material-symbols-outlined text-[18px]">download</span>
                    Download PDF
                </a>
            </div>
        </div>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-emerald-50/40 to-sky-50/40 py-10">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

            <form method="GET"
                  action="{{ route('kepala_sekolah.reports.collections') }}"
                  class="mb-6 rounded-[2rem] border border-white/70 bg-white/90 p-6 shadow-sm backdrop-blur-xl">
                <div class="grid gap-4 md:grid-cols-[1fr_auto]">
                    <div>
                        <label for="keyword" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                            Cari Koleksi
                        </label>

                        <input
                            id="keyword"
                            type="text"
                            name="keyword"
                            value="{{ $keyword }}"
                            placeholder="Cari judul, penulis, penerbit, kategori, atau DDC..."
                            class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                        >
                    </div>

                    <div class="flex items-end gap-2">
                        <button type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-bold text-white transition hover:bg-slate-800">
                            <span class="material-symbols-outlined text-[18px]">filter_alt</span>
                            Terapkan
                        </button>

                        <a href="{{ route('kepala_sekolah.reports.collections') }}"
                           class="inline-flex items-center justify-center gap-2 rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-600 transition hover:bg-gray-50">
                            Reset
                        </a>
                    </div>
                </div>
            </form>

            <div class="rounded-[2rem] border border-white/70 bg-white/95 p-6 shadow-sm backdrop-blur-xl">
                <div class="mb-6 border-b border-gray-100 pb-5 text-center">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
                        {{ $libraryName }}
                    </p>

                    <h3 class="mt-2 text-2xl font-extrabold text-gray-900">
                        {{ $schoolName }}
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Ringkasan kondisi koleksi buku perpustakaan
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-3 xl:grid-cols-6">
                    <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Buku Induk</p>
                        <p class="mt-2 text-2xl font-extrabold text-slate-900">{{ number_format($totalBooks, 0, ',', '.') }}</p>
                    </div>

                    <div class="rounded-2xl border border-indigo-100 bg-indigo-50 p-4">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-indigo-700">Eksemplar</p>
                        <p class="mt-2 text-2xl font-extrabold text-indigo-800">{{ number_format($totalBookItems, 0, ',', '.') }}</p>
                    </div>

                    <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-emerald-700">Tersedia</p>
                        <p class="mt-2 text-2xl font-extrabold text-emerald-800">{{ number_format($availableBooks, 0, ',', '.') }}</p>
                    </div>

                    <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-amber-700">Dipinjam</p>
                        <p class="mt-2 text-2xl font-extrabold text-amber-800">{{ number_format($borrowedBooks, 0, ',', '.') }}</p>
                    </div>

                    <div class="rounded-2xl border border-red-100 bg-red-50 p-4">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-red-700">Rusak</p>
                        <p class="mt-2 text-2xl font-extrabold text-red-700">{{ number_format($damagedBooks, 0, ',', '.') }}</p>
                    </div>

                    <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-600">Hilang</p>
                        <p class="mt-2 text-2xl font-extrabold text-gray-800">{{ number_format($lostBooks, 0, ',', '.') }}</p>
                    </div>
                </div>

                <div class="mt-8 overflow-x-auto rounded-3xl border border-gray-100">
                    <table class="w-full min-w-[1100px] text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-wider text-gray-500">
                            <tr>
                                <th class="px-5 py-4 font-bold">Judul Buku</th>
                                <th class="px-5 py-4 font-bold">Penulis</th>
                                <th class="px-5 py-4 font-bold">Kategori</th>
                                <th class="px-5 py-4 text-center font-bold">DDC</th>
                                <th class="px-5 py-4 text-center font-bold">Total</th>
                                <th class="px-5 py-4 text-center font-bold">Tersedia</th>
                                <th class="px-5 py-4 text-center font-bold">Dipinjam</th>
                                <th class="px-5 py-4 text-center font-bold">Rusak</th>
                                <th class="px-5 py-4 text-center font-bold">Hilang</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($books as $book)
                                <tr class="hover:bg-emerald-50/40">
                                    <td class="px-5 py-4">
                                        <p class="font-bold text-gray-900">{{ $book->title }}</p>
                                        <p class="mt-1 text-xs text-gray-500">
                                            Penerbit: {{ $book->publisher ?? '-' }}
                                        </p>
                                    </td>

                                    <td class="px-5 py-4 text-gray-700">
                                        {{ $book->author ?? '-' }}
                                    </td>

                                    <td class="px-5 py-4">
                                        <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">
                                            {{ $book->category->name ?? '-' }}
                                        </span>
                                    </td>

                                    <td class="px-5 py-4 text-center">
                                        <span class="inline-flex rounded-full border border-sky-200 bg-sky-50 px-3 py-1.5 text-xs font-bold text-sky-700">
                                            {{ $book->ddcClass->code ?? '-' }}
                                        </span>
                                    </td>

                                    <td class="px-5 py-4 text-center font-extrabold text-gray-900">
                                        {{ number_format($book->total_items_count, 0, ',', '.') }}
                                    </td>

                                    <td class="px-5 py-4 text-center font-bold text-emerald-700">
                                        {{ number_format($book->available_items_count, 0, ',', '.') }}
                                    </td>

                                    <td class="px-5 py-4 text-center font-bold text-amber-700">
                                        {{ number_format($book->borrowed_items_count, 0, ',', '.') }}
                                    </td>

                                    <td class="px-5 py-4 text-center font-bold text-red-700">
                                        {{ number_format($book->damaged_items_count, 0, ',', '.') }}
                                    </td>

                                    <td class="px-5 py-4 text-center font-bold text-gray-700">
                                        {{ number_format($book->lost_items_count, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-5 py-12 text-center text-sm text-gray-500">
                                        Tidak ada data koleksi yang sesuai.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 flex justify-end">
                    <a href="{{ route('kepala_sekolah.dashboard') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-600 transition hover:bg-gray-50">
                        <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                        Kembali ke Dashboard
                    </a>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>