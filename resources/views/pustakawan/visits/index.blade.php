<x-app-layout>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
                    Buku Tamu Digital
                </p>
                <h2 class="mt-1 text-xl font-bold text-gray-900">
                    Data Pengunjung Perpustakaan
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Catatan kunjungan anggota, guru, staf, dan pengunjung umum.
                </p>
            </div>

            @if($canManage)
                <a href="{{ route('visits.create') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-800">
                    <span class="material-symbols-outlined text-[18px]">person_add</span>
                    Catat Kunjungan
                </a>
            @endif
        </div>
    </x-slot>

    @php
        $visitTypeLabels = [
            'siswa' => 'Siswa',
            'guru' => 'Guru',
            'staf' => 'Staf',
            'umum' => 'Umum',
        ];
    @endphp

    <div
        x-data="visitDeleteModal()"
        @keydown.escape.window="closeDeleteModal()"
        class="min-h-screen bg-gradient-to-br from-slate-50 via-emerald-50/40 to-sky-50/40 py-10"
    >
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Total Kunjungan</p>
                    <p class="mt-2 text-3xl font-extrabold text-gray-900">{{ number_format($totalVisits, 0, ',', '.') }}</p>
                </div>

                <div class="rounded-3xl border border-emerald-100 bg-emerald-50 p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Hari Ini</p>
                    <p class="mt-2 text-3xl font-extrabold text-emerald-800">{{ number_format($todayVisits, 0, ',', '.') }}</p>
                </div>

                <div class="rounded-3xl border border-sky-100 bg-sky-50 p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wider text-sky-700">Bulan Ini</p>
                    <p class="mt-2 text-3xl font-extrabold text-sky-800">{{ number_format($monthVisits, 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/90 shadow-[0_18px_50px_rgba(15,23,42,0.06)] backdrop-blur-xl">
                <div class="border-b border-gray-100 bg-white/80 p-6">
                    <form method="GET" action="{{ route('visits.index') }}" class="grid gap-4 lg:grid-cols-6">
                        <div class="lg:col-span-2">
                            <label for="keyword" class="block text-sm font-bold text-gray-700">Cari Pengunjung</label>
                            <input
                                id="keyword"
                                type="text"
                                name="keyword"
                                value="{{ $keyword }}"
                                placeholder="Nama, NIS/NIP, kelas, atau keperluan..."
                                class="mt-2 block w-full rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                            >
                        </div>

                        <div>
                            <label for="visitor_type" class="block text-sm font-bold text-gray-700">Jenis</label>
                            <select
                                id="visitor_type"
                                name="visitor_type"
                                class="mt-2 block w-full rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                            >
                                <option value="">Semua</option>
                                @foreach($visitTypeLabels as $value => $label)
                                    <option value="{{ $value }}" @selected($visitorType === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="date_start" class="block text-sm font-bold text-gray-700">Dari</label>
                            <input
                                id="date_start"
                                type="date"
                                name="date_start"
                                value="{{ $dateStart }}"
                                class="mt-2 block w-full rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                            >
                        </div>

                        <div>
                            <label for="date_end" class="block text-sm font-bold text-gray-700">Sampai</label>
                            <input
                                id="date_end"
                                type="date"
                                name="date_end"
                                value="{{ $dateEnd }}"
                                class="mt-2 block w-full rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                            >
                        </div>

                        <div class="flex items-end gap-3">
                            <button
                                type="submit"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-4 py-3 text-sm font-bold text-white transition hover:bg-emerald-800"
                            >
                                <span class="material-symbols-outlined text-[18px]">search</span>
                                Filter
                            </button>

                            <a href="{{ route('visits.index') }}"
                               class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-bold text-gray-600 transition hover:bg-gray-50">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>

                <div class="p-6">
                    <div class="overflow-x-auto rounded-3xl border border-gray-100 bg-white">
                        <table class="w-full min-w-[1000px] divide-y divide-gray-100 text-left text-sm">
                            <thead class="bg-slate-50 text-xs uppercase tracking-wider text-gray-500">
                                <tr>
                                    <th class="w-[170px] px-5 py-4 font-bold">Tanggal</th>
                                    <th class="w-[250px] px-5 py-4 font-bold">Pengunjung</th>
                                    <th class="w-[180px] px-5 py-4 font-bold">Jenis / Kelas</th>
                                    <th class="px-5 py-4 font-bold">Keperluan</th>
                                    <th class="w-[180px] px-5 py-4 font-bold">Petugas</th>
                                    <th class="w-[180px] px-5 py-4 text-center font-bold">Aksi</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100">
                                @forelse($visits as $visit)
                                    @php
                                        $visitDateText = $visit->visit_date
                                            ? \Carbon\Carbon::parse($visit->visit_date)->format('d/m/Y')
                                            : '-';
                                        $visitTimeText = $visit->check_in_time
                                            ? \Illuminate\Support\Str::of($visit->check_in_time)->substr(0, 5)
                                            : '-';
                                        $typeLabel = $visitTypeLabels[$visit->visitor_type] ?? ucwords((string) $visit->visitor_type);
                                        $deletePayload = [
                                            'action' => route('visits.destroy', $visit),
                                            'visitor_name' => $visit->visitor_name,
                                            'visit_date' => $visitDateText,
                                            'visit_purpose' => $visit->visit_purpose,
                                        ];
                                        $deletePayloadEncoded = base64_encode(json_encode($deletePayload));
                                    @endphp

                                    <tr class="transition hover:bg-emerald-50/40">
                                        <td class="px-5 py-5 align-middle">
                                            <p class="font-bold text-gray-900">{{ $visitDateText }}</p>
                                            <p class="mt-1 text-xs text-gray-500">{{ $visitTimeText }}</p>
                                        </td>

                                        <td class="px-5 py-5 align-middle">
                                            <p class="font-extrabold text-gray-900">{{ $visit->visitor_name }}</p>
                                            <p class="mt-1 text-xs text-gray-500">{{ $visit->identity_number ?? '-' }}</p>
                                        </td>

                                        <td class="px-5 py-5 align-middle">
                                            <span class="inline-flex rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                                                {{ $typeLabel }}
                                            </span>
                                            <p class="mt-2 text-xs font-semibold text-gray-500">
                                                {{ $visit->studentClass?->class_name ?? 'Tanpa kelas' }}
                                            </p>
                                        </td>

                                        <td class="px-5 py-5 align-middle">
                                            <p class="font-semibold text-gray-800">{{ $visit->visit_purpose }}</p>
                                            @if($visit->notes)
                                                <p class="mt-1 line-clamp-2 text-xs leading-5 text-gray-500">{{ $visit->notes }}</p>
                                            @endif
                                        </td>

                                        <td class="px-5 py-5 align-middle text-sm text-gray-600">
                                            {{ $visit->recorder?->name ?? '-' }}
                                        </td>

                                        <td class="px-5 py-5 align-middle">
                                            <div class="mx-auto grid w-[160px] grid-cols-2 gap-2">
                                                <a href="{{ route('visits.show', $visit) }}"
                                                   class="inline-flex h-9 items-center justify-center gap-1 rounded-xl border border-emerald-200 bg-white px-2 text-xs font-extrabold text-emerald-700 transition hover:bg-emerald-50">
                                                    <span class="material-symbols-outlined text-[15px]">visibility</span>
                                                    Lihat
                                                </a>

                                                @if($canManage)
                                                    <a href="{{ route('visits.edit', $visit) }}"
                                                       class="inline-flex h-9 items-center justify-center gap-1 rounded-xl border border-teal-200 bg-white px-2 text-xs font-extrabold text-teal-700 transition hover:bg-teal-50">
                                                        <span class="material-symbols-outlined text-[15px]">edit</span>
                                                        Edit
                                                    </a>

                                                    <button
                                                        type="button"
                                                        data-delete-payload="{{ $deletePayloadEncoded }}"
                                                        @click="openDeleteModalFromButton($event.currentTarget)"
                                                        class="col-span-2 inline-flex h-9 items-center justify-center gap-1 rounded-xl border border-red-200 bg-red-50 px-2 text-xs font-extrabold text-red-700 transition hover:bg-red-100"
                                                    >
                                                        <span class="material-symbols-outlined text-[15px]">delete</span>
                                                        Hapus
                                                    </button>
                                                @else
                                                    <span class="inline-flex h-9 items-center justify-center rounded-xl border border-gray-200 bg-gray-50 px-2 text-xs font-bold text-gray-400">-</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-14 text-center">
                                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                                                <span class="material-symbols-outlined">how_to_reg</span>
                                            </div>
                                            <p class="mt-4 text-sm font-semibold text-gray-700">Belum ada kunjungan tercatat.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if(method_exists($visits, 'links'))
                        <div class="mt-6">
                            {{ $visits->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div
            x-show="deleteModalOpen"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 px-4 py-8 backdrop-blur-md"
        >
            <div
                @click.outside="closeDeleteModal()"
                x-show="deleteModalOpen"
                x-transition
                class="w-full max-w-lg overflow-hidden rounded-[2rem] bg-white shadow-2xl"
            >
                <div class="bg-gradient-to-r from-red-700 to-rose-500 px-6 py-5 text-white">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-extrabold">Hapus Catatan Kunjungan</h3>
                            <p class="mt-1 text-sm text-red-50">Data yang dihapus tidak tampil lagi di buku tamu.</p>
                        </div>

                        <button
                            type="button"
                            @click="closeDeleteModal()"
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white/15 transition hover:bg-white/25"
                        >
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>
                </div>

                <div class="space-y-4 p-6">
                    <div class="rounded-3xl border border-gray-100 bg-slate-50 p-5">
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Pengunjung</p>
                        <p class="mt-2 text-lg font-extrabold text-gray-900" x-text="deleteVisit.visitor_name"></p>
                        <p class="mt-1 text-sm text-gray-600">
                            <span x-text="deleteVisit.visit_date"></span>
                            <span> - </span>
                            <span x-text="deleteVisit.visit_purpose"></span>
                        </p>
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-gray-100 bg-white px-6 py-5 sm:flex-row sm:justify-end">
                    <button
                        type="button"
                        @click="closeDeleteModal()"
                        class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-600 transition hover:bg-gray-50"
                    >
                        Batal
                    </button>

                    <form method="POST" :action="deleteVisit.action">
                        @csrf
                        @method('DELETE')

                        <button
                            type="submit"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-red-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-red-600/20 transition hover:bg-red-700"
                        >
                            <span class="material-symbols-outlined text-[18px]">delete</span>
                            Ya, Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <script>
            function visitDeleteModal() {
                return {
                    deleteModalOpen: false,
                    deleteVisit: {
                        action: '',
                        visitor_name: '',
                        visit_date: '',
                        visit_purpose: '',
                    },

                    openDeleteModalFromButton(button) {
                        try {
                            this.deleteVisit = JSON.parse(atob(button.dataset.deletePayload || ''));
                        } catch (error) {
                            this.deleteVisit = {
                                action: '',
                                visitor_name: 'Data kunjungan gagal dibaca',
                                visit_date: '-',
                                visit_purpose: '-',
                            };
                        }

                        this.deleteModalOpen = true;
                    },

                    closeDeleteModal() {
                        this.deleteModalOpen = false;
                        this.deleteVisit = {
                            action: '',
                            visitor_name: '',
                            visit_date: '',
                            visit_purpose: '',
                        };
                    },
                };
            }
        </script>
    </div>
</x-app-layout>
