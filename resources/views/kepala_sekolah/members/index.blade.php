<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
                    Data Anggota
                </p>
                <h2 class="mt-1 text-xl font-bold text-gray-900">
                    Data Anggota Perpustakaan
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Halaman ini bersifat read-only untuk Kepala Sekolah/Kepala Perpustakaan.
                </p>
            </div>

            <a href="{{ route('kepala_sekolah.dashboard') }}"
               class="inline-flex items-center justify-center gap-2 rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-600 transition hover:bg-gray-50">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Dashboard
            </a>
        </div>
    </x-slot>

    @php
        $memberCollection = method_exists($members, 'getCollection') ? $members->getCollection() : $members;

        $memberCount = method_exists($members, 'total') ? $members->total() : $memberCollection->count();
        $activeCount = $memberCollection->where('status', 'aktif')->count();
        $studentCount = $memberCollection->where('member_type', 'siswa')->count();
        $teacherCount = $memberCollection->where('member_type', 'guru')->count();

        $classOptions = $memberCollection
            ->map(fn ($member) => $member->studentClass->class_name ?? 'Guru/Staff')
            ->filter()
            ->unique()
            ->sort()
            ->values();
    @endphp

    <div
        class="min-h-screen bg-gradient-to-br from-slate-50 via-emerald-50/30 to-sky-50/30 py-8"
        x-data="{
            search: '',
            filterType: '',
            filterStatus: '',
            filterClass: '',

            matches(searchText, type, status, className) {
                const keyword = this.search.toLowerCase().trim();

                const matchSearch = keyword === '' || searchText.toLowerCase().includes(keyword);
                const matchType = this.filterType === '' || type === this.filterType;
                const matchStatus = this.filterStatus === '' || status === this.filterStatus;
                const matchClass = this.filterClass === '' || className === this.filterClass;

                return matchSearch && matchType && matchStatus && matchClass;
            },

            resetFilters() {
                this.search = '';
                this.filterType = '';
                this.filterStatus = '';
                this.filterClass = '';
            }
        }"
    >
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

            <div class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/80 shadow-[0_18px_50px_rgba(15,23,42,0.06)] backdrop-blur-xl">

                <div class="border-b border-gray-100 bg-white px-6 py-6">
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                                <span class="material-symbols-outlined">groups</span>
                            </div>

                            <div>
                                <h3 class="text-lg font-bold text-gray-900">
                                    Daftar Anggota
                                </h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Total {{ number_format($memberCount, 0, ',', '.') }} anggota tercatat dalam sistem.
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-2 sm:flex sm:flex-wrap sm:justify-end">
                            <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3">
                                <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-emerald-700">
                                    Aktif
                                </p>
                                <p class="mt-1 text-lg font-extrabold text-emerald-800">
                                    {{ $activeCount }}
                                </p>
                            </div>

                            <div class="rounded-2xl border border-sky-100 bg-sky-50 px-4 py-3">
                                <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-sky-700">
                                    Siswa
                                </p>
                                <p class="mt-1 text-lg font-extrabold text-sky-800">
                                    {{ $studentCount }}
                                </p>
                            </div>

                            <div class="rounded-2xl border border-purple-100 bg-purple-50 px-4 py-3">
                                <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-purple-700">
                                    Guru
                                </p>
                                <p class="mt-1 text-lg font-extrabold text-purple-800">
                                    {{ $teacherCount }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-3 lg:grid-cols-[1.5fr_1fr_1fr_1fr_auto]">
                        <div class="relative">
                            <span class="material-symbols-outlined pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[20px] text-gray-400">
                                search
                            </span>

                            <input
                                type="text"
                                x-model="search"
                                placeholder="Cari nama, kode anggota, NIS/NIP, atau kelas..."
                                class="w-full rounded-2xl border border-gray-200 bg-slate-50 px-12 py-3 text-sm text-gray-800 outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100"
                            >
                        </div>

                        <div class="relative">
                            <select
                                x-model="filterType"
                                class="w-full appearance-none rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 pr-10 text-sm text-gray-700 outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100"
                            >
                                <option value="">Semua Jenis</option>
                                <option value="siswa">Siswa</option>
                                <option value="guru">Guru</option>
                            </select>

                            <span class="material-symbols-outlined pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-[20px] text-gray-400">
                                expand_more
                            </span>
                        </div>

                        <div class="relative">
                            <select
                                x-model="filterStatus"
                                class="w-full appearance-none rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 pr-10 text-sm text-gray-700 outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100"
                            >
                                <option value="">Semua Status</option>
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>

                            <span class="material-symbols-outlined pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-[20px] text-gray-400">
                                expand_more
                            </span>
                        </div>

                        <div class="relative">
                            <select
                                x-model="filterClass"
                                class="w-full appearance-none rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 pr-10 text-sm text-gray-700 outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100"
                            >
                                <option value="">Semua Kelas</option>
                                @foreach($classOptions as $className)
                                    <option value="{{ $className }}">{{ $className }}</option>
                                @endforeach
                            </select>

                            <span class="material-symbols-outlined pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-[20px] text-gray-400">
                                expand_more
                            </span>
                        </div>

                        <button
                            type="button"
                            @click="resetFilters()"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-bold text-gray-600 transition hover:bg-gray-50"
                        >
                            <span class="material-symbols-outlined text-[18px]">restart_alt</span>
                            Reset
                        </button>
                    </div>
                </div>

                <div class="p-6">
                    <div class="overflow-x-auto rounded-3xl border border-gray-100 bg-white">
                        <table class="min-w-[1000px] w-full divide-y divide-gray-100 text-left text-sm">
                            <thead class="bg-slate-50 text-xs uppercase tracking-wider text-gray-500">
                                <tr>
                                    <th class="px-5 py-4 font-bold w-[160px]">Kode</th>
                                    <th class="px-5 py-4 font-bold w-[280px]">Anggota</th>
                                    <th class="px-5 py-4 font-bold w-[140px]">Jenis</th>
                                    <th class="px-5 py-4 font-bold w-[170px]">Kelas</th>
                                    <th class="px-5 py-4 font-bold w-[140px]">Status</th>
                                    <th class="px-5 py-4 text-center font-bold w-[120px]">Aksi</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse($members as $member)
                                    @php
                                        $className = $member->studentClass->class_name ?? 'Guru/Staff';

                                        $searchText = strtolower(
                                            ($member->member_code ?? '') . ' ' .
                                            ($member->name ?? '') . ' ' .
                                            ($member->nis_nip ?? '') . ' ' .
                                            ($member->member_type ?? '') . ' ' .
                                            ($className ?? '') . ' ' .
                                            ($member->status ?? '')
                                        );
                                    @endphp

                                    <tr
                                        class="transition hover:bg-emerald-50/40"
                                        x-show="matches(@js($searchText), @js($member->member_type), @js($member->status), @js($className))"
                                    >
                                        <td class="px-5 py-4 align-middle">
                                            <div class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2">
                                                <span class="material-symbols-outlined text-[16px] text-slate-500">badge</span>
                                                <span class="font-mono text-xs font-bold text-slate-800">
                                                    {{ $member->member_code }}
                                                </span>
                                            </div>
                                        </td>

                                        <td class="px-5 py-4 align-middle">
                                            <div class="flex items-center gap-3">
                                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700">
                                                    <span class="material-symbols-outlined text-[20px]">person</span>
                                                </div>

                                                <div>
                                                    <p class="font-bold leading-5 text-gray-900">
                                                        {{ $member->name }}
                                                    </p>
                                                    <p class="mt-1 text-xs text-gray-500">
                                                        NIS/NIP: {{ $member->nis_nip ?? '-' }}
                                                    </p>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="px-5 py-4 align-middle">
                                            @if($member->member_type === 'siswa')
                                                <span class="inline-flex items-center gap-1.5 rounded-full border border-sky-100 bg-sky-50 px-3 py-1.5 text-xs font-bold text-sky-700">
                                                    <span class="material-symbols-outlined text-[14px]">school</span>
                                                    Siswa
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 rounded-full border border-purple-100 bg-purple-50 px-3 py-1.5 text-xs font-bold text-purple-700">
                                                    <span class="material-symbols-outlined text-[14px]">person</span>
                                                    Guru
                                                </span>
                                            @endif
                                        </td>

                                        <td class="px-5 py-4 align-middle">
                                            <span class="text-sm font-medium text-gray-700">
                                                {{ $className }}
                                            </span>
                                        </td>

                                        <td class="px-5 py-4 align-middle">
                                            @if($member->status === 'aktif')
                                                <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                    Aktif
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs font-bold text-gray-600">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>
                                                    Nonaktif
                                                </span>
                                            @endif
                                        </td>

                                        <td class="px-5 py-4 text-center align-middle">
                                            <a href="{{ route('members.show', $member) }}"
                                               class="inline-flex h-9 items-center justify-center gap-1.5 rounded-xl border border-emerald-200 bg-white px-3 text-xs font-bold text-emerald-700 transition hover:bg-emerald-50">
                                                <span class="material-symbols-outlined text-[15px]">visibility</span>
                                                Lihat
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-14 text-center">
                                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                                                <span class="material-symbols-outlined">groups</span>
                                            </div>
                                            <p class="mt-4 text-sm font-semibold text-gray-700">
                                                Belum ada data anggota.
                                            </p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if(method_exists($members, 'links'))
                        <div class="mt-6">
                            {{ $members->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>