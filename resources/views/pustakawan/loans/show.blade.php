<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
                    Peminjaman & Pengembalian
                </p>
                <h2 class="mt-1 text-xl font-bold text-gray-900">
                    Detail Struk Transaksi
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Lihat detail transaksi peminjaman buku dan cetak struk bila diperlukan.
                </p>
            </div>

            <a href="{{ route('loans.index') }}"
               class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-white px-5 py-3 text-sm font-semibold text-emerald-700 shadow-sm transition hover:bg-emerald-50">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Kembali
            </a>
        </div>
    </x-slot>

    @php
        $today = \Carbon\Carbon::today()->startOfDay();
        $dueDate = \Carbon\Carbon::parse($loan->due_date)->startOfDay();

        $isActiveLoan = in_array($loan->status, ['aktif', 'terlambat']);
        $isOverdue = $isActiveLoan && $today->gt($dueDate);

        $lateDays = $isOverdue ? (int) $dueDate->diffInDays($today) : 0;
        $finePerDay = 500;

        $activeItemCount = $loan->loanItems
            ->whereIn('status', ['dipinjam', 'terlambat'])
            ->count();

        $storedFine = (int) $loan->loanItems->sum('fine_amount');

        $runningFine = $isOverdue
            ? $lateDays * $finePerDay * $activeItemCount
            : 0;

        $totalFine = $storedFine + $runningFine;
    @endphp

    <div class="py-10 bg-gradient-to-br from-slate-50 via-emerald-50/40 to-sky-50/40 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            <div id="print-area" class="overflow-hidden rounded-[2rem] border border-white/70 bg-white shadow-[0_20px_60px_rgba(15,23,42,0.08)]">

                {{-- Header Struk --}}
                <div class="relative overflow-hidden bg-gradient-to-r from-emerald-700 to-teal-500 p-6 text-white">
                    <div class="absolute -right-16 -top-20 h-52 w-52 rounded-full bg-white/10 blur-2xl"></div>
                    <div class="absolute -left-20 bottom-0 h-48 w-48 rounded-full bg-emerald-200/20 blur-2xl"></div>

                    <div class="relative flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                        <div class="flex items-start gap-4">
                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/20 text-white shadow-sm">
                                <span class="material-symbols-outlined text-[30px]" style="font-variation-settings: 'FILL' 1;">
                                    local_library
                                </span>
                            </div>

                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-50">
                                    SIM Perpustakaan
                                </p>
                                <h3 class="mt-2 text-2xl font-bold leading-tight text-white">
                                    MTsN 1 Majene
                                </h3>
                                <p class="mt-2 text-sm text-emerald-50">
                                    Struk transaksi peminjaman dan pengembalian buku.
                                </p>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-white/20 bg-white/15 px-4 py-3 text-right">
                            <p class="text-xs text-emerald-50">Kode Transaksi</p>
                            <p class="mt-1 text-lg font-bold text-white">
                                {{ $loan->loan_code }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="p-6 md:p-8 space-y-8">

                    {{-- Ringkasan Transaksi --}}
                    <section class="grid gap-5 md:grid-cols-2">
                        <div class="rounded-3xl border border-emerald-100 bg-emerald-50/60 p-5">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-emerald-700">
                                Data Peminjam
                            </p>
                            <p class="mt-3 text-base font-bold text-gray-900">
                                {{ $loan->member->name ?? '-' }}
                            </p>
                            <p class="mt-1 text-sm text-gray-600">
                                NIS/NIP: {{ $loan->member->nis_nip ?? '-' }}
                            </p>
                            <p class="mt-1 text-sm text-gray-600">
                                Kelas/Jenis: {{ $loan->member->studentClass->class_name ?? 'Guru/Staff' }}
                            </p>
                        </div>

                        <div class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-500">
                                Status Transaksi
                            </p>

                            <div class="mt-3">
                                @if($isOverdue)
                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-bold text-red-700">
                                        <span class="material-symbols-outlined text-[14px]">warning</span>
                                        Terlambat {{ $lateDays }} Hari
                                    </span>
                                @elseif($loan->status === 'aktif')
                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700">
                                        <span class="material-symbols-outlined text-[14px]">autorenew</span>
                                        Sedang Dipinjam
                                    </span>
                                @elseif($loan->status === 'selesai')
                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">
                                        <span class="material-symbols-outlined text-[14px]">check_circle</span>
                                        Selesai
                                    </span>
                                @elseif($loan->status === 'batal')
                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs font-bold text-gray-600">
                                        <span class="material-symbols-outlined text-[14px]">cancel</span>
                                        Dibatalkan
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs font-bold text-gray-600">
                                        {{ strtoupper($loan->status ?? '-') }}
                                    </span>
                                @endif
                            </div>

                            <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-gray-400">
                                        Tanggal Pinjam
                                    </p>
                                    <p class="mt-1 font-bold text-gray-900">
                                        {{ \Carbon\Carbon::parse($loan->loan_date)->format('d M Y') }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-gray-400">
                                        Batas Kembali
                                    </p>
                                    <p class="mt-1 font-bold {{ $isOverdue ? 'text-red-600' : 'text-emerald-700' }}">
                                        {{ \Carbon\Carbon::parse($loan->due_date)->format('d M Y') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- Detail Buku --}}
                    <section class="rounded-3xl border border-gray-100 bg-white p-5 md:p-6 shadow-sm">
                        <div class="mb-5 flex items-start gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                                <span class="material-symbols-outlined text-[20px]">format_list_bulleted</span>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900">
                                    Daftar Buku
                                </h4>
                                <p class="mt-1 text-sm text-gray-500">
                                    Buku yang tercatat pada transaksi ini.
                                </p>
                            </div>
                        </div>

                        <div class="overflow-x-auto rounded-2xl border border-emerald-100">
                            <table class="min-w-full divide-y divide-emerald-100 text-left text-sm">
                                <thead class="bg-emerald-50 text-xs uppercase tracking-wider text-emerald-700">
                                    <tr>
                                        <th class="px-5 py-3 font-bold w-14">No</th>
                                        <th class="px-5 py-3 font-bold">Judul Buku</th>
                                        <th class="px-5 py-3 font-bold">Kode Eksemplar</th>
                                        <th class="px-5 py-3 font-bold">Kondisi</th>
                                        <th class="px-5 py-3 font-bold">Status</th>
                                        <th class="px-5 py-3 font-bold text-right">Denda</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-emerald-50 bg-white">
                                    @forelse($loan->loanItems as $index => $item)
                                        @php
                                            $itemRunningFine = $isOverdue && in_array($item->status, ['dipinjam', 'terlambat'])
                                                ? $lateDays * $finePerDay
                                                : 0;

                                            $itemTotalFine = (int) ($item->fine_amount ?? 0) + $itemRunningFine;
                                        @endphp

                                        <tr>
                                            <td class="px-5 py-4 text-gray-500">
                                                {{ $index + 1 }}
                                            </td>

                                            <td class="px-5 py-4">
                                                <p class="font-semibold text-gray-900">
                                                    {{ $item->bookItem->book->title ?? '-' }}
                                                </p>
                                                <p class="mt-1 text-xs text-gray-500">
                                                    Penulis: {{ $item->bookItem->book->author ?? '-' }}
                                                </p>
                                            </td>

                                            <td class="px-5 py-4">
                                                <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 font-mono text-xs font-bold text-slate-700">
                                                    <span class="material-symbols-outlined text-[14px]">qr_code_2</span>
                                                    {{ $item->bookItem->item_code ?? '-' }}
                                                </span>
                                            </td>

                                            <td class="px-5 py-4">
                                                <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold capitalize text-emerald-700">
                                                    {{ $item->return_condition ?? $item->bookItem->condition ?? '-' }}
                                                </span>
                                            </td>

                                            <td class="px-5 py-4">
                                                @if($item->status === 'dipinjam')
                                                    <span class="inline-flex rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700">
                                                        Dipinjam
                                                    </span>
                                                @elseif($item->status === 'dikembalikan')
                                                    <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">
                                                        Dikembalikan
                                                    </span>
                                                @elseif($item->status === 'terlambat')
                                                    <span class="inline-flex rounded-full border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-bold text-red-700">
                                                        Terlambat
                                                    </span>
                                                @elseif($item->status === 'rusak')
                                                    <span class="inline-flex rounded-full border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-bold text-red-700">
                                                        Rusak
                                                    </span>
                                                @elseif($item->status === 'hilang')
                                                    <span class="inline-flex rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-600">
                                                        Hilang
                                                    </span>
                                                @else
                                                    <span class="inline-flex rounded-full border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs font-bold text-gray-600">
                                                        {{ ucfirst($item->status ?? '-') }}
                                                    </span>
                                                @endif
                                            </td>

                                            <td class="px-5 py-4 text-right font-bold text-gray-900">
                                                Rp {{ number_format($itemTotalFine, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-5 py-10 text-center text-sm text-gray-500">
                                                Tidak ada buku pada transaksi ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>

                    {{-- Ringkasan Denda --}}
                    <section class="grid gap-5 md:grid-cols-2">
                        <div class="rounded-3xl border border-emerald-100 bg-emerald-50/60 p-5">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-emerald-700">
                                Ditangani Oleh
                            </p>
                            <p class="mt-2 text-sm font-bold text-gray-900">
                                {{ $loan->handler->name ?? 'Petugas Perpustakaan' }}
                            </p>
                            <p class="mt-1 text-xs text-gray-500">
                                Dicatat pada sistem perpustakaan.
                            </p>
                        </div>

                        <div class="rounded-3xl border border-rose-100 bg-rose-50/60 p-5">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-rose-700">
                                Total Denda
                            </p>
                            <p class="mt-2 text-2xl font-extrabold text-rose-600">
                                Rp {{ number_format($totalFine, 0, ',', '.') }}
                            </p>
                            <p class="mt-1 text-xs text-rose-700">
                                @if($runningFine > 0)
                                    Total denda berjalan dari seluruh buku yang masih dipinjam.
                                @else
                                    Total denda dari seluruh buku pada transaksi ini.
                                @endif
                            </p>
                        </div>
                    </section>

                    {{-- Footer Struk --}}
                    <div class="border-t border-gray-100 pt-6 text-center text-xs text-gray-400">
                        <p>Harap mengembalikan buku tepat waktu untuk menghindari denda.</p>
                        <p class="mt-1">Dicetak pada: {{ date('d M Y H:i') }}</p>
                    </div>

                </div>
            </div>

            {{-- Tombol Aksi --}}
            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end no-print">
                @if(in_array($loan->status, ['aktif', 'terlambat']))
                    <a href="{{ route('loans.edit', $loan) }}"
                       class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-700/20 transition hover:bg-emerald-800">
                        <span class="material-symbols-outlined text-[18px]">assignment_return</span>
                        Proses Pengembalian
                    </a>
                @endif

                <button onclick="window.print()"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gray-900 px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-gray-800">
                    <span class="material-symbols-outlined text-[18px]">print</span>
                    Cetak Struk
                </button>
            </div>

        </div>
    </div>

    <style>
        @media print {
            body * {
                visibility: hidden !important;
            }

            #print-area,
            #print-area * {
                visibility: visible !important;
            }

            #print-area {
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                width: 100% !important;
                border: none !important;
                box-shadow: none !important;
                border-radius: 0 !important;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</x-app-layout>