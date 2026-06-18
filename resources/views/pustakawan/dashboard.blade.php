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
                                        {{ number_format($book->borrow_count ?? 0, 0, ',', '.') }} kali dipinjam
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
                        <table class="w-full min-w-[860px] text-left text-sm">
                            <thead>
                                <tr class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">
                                    <th class="px-4 py-3">ID Transaksi</th>
                                    <th class="px-4 py-3">Peminjam</th>
                                    <th class="px-4 py-3">Buku</th>
                                    <th class="px-4 py-3">Status</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100">
                                @forelse($recentLoans ?? [] as $loan)
                                    @php
                                        $today = \Carbon\Carbon::today()->startOfDay();
                                        $dueDate = $loan->due_date ? \Carbon\Carbon::parse($loan->due_date)->startOfDay() : null;
                                        $isLate = in_array($loan->status, ['aktif', 'terlambat']) && $dueDate && $today->gt($dueDate);
                                        $bookTitles = $loan->loanItems
                                            ->map(fn ($item) => $item->bookItem?->book?->title)
                                            ->filter()
                                            ->unique()
                                            ->values();
                                        $visibleBookTitles = $bookTitles->take(2);
                                        $remainingBookCount = max($bookTitles->count() - $visibleBookTitles->count(), 0);
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
                                            @if($bookTitles->isNotEmpty())
                                                <p class="max-w-xs truncate font-bold text-slate-900">
                                                    {{ $visibleBookTitles->join(', ') }}
                                                </p>

                                                <p class="mt-1 text-xs text-slate-500">
                                                    {{ number_format($loan->loanItems->count(), 0, ',', '.') }} eksemplar
                                                    @if($remainingBookCount > 0)
                                                        +{{ number_format($remainingBookCount, 0, ',', '.') }} buku lainnya
                                                    @endif
                                                </p>
                                            @else
                                                <span class="text-sm text-slate-500">-</span>
                                            @endif
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

            @php
                $leaderboardStudents = collect($activeStudentLeaderboard ?? []);
                $topLeaderboardStudents = $leaderboardStudents->take(3);
                $rankStyles = [
                    1 => [
                        'label' => 'Gold',
                        'card' => 'border-amber-200 bg-gradient-to-br from-amber-50 via-white to-yellow-50 shadow-[0_18px_45px_rgba(245,158,11,0.16)] hover:border-amber-300',
                        'medal' => 'bg-amber-100 text-amber-700',
                        'badge' => 'bg-amber-400 text-amber-950',
                        'score' => 'bg-amber-100 text-amber-800',
                        'bar' => 'bg-amber-400',
                    ],
                    2 => [
                        'label' => 'Perak',
                        'card' => 'border-slate-200 bg-gradient-to-br from-slate-50 via-white to-zinc-50 shadow-[0_18px_45px_rgba(100,116,139,0.14)] hover:border-slate-300',
                        'medal' => 'bg-slate-100 text-slate-600',
                        'badge' => 'bg-slate-300 text-slate-900',
                        'score' => 'bg-slate-100 text-slate-700',
                        'bar' => 'bg-slate-400',
                    ],
                    3 => [
                        'label' => 'Perunggu',
                        'card' => 'border-orange-200 bg-gradient-to-br from-orange-50 via-white to-amber-50 shadow-[0_18px_45px_rgba(234,88,12,0.13)] hover:border-orange-300',
                        'medal' => 'bg-orange-100 text-orange-700',
                        'badge' => 'bg-orange-300 text-orange-950',
                        'score' => 'bg-orange-100 text-orange-800',
                        'bar' => 'bg-orange-400',
                    ],
                ];
                $defaultRankStyle = [
                    'label' => 'Peringkat',
                    'card' => 'border-slate-100 bg-white hover:border-emerald-200',
                    'medal' => 'bg-slate-100 text-slate-700',
                    'badge' => 'bg-slate-900 text-white',
                    'score' => 'bg-emerald-100 text-emerald-800',
                    'bar' => 'bg-emerald-500',
                ];
            @endphp

            <style>
                [x-cloak] {
                    display: none !important;
                }
            </style>

            <div x-data="{ leaderboardOpen: false }"
                 x-on:keydown.escape.window="leaderboardOpen = false"
                 class="mt-8 rounded-[2rem] border border-white/70 bg-white/80 p-6 shadow-[0_18px_50px_rgba(15,23,42,0.06)] backdrop-blur-xl">
                <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h3 class="text-lg font-extrabold text-slate-900">
                            Leaderboard Siswa Aktif
                        </h3>
                        <p class="mt-1 text-sm text-slate-500">
                            Peringkat berdasarkan jumlah peminjaman dan kunjungan perpustakaan.
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        @if($leaderboardStudents->isNotEmpty())
                            <button type="button"
                                    x-on:click="leaderboardOpen = true"
                                    class="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-slate-900/15 transition hover:bg-emerald-700">
                                <span class="material-symbols-outlined text-[18px]">leaderboard</span>
                                Lihat Detail 10 Teratas
                            </button>
                        @endif

                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-700">
                            <span class="material-symbols-outlined">workspace_premium</span>
                        </div>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    @forelse($topLeaderboardStudents as $index => $student)
                        @php
                            $rank = $index + 1;
                            $style = $rankStyles[$rank] ?? $defaultRankStyle;
                        @endphp

                        <a href="{{ route('members.show', $student) }}"
                           class="group relative overflow-hidden rounded-3xl border p-5 transition duration-300 hover:-translate-y-1 {{ $style['card'] }}">
                            <span class="pointer-events-none absolute inset-y-0 left-0 w-1/4 -translate-x-full -skew-x-12 bg-gradient-to-r from-transparent via-white/80 to-transparent opacity-0 transition duration-700 group-hover:translate-x-[520%] group-hover:opacity-100"></span>

                            <div class="relative flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl {{ $style['badge'] }} text-base font-extrabold">
                                        {{ $rank }}
                                    </div>

                                    <div class="min-w-0">
                                        <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-slate-400">
                                            Peringkat {{ $rank }}
                                        </p>
                                        <p class="mt-1 text-sm font-extrabold text-slate-900">
                                            {{ $style['label'] }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl {{ $style['medal'] }}">
                                    <span class="material-symbols-outlined">military_tech</span>
                                </div>
                            </div>

                            <div class="relative mt-5 min-w-0">
                                <p class="truncate text-base font-extrabold text-slate-900">
                                    {{ $student->name }}
                                </p>

                                <p class="mt-1 truncate text-xs text-slate-500">
                                    {{ $student->studentClass->class_name ?? 'Tanpa kelas' }}
                                </p>
                            </div>

                            <div class="relative mt-5 rounded-2xl {{ $style['score'] }} px-4 py-3">
                                <p class="text-xs font-bold uppercase tracking-[0.12em]">
                                    Total Aktivitas
                                </p>
                                <p class="mt-1 text-2xl font-extrabold">
                                    {{ number_format($student->activity_score ?? 0, 0, ',', '.') }}
                                </p>
                            </div>

                            <div class="relative mt-4 grid grid-cols-2 gap-2 text-xs">
                                <div class="rounded-2xl bg-white/70 p-3">
                                    <p class="font-extrabold text-emerald-700">
                                        {{ number_format($student->loans_count ?? 0, 0, ',', '.') }}
                                    </p>
                                    <p class="mt-1 text-slate-500">Peminjaman</p>
                                </div>

                                <div class="rounded-2xl bg-white/70 p-3">
                                    <p class="font-extrabold text-sky-700">
                                        {{ number_format($student->visits_count ?? 0, 0, ',', '.') }}
                                    </p>
                                    <p class="mt-1 text-slate-500">Kunjungan</p>
                                </div>
                            </div>

                            <div class="relative mt-4 h-1.5 overflow-hidden rounded-full bg-white/80">
                                <div class="h-full w-full rounded-full {{ $style['bar'] }}"></div>
                            </div>
                        </a>
                    @empty
                        <div class="rounded-3xl border border-dashed border-slate-200 bg-slate-50 p-6 text-center text-sm text-slate-500 md:col-span-3">
                            Belum ada riwayat peminjaman atau kunjungan siswa.
                        </div>
                    @endforelse
                </div>

                @if($leaderboardStudents->isNotEmpty())
                    <div x-cloak
                         x-show="leaderboardOpen"
                         x-transition.opacity
                         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 px-4 py-6">
                        <div class="absolute inset-0" x-on:click="leaderboardOpen = false"></div>

                        <div x-show="leaderboardOpen"
                             x-transition
                             class="relative flex max-h-[90vh] w-full max-w-4xl flex-col overflow-hidden rounded-[2rem] bg-white shadow-2xl">
                            <div class="flex items-start justify-between gap-4 border-b border-slate-100 p-6">
                                <div>
                                    <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-emerald-700">
                                        Detail Leaderboard
                                    </p>
                                    <h3 class="mt-1 text-xl font-extrabold text-slate-900">
                                        10 Siswa Teratas
                                    </h3>
                                    <p class="mt-1 text-sm text-slate-500">
                                        Urutan berdasarkan total peminjaman dan kunjungan perpustakaan.
                                    </p>
                                </div>

                                <button type="button"
                                        x-on:click="leaderboardOpen = false"
                                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-slate-100 text-slate-600 transition hover:bg-slate-200"
                                        aria-label="Tutup detail leaderboard">
                                    <span class="material-symbols-outlined text-[20px]">close</span>
                                </button>
                            </div>

                            <div class="overflow-y-auto p-6">
                                <div class="space-y-3">
                                    @foreach($leaderboardStudents as $index => $student)
                                        @php
                                            $rank = $index + 1;
                                            $style = $rankStyles[$rank] ?? $defaultRankStyle;
                                        @endphp

                                        <a href="{{ route('members.show', $student) }}"
                                           class="group flex flex-col gap-4 rounded-3xl border border-slate-100 bg-slate-50/70 p-4 transition hover:border-emerald-200 hover:bg-emerald-50/50 sm:flex-row sm:items-center">
                                            <div class="flex items-center gap-3 sm:w-72">
                                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl {{ $style['badge'] }} text-sm font-extrabold">
                                                    {{ $rank }}
                                                </div>

                                                <div class="min-w-0">
                                                    <p class="truncate text-sm font-extrabold text-slate-900">
                                                        {{ $student->name }}
                                                    </p>
                                                    <p class="mt-1 truncate text-xs text-slate-500">
                                                        {{ $student->studentClass->class_name ?? 'Tanpa kelas' }}
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="grid flex-1 grid-cols-3 gap-2 text-xs">
                                                <div class="rounded-2xl bg-white p-3">
                                                    <p class="font-extrabold text-slate-900">
                                                        {{ number_format($student->activity_score ?? 0, 0, ',', '.') }}
                                                    </p>
                                                    <p class="mt-1 text-slate-500">Aktivitas</p>
                                                </div>

                                                <div class="rounded-2xl bg-white p-3">
                                                    <p class="font-extrabold text-emerald-700">
                                                        {{ number_format($student->loans_count ?? 0, 0, ',', '.') }}
                                                    </p>
                                                    <p class="mt-1 text-slate-500">Peminjaman</p>
                                                </div>

                                                <div class="rounded-2xl bg-white p-3">
                                                    <p class="font-extrabold text-sky-700">
                                                        {{ number_format($student->visits_count ?? 0, 0, ',', '.') }}
                                                    </p>
                                                    <p class="mt-1 text-slate-500">Kunjungan</p>
                                                </div>
                                            </div>

                                            <div class="hidden items-center gap-1 text-xs font-extrabold text-emerald-700 sm:flex">
                                                Detail
                                                <span class="material-symbols-outlined text-[18px] transition group-hover:translate-x-1">arrow_forward</span>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
