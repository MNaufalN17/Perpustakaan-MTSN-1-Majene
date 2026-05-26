<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
                    Laporan Kepala Sekolah
                </p>

                <h2 class="mt-1 text-xl font-bold text-gray-900">
                    Laporan Anggota Perpustakaan
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

                <a href="{{ route('kepala_sekolah.reports.members.download', request()->only(['keyword', 'member_type', 'status', 'class_filter'])) }}"
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
                  action="{{ route('kepala_sekolah.reports.members') }}"
                  class="mb-6 rounded-[2rem] border border-white/70 bg-white/90 p-6 shadow-sm backdrop-blur-xl">
                <div class="grid gap-4 lg:grid-cols-[1.5fr_1fr_1fr_1fr_auto]">
                    <div>
                        <label for="keyword" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                            Cari Anggota
                        </label>

                        <input
                            id="keyword"
                            type="text"
                            name="keyword"
                            value="{{ $keyword }}"
                            placeholder="Cari nama, kode anggota, NIS/NIP, kelas, atau nomor HP..."
                            class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                        >
                    </div>

                    <div>
                        <label for="member_type" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                            Jenis
                        </label>

                        <select
                            id="member_type"
                            name="member_type"
                            class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                        >
                            <option value="">Semua Jenis</option>
                            <option value="siswa" @selected($memberType === 'siswa')>Siswa</option>
                            <option value="guru" @selected($memberType === 'guru')>Guru</option>
                        </select>
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
                            <option value="aktif" @selected($status === 'aktif')>Aktif</option>
                            <option value="nonaktif" @selected($status === 'nonaktif')>Nonaktif</option>
                        </select>
                    </div>

                    <div>
                        <label for="class_filter" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                            Kelas
                        </label>

                        <select
                            id="class_filter"
                            name="class_filter"
                            class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                        >
                            <option value="">Semua Kelas</option>
                            @foreach($classOptions as $className)
                                <option value="{{ $className }}" @selected($classFilter === $className)>
                                    {{ $className }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-end gap-2">
                        <button type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-bold text-white transition hover:bg-slate-800">
                            <span class="material-symbols-outlined text-[18px]">filter_alt</span>
                            Terapkan
                        </button>

                        <a href="{{ route('kepala_sekolah.reports.members') }}"
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
                        Ringkasan data anggota perpustakaan
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                    <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Total Anggota</p>
                        <p class="mt-2 text-2xl font-extrabold text-slate-900">{{ number_format($totalMembers, 0, ',', '.') }}</p>
                    </div>

                    <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-emerald-700">Aktif</p>
                        <p class="mt-2 text-2xl font-extrabold text-emerald-800">{{ number_format($activeMembers, 0, ',', '.') }}</p>
                    </div>

                    <div class="rounded-2xl border border-red-100 bg-red-50 p-4">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-red-700">Nonaktif</p>
                        <p class="mt-2 text-2xl font-extrabold text-red-700">{{ number_format($inactiveMembers, 0, ',', '.') }}</p>
                    </div>

                    <div class="rounded-2xl border border-sky-100 bg-sky-50 p-4">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-sky-700">Siswa</p>
                        <p class="mt-2 text-2xl font-extrabold text-sky-800">{{ number_format($studentMembers, 0, ',', '.') }}</p>
                    </div>

                    <div class="rounded-2xl border border-purple-100 bg-purple-50 p-4">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-purple-700">Guru</p>
                        <p class="mt-2 text-2xl font-extrabold text-purple-800">{{ number_format($teacherMembers, 0, ',', '.') }}</p>
                    </div>
                </div>

                <div class="mt-8 grid gap-6 lg:grid-cols-3">
                    <div class="rounded-3xl border border-gray-100 bg-white p-5 lg:col-span-1">
                        <h4 class="text-base font-bold text-gray-900">
                            Rekap Per Kelas
                        </h4>

                        <div class="mt-4 overflow-x-auto rounded-2xl border border-gray-100">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-slate-50 text-xs uppercase tracking-wider text-gray-500">
                                    <tr>
                                        <th class="px-4 py-3">Kelas</th>
                                        <th class="px-4 py-3 text-center">Total</th>
                                        <th class="px-4 py-3 text-center">Aktif</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-100">
                                    @forelse($classRecaps as $recap)
                                        <tr>
                                            <td class="px-4 py-3 font-bold text-gray-900">
                                                {{ $recap['class_name'] }}
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                {{ number_format($recap['total'], 0, ',', '.') }}
                                            </td>
                                            <td class="px-4 py-3 text-center text-emerald-700 font-bold">
                                                {{ number_format($recap['active'], 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-4 py-8 text-center text-gray-500">
                                                Tidak ada rekap kelas.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-gray-100 bg-white p-5 lg:col-span-2">
                        <h4 class="text-base font-bold text-gray-900">
                            Daftar Anggota
                        </h4>

                        <div class="mt-4 overflow-x-auto rounded-2xl border border-gray-100">
                            <table class="w-full min-w-[900px] text-left text-sm">
                                <thead class="bg-slate-50 text-xs uppercase tracking-wider text-gray-500">
                                    <tr>
                                        <th class="px-5 py-4">Kode</th>
                                        <th class="px-5 py-4">Nama</th>
                                        <th class="px-5 py-4">NIS/NIP</th>
                                        <th class="px-5 py-4">Jenis</th>
                                        <th class="px-5 py-4">Kelas</th>
                                        <th class="px-5 py-4 text-center">Status</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @forelse($members as $member)
                                        <tr class="hover:bg-emerald-50/40">
                                            <td class="px-5 py-4 font-mono text-xs font-bold text-gray-900">
                                                {{ $member->member_code ?? '-' }}
                                            </td>

                                            <td class="px-5 py-4 font-bold text-gray-900">
                                                {{ $member->name ?? '-' }}
                                            </td>

                                            <td class="px-5 py-4 text-gray-700">
                                                {{ $member->nis_nip ?? '-' }}
                                            </td>

                                            <td class="px-5 py-4">
                                                {{ ucfirst($member->member_type ?? '-') }}
                                            </td>

                                            <td class="px-5 py-4">
                                                {{ $member->studentClass->class_name ?? 'Guru/Staff' }}
                                            </td>

                                            <td class="px-5 py-4 text-center">
                                                @if($member->status === 'aktif')
                                                    <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">
                                                        Aktif
                                                    </span>
                                                @else
                                                    <span class="inline-flex rounded-full border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-bold text-red-700">
                                                        Nonaktif
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-5 py-12 text-center text-sm text-gray-500">
                                                Tidak ada data anggota yang sesuai.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
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