<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
                    Laporan Kepala Sekolah
                </p>

                <h2 class="mt-1 text-xl font-bold text-gray-900">
                    Laporan Buku Rusak / Hilang
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    {{ $libraryName }} — {{ $schoolName }}
                </p>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row">
                <a href="{{ route('kepala_sekolah.reports.collections') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-700 transition hover:bg-gray-50">
                    <span class="material-symbols-outlined text-[18px]">library_books</span>
                    Laporan Koleksi
                </a>

                <a href="{{ route('kepala_sekolah.reports.damaged_lost.download', request()->only(['keyword', 'status', 'condition'])) }}"
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
                  action="{{ route('kepala_sekolah.reports.damaged_lost') }}"
                  class="mb-6 rounded-[2rem] border border-white/70 bg-white/90 p-6 shadow-sm backdrop-blur-xl">
                <div class="grid gap-4 lg:grid-cols-[1.5fr_1fr_1fr_auto]">
                    <div>
                        <label for="keyword" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                            Cari Buku / Eksemplar
                        </label>

                        <input
                            id="keyword"
                            type="text"
                            name="keyword"
                            value="{{ $keyword }}"
                            placeholder="Cari kode eksemplar, judul, penulis, penerbit, atau DDC..."
                            class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                        >
                    </div>

                    <div>
                        <label for="status" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                            Status
                        </label>

                        <select
                            id="status"
                            name="status"
                            class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                        >
                            <option value="">Semua Status</option>
                            <option value="rusak" @selected($status === 'rusak')>Rusak</option>
                            <option value="hilang" @selected($status === 'hilang')>Hilang</option>
                            <option value="nonaktif" @selected($status === 'nonaktif')>Nonaktif</option>
                        </select>
                    </div>

                    <div>
                        <label for="condition" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                            Kondisi
                        </label>

                        <select
                            id="condition"
                            name="condition"
                            class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                        >
                            <option value="">Semua Kondisi</option>
                            <option value="rusak ringan" @selected($condition === 'rusak ringan')>Rusak Ringan</option>
                            <option value="rusak berat" @selected($condition === 'rusak berat')>Rusak Berat</option>
                            <option value="hilang" @selected($condition === 'hilang')>Hilang</option>
                        </select>
                    </div>

                    <div class="flex items-end gap-2">
                        <button type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-bold text-white transition hover:bg-slate-800">
                            <span class="material-symbols-outlined text-[18px]">filter_alt</span>
                            Terapkan
                        </button>

                        <a href="{{ route('kepala_sekolah.reports.damaged_lost') }}"
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
                        Ringkasan buku rusak, hilang, dan nonaktif
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                    <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Total Bermasalah</p>
                        <p class="mt-2 text-2xl font-extrabold text-slate-900">{{ number_format($totalProblemItems, 0, ',', '.') }}</p>
                    </div>

                    <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-amber-700">Rusak Ringan</p>
                        <p class="mt-2 text-2xl font-extrabold text-amber-800">{{ number_format($lightDamagedItems, 0, ',', '.') }}</p>
                    </div>

                    <div class="rounded-2xl border border-red-100 bg-red-50 p-4">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-red-700">Rusak Berat</p>
                        <p class="mt-2 text-2xl font-extrabold text-red-700">{{ number_format($heavyDamagedItems, 0, ',', '.') }}</p>
                    </div>

                    <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-600">Hilang</p>
                        <p class="mt-2 text-2xl font-extrabold text-gray-800">{{ number_format($lostItems, 0, ',', '.') }}</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-600">Nonaktif</p>
                        <p class="mt-2 text-2xl font-extrabold text-slate-800">{{ number_format($inactiveItems, 0, ',', '.') }}</p>
                    </div>
                </div>

                <div class="mt-8 overflow-x-auto rounded-3xl border border-gray-100">
                    <table class="w-full min-w-[1100px] text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-wider text-gray-500">
                            <tr>
                                <th class="px-5 py-4 font-bold">Kode Eksemplar</th>
                                <th class="px-5 py-4 font-bold">Judul Buku</th>
                                <th class="px-5 py-4 font-bold">Penulis</th>
                                <th class="px-5 py-4 text-center font-bold">DDC</th>
                                <th class="px-5 py-4 text-center font-bold">Copy</th>
                                <th class="px-5 py-4 text-center font-bold">Status</th>
                                <th class="px-5 py-4 text-center font-bold">Kondisi</th>
                                <th class="px-5 py-4 font-bold">Keterangan</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($items as $item)
                                <tr class="hover:bg-emerald-50/40">
                                    <td class="px-5 py-4">
                                        <p class="font-mono text-xs font-bold text-gray-900">
                                            {{ $item->item_code ?? '-' }}
                                        </p>
                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ $item->classification_code ?? '-' }} -
                                            {{ $item->author_code ?? '-' }} -
                                            {{ $item->title_code ?? $item->title_initial ?? '-' }}
                                        </p>
                                    </td>

                                    <td class="px-5 py-4">
                                        <p class="font-bold text-gray-900">
                                            {{ $item->book->title ?? '-' }}
                                        </p>
                                        <p class="mt-1 text-xs text-gray-500">
                                            Penerbit: {{ $item->book->publisher ?? '-' }}
                                        </p>
                                    </td>

                                    <td class="px-5 py-4 text-gray-700">
                                        {{ $item->book->author ?? '-' }}
                                    </td>

                                    <td class="px-5 py-4 text-center">
                                        <span class="inline-flex rounded-full border border-sky-200 bg-sky-50 px-3 py-1.5 text-xs font-bold text-sky-700">
                                            {{ $item->book->ddcClass->code ?? $item->classification_code ?? '-' }}
                                        </span>
                                    </td>

                                    <td class="px-5 py-4 text-center font-bold text-gray-900">
                                        {{ $item->copy_number ?? '-' }}
                                    </td>

                                    <td class="px-5 py-4 text-center">
                                        @if($item->status === 'rusak')
                                            <span class="inline-flex rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700">
                                                Rusak
                                            </span>
                                        @elseif($item->status === 'hilang')
                                            <span class="inline-flex rounded-full border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-bold text-red-700">
                                                Hilang
                                            </span>
                                        @elseif($item->status === 'nonaktif')
                                            <span class="inline-flex rounded-full border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs font-bold text-gray-700">
                                                Nonaktif
                                            </span>
                                        @else
                                            <span class="inline-flex rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-600">
                                                {{ ucfirst($item->status ?? '-') }}
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-5 py-4 text-center">
                                        @if($item->condition === 'rusak ringan')
                                            <span class="inline-flex rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700">
                                                Rusak Ringan
                                            </span>
                                        @elseif($item->condition === 'rusak berat')
                                            <span class="inline-flex rounded-full border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-bold text-red-700">
                                                Rusak Berat
                                            </span>
                                        @elseif($item->condition === 'hilang')
                                            <span class="inline-flex rounded-full border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs font-bold text-gray-700">
                                                Hilang
                                            </span>
                                        @else
                                            <span class="inline-flex rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-600">
                                                {{ ucwords($item->condition ?? '-') }}
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-5 py-4 text-gray-600">
                                        {{ $item->notes ?? $item->description ?? $item->remarks ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-5 py-12 text-center text-sm text-gray-500">
                                        Tidak ada data buku rusak / hilang yang sesuai.
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