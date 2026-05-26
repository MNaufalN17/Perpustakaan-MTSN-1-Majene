<x-app-layout>
    @php
        $schoolName = \App\Models\SystemSetting::getValue('school_name', 'MTsN 1 Majene');
        $libraryName = \App\Models\SystemSetting::getValue('library_name', 'Sistem Informasi Perpustakaan');
    @endphp

    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
                    Dashboard Kepala Sekolah
                </p>

                <h2 class="mt-1 font-semibold text-xl text-gray-800 leading-tight">
                    {{ $libraryName }}
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    {{ $schoolName }} — Ringkasan statistik perpustakaan.
                </p>
            </div>

            <a href="{{ route('kepala_sekolah.reports.index') }}"
               class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-700/20 transition hover:bg-emerald-800">
                <span class="material-symbols-outlined text-[18px]">bar_chart</span>
                Lihat Laporan
            </a>
        </div>
    </x-slot>

    <div class="py-12 bg-gradient-to-br from-slate-50 via-emerald-50/40 to-sky-50/40 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-bold">
                        Selamat datang, {{ Auth::user()->name }}!
                    </h3>

                    <p class="mt-1 text-gray-600">
                        Berikut adalah ringkasan statistik {{ $libraryName }} saat ini.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-blue-500">
                    <div class="text-sm font-medium text-gray-500 uppercase tracking-wider">
                        Total Anggota Aktif
                    </div>

                    <div class="mt-2 text-3xl font-bold text-gray-900">
                        {{ number_format($totalMembers ?? 0, 0, ',', '.') }}
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-green-500">
                    <div class="text-sm font-medium text-gray-500 uppercase tracking-wider">
                        Total Eksemplar Buku
                    </div>

                    <div class="mt-2 text-3xl font-bold text-gray-900">
                        {{ number_format($totalBookItems ?? 0, 0, ',', '.') }}
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-yellow-500">
                    <div class="text-sm font-medium text-gray-500 uppercase tracking-wider">
                        Buku Sedang Dipinjam
                    </div>

                    <div class="mt-2 text-3xl font-bold text-gray-900">
                        {{ number_format($borrowedBooks ?? 0, 0, ',', '.') }}
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-indigo-500">
                    <div class="text-sm font-medium text-gray-500 uppercase tracking-wider">
                        Transaksi Aktif
                    </div>

                    <div class="mt-2 text-3xl font-bold text-gray-900">
                        {{ number_format($activeLoans ?? 0, 0, ',', '.') }}
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-red-500">
                    <div class="text-sm font-medium text-gray-500 uppercase tracking-wider">
                        Buku Rusak / Hilang
                    </div>

                    <div class="mt-2 text-3xl font-bold text-gray-900">
                        {{ number_format($problematicBooks ?? 0, 0, ',', '.') }}
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-orange-500">
                    <div class="text-sm font-medium text-gray-500 uppercase tracking-wider">
                        Terlambat Dikembalikan
                    </div>

                    <div class="mt-2 text-3xl font-bold text-gray-900">
                        {{ number_format($overdueItems ?? 0, 0, ',', '.') }}
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>