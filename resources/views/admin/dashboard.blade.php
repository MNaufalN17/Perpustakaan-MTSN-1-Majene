<x-app-layout>
    @php
        $schoolName = \App\Models\SystemSetting::getValue('school_name', 'MTsN 1 Majene');
        $libraryName = \App\Models\SystemSetting::getValue('library_name', 'Sistem Manajemen Perpustakaan');
    @endphp

    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
                    Staff IT Admin
                </p>

                <h2 class="mt-1 text-xl font-bold text-gray-900">
                    Dashboard Admin
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    {{ $libraryName }} — {{ $schoolName }}
                </p>
            </div>

            <a href="{{ route('users.index') }}"
               class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800">
                <span class="material-symbols-outlined text-[18px]">manage_accounts</span>
                Kelola User
            </a>
        </div>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-emerald-50/40 to-sky-50/40 py-10">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-3xl border border-emerald-100 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-500">
                            Total User
                        </p>
                        <span class="material-symbols-outlined text-emerald-700">manage_accounts</span>
                    </div>

                    <p class="mt-4 text-3xl font-extrabold text-gray-900">
                        {{ number_format($totalUsers ?? 0, 0, ',', '.') }}
                    </p>

                    <p class="mt-1 text-xs text-gray-500">
                        Seluruh akun login sistem.
                    </p>
                </div>

                <div class="rounded-3xl border border-emerald-100 bg-emerald-50 p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-emerald-700">
                            User Aktif
                        </p>
                        <span class="material-symbols-outlined text-emerald-700">verified_user</span>
                    </div>

                    <p class="mt-4 text-3xl font-extrabold text-emerald-800">
                        {{ number_format($activeUsers ?? 0, 0, ',', '.') }}
                    </p>

                    <p class="mt-1 text-xs text-emerald-700">
                        Akun yang bisa digunakan.
                    </p>
                </div>

                <div class="rounded-3xl border border-red-100 bg-red-50 p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-red-700">
                            User Nonaktif
                        </p>
                        <span class="material-symbols-outlined text-red-700">person_off</span>
                    </div>

                    <p class="mt-4 text-3xl font-extrabold text-red-700">
                        {{ number_format($inactiveUsers ?? 0, 0, ',', '.') }}
                    </p>

                    <p class="mt-1 text-xs text-red-600">
                        Akun yang dinonaktifkan.
                    </p>
                </div>

                <div class="rounded-3xl border border-amber-100 bg-amber-50 p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-amber-700">
                            Transaksi Terlambat
                        </p>
                        <span class="material-symbols-outlined text-amber-700">warning</span>
                    </div>

                    <p class="mt-4 text-3xl font-extrabold text-amber-800">
                        {{ number_format($overdueLoans ?? 0, 0, ',', '.') }}
                    </p>

                    <p class="mt-1 text-xs text-amber-700">
                        Peminjaman melewati jatuh tempo.
                    </p>
                </div>
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-3">
                <div class="rounded-[2rem] border border-white/70 bg-white/80 p-6 shadow-sm backdrop-blur-xl lg:col-span-2">
                    <div class="mb-5 flex items-center justify-between">
                        <div>
                            <h3 class="font-bold text-gray-900">
                                Ringkasan Data Sistem
                            </h3>

                            <p class="mt-1 text-sm text-gray-500">
                                Kondisi data utama pada aplikasi perpustakaan.
                            </p>
                        </div>

                        <span class="material-symbols-outlined text-emerald-700">dns</span>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        <div class="rounded-2xl border border-gray-100 bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-500">
                                Total Anggota
                            </p>

                            <p class="mt-2 text-2xl font-extrabold text-gray-900">
                                {{ number_format($totalMembers ?? 0, 0, ',', '.') }}
                            </p>

                            <p class="mt-1 text-xs text-gray-500">
                                Aktif: {{ number_format($activeMembers ?? 0, 0, ',', '.') }}
                            </p>
                        </div>

                        <div class="rounded-2xl border border-gray-100 bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-500">
                                Buku Induk
                            </p>

                            <p class="mt-2 text-2xl font-extrabold text-gray-900">
                                {{ number_format($totalBooks ?? 0, 0, ',', '.') }}
                            </p>
                        </div>

                        <div class="rounded-2xl border border-gray-100 bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-500">
                                Eksemplar Buku
                            </p>

                            <p class="mt-2 text-2xl font-extrabold text-gray-900">
                                {{ number_format($totalBookItems ?? 0, 0, ',', '.') }}
                            </p>
                        </div>

                        <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-emerald-700">
                                Buku Tersedia
                            </p>

                            <p class="mt-2 text-2xl font-extrabold text-emerald-800">
                                {{ number_format($availableBookItems ?? 0, 0, ',', '.') }}
                            </p>
                        </div>

                        <div class="rounded-2xl border border-sky-100 bg-sky-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-sky-700">
                                Buku Dipinjam
                            </p>

                            <p class="mt-2 text-2xl font-extrabold text-sky-800">
                                {{ number_format($borrowedBookItems ?? 0, 0, ',', '.') }}
                            </p>
                        </div>

                        <div class="rounded-2xl border border-red-100 bg-red-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-red-700">
                                Rusak / Hilang
                            </p>

                            <p class="mt-2 text-2xl font-extrabold text-red-700">
                                {{ number_format($problematicBookItems ?? 0, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="rounded-[2rem] border border-white/70 bg-white/80 p-6 shadow-sm backdrop-blur-xl">
                    <div class="mb-5">
                        <h3 class="font-bold text-gray-900">
                            Komposisi Role
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Jumlah akun berdasarkan role.
                        </p>
                    </div>

                    <div class="space-y-3">
                        <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-emerald-700">
                                Staff IT Admin
                            </p>

                            <p class="mt-2 text-xl font-extrabold text-emerald-800">
                                {{ number_format($adminUsers ?? 0, 0, ',', '.') }}
                            </p>
                        </div>

                        <div class="rounded-2xl border border-sky-100 bg-sky-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-sky-700">
                                Pustakawan
                            </p>

                            <p class="mt-2 text-xl font-extrabold text-sky-800">
                                {{ number_format($pustakawanUsers ?? 0, 0, ',', '.') }}
                            </p>
                        </div>

                        <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-amber-700">
                                Kepala Perpustakaan
                            </p>

                            <p class="mt-2 text-xl font-extrabold text-amber-800">
                                {{ number_format($kepalaUsers ?? 0, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-2">
                <div class="rounded-[2rem] border border-white/70 bg-white/80 p-6 shadow-sm backdrop-blur-xl">
                    <div class="mb-5 flex items-center justify-between">
                        <div>
                            <h3 class="font-bold text-gray-900">
                                Aktivitas Sirkulasi
                            </h3>

                            <p class="mt-1 text-sm text-gray-500">
                                Ringkasan transaksi perpustakaan.
                            </p>
                        </div>

                        <span class="material-symbols-outlined text-emerald-700">receipt_long</span>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="rounded-2xl border border-gray-100 bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-500">
                                Hari Ini
                            </p>

                            <p class="mt-2 text-2xl font-extrabold text-gray-900">
                                {{ number_format($loansToday ?? 0, 0, ',', '.') }}
                            </p>
                        </div>

                        <div class="rounded-2xl border border-gray-100 bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-500">
                                Aktif
                            </p>

                            <p class="mt-2 text-2xl font-extrabold text-gray-900">
                                {{ number_format($activeLoans ?? 0, 0, ',', '.') }}
                            </p>
                        </div>

                        <div class="rounded-2xl border border-red-100 bg-red-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-red-700">
                                Estimasi Denda
                            </p>

                            <p class="mt-2 text-2xl font-extrabold text-red-700">
                                Rp {{ number_format($estimatedFines ?? 0, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="rounded-[2rem] border border-white/70 bg-white/80 p-6 shadow-sm backdrop-blur-xl">
                    <div class="mb-5">
                        <h3 class="font-bold text-gray-900">
                            Transaksi Terbaru
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Lima transaksi terakhir yang tercatat.
                        </p>
                    </div>

                    <div class="space-y-3">
                        @forelse($recentLoans ?? [] as $loan)
                            <a href="{{ route('loans.show', $loan) }}"
                               class="block rounded-2xl border border-gray-100 bg-slate-50 p-4 transition hover:bg-emerald-50/50">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-bold text-gray-900">
                                            {{ $loan->loan_code }}
                                        </p>

                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ $loan->member->name ?? '-' }}
                                        </p>
                                    </div>

                                    <span class="rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                                        {{ strtoupper($loan->status ?? '-') }}
                                    </span>
                                </div>
                            </a>
                        @empty
                            <div class="rounded-2xl border border-gray-100 bg-slate-50 p-5 text-center text-sm text-gray-500">
                                Belum ada transaksi terbaru.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>