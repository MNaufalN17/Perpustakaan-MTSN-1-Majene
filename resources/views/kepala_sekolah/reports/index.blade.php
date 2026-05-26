<x-app-layout>
    @php
        $schoolName = \App\Models\SystemSetting::getValue('school_name', 'MTsN 1 Majene');
        $libraryName = \App\Models\SystemSetting::getValue('library_name', 'Sistem Informasi Perpustakaan');
    @endphp

    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
                    Laporan Kepala Sekolah
                </p>

                <h2 class="mt-1 text-xl font-bold text-gray-900">
                    Laporan Aktivitas Perpustakaan
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    {{ $libraryName }} — {{ $schoolName }}
                </p>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row">
                <a href="{{ route('kepala_sekolah.reports.download', request()->only(['start_date', 'end_date'])) }}"
                   class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-700/20 transition hover:bg-emerald-800">
                    <span class="material-symbols-outlined text-[18px]">download</span>
                    Download PDF
                </a>

                <button
                    type="button"
                    onclick="window.print()"
                    class="inline-flex items-center justify-center gap-2 rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-700 transition hover:bg-gray-50"
                >
                    <span class="material-symbols-outlined text-[18px]">print</span>
                    Cetak
                </button>
            </div>
        </div>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-emerald-50/40 to-sky-50/40 py-10 print:bg-white print:py-0">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 print:max-w-none print:px-0">

            <form
                method="GET"
                action="{{ route('kepala_sekolah.reports.index') }}"
                class="mb-6 rounded-[2rem] border border-white/70 bg-white/90 p-6 shadow-sm backdrop-blur-xl print:hidden"
            >
                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <label for="start_date" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                            Tanggal Awal
                        </label>

                        <input
                            id="start_date"
                            type="date"
                            name="start_date"
                            value="{{ $startDate }}"
                            class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                        >
                    </div>

                    <div>
                        <label for="end_date" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                            Tanggal Akhir
                        </label>

                        <input
                            id="end_date"
                            type="date"
                            name="end_date"
                            value="{{ $endDate }}"
                            class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                        >
                    </div>

                    <div class="flex items-end">
                        <button
                            type="submit"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-bold text-white transition hover:bg-slate-800"
                        >
                            <span class="material-symbols-outlined text-[18px]">filter_alt</span>
                            Terapkan Filter
                        </button>
                    </div>
                </div>
            </form>

            <div id="print-area" class="rounded-[2rem] border border-white/70 bg-white/95 p-6 shadow-sm backdrop-blur-xl print:rounded-none print:border-0 print:bg-white print:p-0 print:shadow-none">

                <div class="mb-6 border-b border-gray-200 pb-5 text-center print:mb-4 print:pb-3">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700 print:text-[10px]">
                        {{ $libraryName }}
                    </p>

                    <h3 class="mt-2 text-2xl font-extrabold text-gray-900 print:mt-1 print:text-lg">
                        {{ $schoolName }}
                    </h3>

                    <p class="mt-1 text-sm text-gray-500 print:text-[11px]">
                        Periode {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}
                        sampai {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-4 print:grid-cols-4 print:gap-2">
                    <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4 print:rounded-lg print:border-gray-300 print:bg-white print:p-2">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500 print:text-[8px]">
                            Total Transaksi
                        </p>
                        <p class="mt-2 text-2xl font-extrabold text-slate-900 print:mt-1 print:text-base">
                            {{ number_format($totalLoans ?? 0, 0, ',', '.') }}
                        </p>
                    </div>

                    <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4 print:rounded-lg print:border-gray-300 print:bg-white print:p-2">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-emerald-700 print:text-[8px] print:text-gray-700">
                            Selesai
                        </p>
                        <p class="mt-2 text-2xl font-extrabold text-emerald-800 print:mt-1 print:text-base print:text-gray-900">
                            {{ number_format($completedLoans ?? 0, 0, ',', '.') }}
                        </p>
                    </div>

                    <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4 print:rounded-lg print:border-gray-300 print:bg-white print:p-2">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-amber-700 print:text-[8px] print:text-gray-700">
                            Aktif
                        </p>
                        <p class="mt-2 text-2xl font-extrabold text-amber-800 print:mt-1 print:text-base print:text-gray-900">
                            {{ number_format($activeLoans ?? 0, 0, ',', '.') }}
                        </p>
                    </div>

                    <div class="rounded-2xl border border-red-100 bg-red-50 p-4 print:rounded-lg print:border-gray-300 print:bg-white print:p-2">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-red-700 print:text-[8px] print:text-gray-700">
                            Terlambat
                        </p>
                        <p class="mt-2 text-2xl font-extrabold text-red-700 print:mt-1 print:text-base print:text-gray-900">
                            {{ number_format($overdueLoans ?? 0, 0, ',', '.') }}
                        </p>
                    </div>
                </div>

                <div class="mt-6 rounded-3xl border border-gray-100 bg-white p-5 print:mt-4 print:rounded-none print:border-gray-400 print:p-0">
                    <div class="mb-4 flex flex-col gap-1 print:hidden">
                        <h4 class="text-base font-bold text-gray-900">
                            Daftar Peminjam dan Status Peminjaman
                        </h4>
                        <p class="text-sm text-gray-500">
                            Status peminjaman ditandai agar mudah dibaca oleh kepala sekolah.
                        </p>
                    </div>

                    <div class="overflow-x-auto print:overflow-visible">
                        <table class="w-full min-w-[980px] text-left text-sm print:min-w-0 print:text-[9px]">
                            <thead class="bg-slate-50 text-xs uppercase tracking-wider text-gray-500 print:bg-gray-100 print:text-[8px] print:text-gray-800">
                                <tr>
                                    <th class="px-5 py-4 font-bold print:px-1.5 print:py-1.5 print:w-[16%]">Kode</th>
                                    <th class="px-5 py-4 font-bold print:px-1.5 print:py-1.5 print:w-[28%]">Peminjam</th>
                                    <th class="px-5 py-4 font-bold print:px-1.5 print:py-1.5 print:w-[14%]">Tanggal Pinjam</th>
                                    <th class="px-5 py-4 font-bold print:px-1.5 print:py-1.5 print:w-[14%]">Batas Kembali</th>
                                    <th class="px-5 py-4 text-center font-bold print:px-1.5 print:py-1.5 print:w-[10%]">Buku</th>
                                    <th class="px-5 py-4 text-center font-bold print:px-1.5 print:py-1.5 print:w-[14%]">Status</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100 bg-white print:divide-gray-300">
                                @forelse($loans ?? [] as $loan)
                                    @php
                                        $isLate = in_array($loan->status, ['aktif', 'terlambat'], true)
                                            && $loan->due_date
                                            && \Carbon\Carbon::parse($loan->due_date)->startOfDay()->lt(today());

                                        $statusLabel = $isLate
                                            ? 'Terlambat'
                                            : ($loan->status === 'aktif'
                                                ? 'Dipinjam'
                                                : ($loan->status === 'selesai'
                                                    ? 'Selesai'
                                                    : ucfirst($loan->status ?? '-')));

                                        $rowClass = $isLate
                                            ? 'bg-red-50/70'
                                            : ($loan->status === 'aktif'
                                                ? 'bg-amber-50/60'
                                                : ($loan->status === 'selesai'
                                                    ? 'bg-emerald-50/60'
                                                    : 'bg-white'));
                                    @endphp

                                    <tr class="{{ $rowClass }} print:bg-white print:break-inside-avoid">
                                        <td class="px-5 py-4 print:border print:border-gray-300 print:px-1.5 print:py-1">
                                            <p class="font-bold text-gray-900 print:text-[9px]">
                                                {{ $loan->loan_code }}
                                            </p>
                                        </td>

                                        <td class="px-5 py-4 print:border print:border-gray-300 print:px-1.5 print:py-1">
                                            <p class="font-bold text-gray-900 print:text-[9px]">
                                                {{ $loan->member->name ?? '-' }}
                                            </p>

                                            <p class="mt-1 text-xs text-gray-500 print:mt-0 print:text-[8px] print:text-gray-700">
                                                {{ $loan->member->nis_nip ?? '-' }}

                                                @if($loan->member?->studentClass)
                                                    — {{ $loan->member->studentClass->class_name }}
                                                @endif
                                            </p>
                                        </td>

                                        <td class="px-5 py-4 text-gray-700 print:border print:border-gray-300 print:px-1.5 print:py-1">
                                            {{ $loan->loan_date ? \Carbon\Carbon::parse($loan->loan_date)->format('d M Y') : '-' }}
                                        </td>

                                        <td class="px-5 py-4 text-gray-700 print:border print:border-gray-300 print:px-1.5 print:py-1">
                                            {{ $loan->due_date ? \Carbon\Carbon::parse($loan->due_date)->format('d M Y') : '-' }}
                                        </td>

                                        <td class="px-5 py-4 text-center font-bold text-gray-900 print:border print:border-gray-300 print:px-1.5 print:py-1">
                                            {{ $loan->loanItems->count() }}
                                        </td>

                                        <td class="px-5 py-4 text-center print:border print:border-gray-300 print:px-1.5 print:py-1">
                                            @if($isLate)
                                                <span class="inline-flex rounded-full border border-red-200 bg-red-100 px-3 py-1.5 text-xs font-bold text-red-700 print:border-0 print:bg-transparent print:px-0 print:py-0 print:text-[9px] print:text-gray-900">
                                                    {{ $statusLabel }}
                                                </span>
                                            @elseif($loan->status === 'aktif')
                                                <span class="inline-flex rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-xs font-bold text-amber-700 print:border-0 print:bg-transparent print:px-0 print:py-0 print:text-[9px] print:text-gray-900">
                                                    {{ $statusLabel }}
                                                </span>
                                            @elseif($loan->status === 'selesai')
                                                <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-xs font-bold text-emerald-700 print:border-0 print:bg-transparent print:px-0 print:py-0 print:text-[9px] print:text-gray-900">
                                                    {{ $statusLabel }}
                                                </span>
                                            @else
                                                <span class="inline-flex rounded-full border border-slate-200 bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600 print:border-0 print:bg-transparent print:px-0 print:py-0 print:text-[9px] print:text-gray-900">
                                                    {{ $statusLabel }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-5 py-12 text-center text-sm text-gray-500 print:border print:border-gray-300 print:px-2 print:py-4 print:text-[10px]">
                                            Tidak ada transaksi pada periode ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-8 grid gap-8 md:grid-cols-2 print:mt-5 print:grid-cols-2 print:gap-6">
                    <div class="text-sm text-gray-500 print:text-[10px] print:text-gray-800">
                        <p>Dicetak pada: {{ now()->format('d M Y H:i') }}</p>
                        <p>Nominal denda per hari: Rp {{ number_format($finePerDay ?? 0, 0, ',', '.') }}</p>
                    </div>

                    <div class="text-center text-sm text-gray-700 print:text-[10px] print:text-gray-900">
                        <p>Kepala Sekolah / Kepala Perpustakaan</p>
                        <div class="h-20 print:h-12"></div>
                        <p class="font-bold">____________________________</p>
                    </div>
                </div>

            </div>

            <div class="mt-6 flex justify-end print:hidden">
                <a href="{{ route('kepala_sekolah.dashboard') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-600 transition hover:bg-gray-50">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                    Kembali ke Dashboard
                </a>
            </div>

        </div>
    </div>

    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        @media print {
            html,
            body {
                width: 297mm;
                min-height: 210mm;
                background: #ffffff !important;
                color: #111827 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            nav,
            header,
            .print\:hidden {
                display: none !important;
            }

            * {
                box-shadow: none !important;
            }

            #print-area {
                width: 100% !important;
                max-width: 100% !important;
            }

            table {
                table-layout: fixed !important;
                border-collapse: collapse !important;
            }

            thead {
                display: table-header-group !important;
            }

            tr {
                break-inside: avoid !important;
                page-break-inside: avoid !important;
            }

            a[href]::after {
                content: none !important;
            }
        }
    </style>
</x-app-layout>