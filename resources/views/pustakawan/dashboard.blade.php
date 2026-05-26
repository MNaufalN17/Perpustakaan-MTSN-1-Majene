<x-app-layout>
    @php
        $schoolName = \App\Models\SystemSetting::getValue('school_name', 'MTsN 1 Majene');
        $libraryName = \App\Models\SystemSetting::getValue('library_name', 'Sistem Manajemen Perpustakaan');
    @endphp

    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
                Dashboard Perpustakaan
            </p>

            <h2 class="mt-1 text-2xl font-extrabold text-slate-900">
                {{ $libraryName }}
            </h2>

            <p class="text-sm text-slate-500 max-w-3xl">
                {{ $schoolName }} — Ringkasan aktivitas perpustakaan, data koleksi, dan transaksi peminjaman.
            </p>
        </div>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-emerald-50/40 to-sky-50/40 py-10">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-[2rem] border border-white/70 bg-white/80 p-6 shadow-[0_18px_50px_rgba(15,23,42,0.06)] backdrop-blur-xl">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                            Total Eksemplar
                        </p>

                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                            <span class="material-symbols-outlined">library_books</span>
                        </div>
                    </div>

                    <p class="mt-4 text-3xl font-extrabold text-slate-900">
                        {{ number_format($totalBooks ?? 0, 0, ',', '.') }}
                    </p>

                    <p class="mt-2 text-sm text-slate-500">
                        Seluruh copy buku yang tercatat.
                    </p>
                </div>

                <div class="rounded-[2rem] border border-white/70 bg-white/80 p-6 shadow-[0_18px_50px_rgba(15,23,42,0.06)] backdrop-blur-xl">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                            Anggota Aktif
                        </p>

                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-100 text-sky-700">
                            <span class="material-symbols-outlined">groups</span>
                        </div>
                    </div>

                    <p class="mt-4 text-3xl font-extrabold text-slate-900">
                        {{ number_format($activeMembers ?? 0, 0, ',', '.') }}
                    </p>

                    <p class="mt-2 text-sm text-slate-500">
                        Siswa dan guru aktif.
                    </p>
                </div>

                <div class="rounded-[2rem] border border-white/70 bg-white/80 p-6 shadow-[0_18px_50px_rgba(15,23,42,0.06)] backdrop-blur-xl">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                            Peminjaman Hari Ini
                        </p>

                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-700">
                            <span class="material-symbols-outlined">sync_alt</span>
                        </div>
                    </div>

                    <p class="mt-4 text-3xl font-extrabold text-slate-900">
                        {{ number_format($loansToday ?? 0, 0, ',', '.') }}
                    </p>

                    <p class="mt-2 text-sm text-slate-500">
                        {{ number_format($activeLoans ?? 0, 0, ',', '.') }} masih diproses.
                    </p>
                </div>

                <div class="rounded-[2rem] border border-red-100 bg-red-50/80 p-6 shadow-[0_18px_50px_rgba(15,23,42,0.06)] backdrop-blur-xl">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                            Denda Belum Dibayar
                        </p>

                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-red-100 text-red-700">
                            <span class="material-symbols-outlined">payments</span>
                        </div>
                    </div>

                    <p class="mt-4 text-3xl font-extrabold text-red-600">
                        Rp {{ number_format($estimatedFines ?? 0, 0, ',', '.') }}
                    </p>

                    <p class="mt-2 text-sm text-slate-500">
                        Dari {{ number_format($overdueLoansCount ?? 0, 0, ',', '.') }} transaksi telat.
                    </p>
                </div>
            </div>

            <div class="mt-8 grid gap-6 lg:grid-cols-3">
                <div class="rounded-[2rem] border border-white/70 bg-white/80 p-6 shadow-[0_18px_50px_rgba(15,23,42,0.06)] backdrop-blur-xl">
                    <div class="mb-5 flex items-start justify-between">
                        <div>
                            <h3 class="text-lg font-extrabold text-slate-900">
                                Sorotan Buku
                            </h3>
                            <p class="mt-1 text-sm text-slate-500">
                                Koleksi yang sering tampil di sistem.
                            </p>
                        </div>

                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                            <span class="material-symbols-outlined">menu_book</span>
                        </div>
                    </div>

                    <div class="space-y-4">
                        @forelse($popularBooks ?? [] as $book)
                            <a href="{{ route('books.show', $book) }}"
                               class="flex items-center gap-4 rounded-3xl bg-white p-4 shadow-sm transition hover:bg-emerald-50/60">
                                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700">
                                    <span class="material-symbols-outlined">import_contacts</span>
                                </div>

                                <div class="min-w-0">
                                    <p class="truncate text-sm font-extrabold text-slate-900">
                                        {{ $book->title }}
                                    </p>

                                    <p class="mt-1 truncate text-xs text-slate-500">
                                        {{ $book->author ?? '-' }}
                                    </p>

                                    <p class="mt-2 text-xs font-extrabold uppercase tracking-wider text-emerald-700">
                                        Koleksi Buku
                                    </p>
                                </div>
                            </a>
                        @empty
                            <div class="rounded-3xl border border-dashed border-slate-200 bg-slate-50 p-6 text-center text-sm text-slate-500">
                                Belum ada data buku.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-[2rem] border border-white/70 bg-white/80 p-6 shadow-[0_18px_50px_rgba(15,23,42,0.06)] backdrop-blur-xl lg:col-span-2">
                    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h3 class="text-lg font-extrabold text-slate-900">
                                Transaksi Terbaru
                            </h3>
                            <p class="mt-1 text-sm text-slate-500">
                                Aktivitas peminjaman terbaru di perpustakaan.
                            </p>
                        </div>

                        <a href="{{ route('loans.index') }}"
                           class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-700/20 transition hover:bg-emerald-800">
                            Lihat Semua
                            <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[720px] text-left text-sm">
                            <thead>
                                <tr class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">
                                    <th class="px-4 py-3">ID Transaksi</th>
                                    <th class="px-4 py-3">Peminjam</th>
                                    <th class="px-4 py-3">Status</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100">
                                @forelse($recentLoans ?? [] as $loan)
                                    @php
                                        $today = \Carbon\Carbon::today()->startOfDay();
                                        $dueDate = $loan->due_date ? \Carbon\Carbon::parse($loan->due_date)->startOfDay() : null;
                                        $isLate = in_array($loan->status, ['aktif', 'terlambat']) && $dueDate && $today->gt($dueDate);
                                    @endphp

                                    <tr class="transition hover:bg-emerald-50/40">
                                        <td class="px-4 py-4">
                                            <a href="{{ route('loans.show', $loan) }}"
                                               class="font-extrabold text-slate-900 hover:text-emerald-700">
                                                {{ $loan->loan_code }}
                                            </a>

                                            <p class="mt-1 text-xs text-slate-500">
                                                {{ $loan->loan_date ? \Carbon\Carbon::parse($loan->loan_date)->format('d M Y') : '-' }}
                                            </p>
                                        </td>

                                        <td class="px-4 py-4">
                                            <p class="font-bold text-slate-900">
                                                {{ $loan->member->name ?? '-' }}
                                            </p>

                                            <p class="mt-1 text-xs text-slate-500">
                                                {{ $loan->member->nis_nip ?? '-' }}
                                            </p>
                                        </td>

                                        <td class="px-4 py-4">
                                            @if($isLate)
                                                <span class="inline-flex rounded-full border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-bold text-red-700">
                                                    Terlambat
                                                </span>
                                            @elseif($loan->status === 'aktif')
                                                <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">
                                                    Dipinjam
                                                </span>
                                            @elseif($loan->status === 'selesai')
                                                <span class="inline-flex rounded-full border border-sky-200 bg-sky-50 px-3 py-1.5 text-xs font-bold text-sky-700">
                                                    Selesai
                                                </span>
                                            @else
                                                <span class="inline-flex rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-600">
                                                    {{ ucfirst($loan->status ?? '-') }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-10 text-center text-sm text-slate-500">
                                            Belum ada transaksi terbaru.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>