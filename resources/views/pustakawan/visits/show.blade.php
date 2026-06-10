<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
                    Buku Tamu Digital
                </p>
                <h2 class="mt-1 text-xl font-bold text-gray-900">
                    Detail Kunjungan
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Informasi kunjungan perpustakaan yang tercatat di sistem.
                </p>
            </div>

            <a href="{{ route('visits.index') }}"
               class="inline-flex items-center justify-center gap-2 rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-600 transition hover:bg-gray-50">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Kembali
            </a>
        </div>
    </x-slot>

    @php
        $visitTypeLabels = [
            'siswa' => 'Siswa',
            'guru' => 'Guru',
            'staf' => 'Staf',
            'umum' => 'Umum',
        ];

        $visitDateText = $visit->visit_date
            ? \Carbon\Carbon::parse($visit->visit_date)->format('d/m/Y')
            : '-';
        $visitTimeText = $visit->check_in_time
            ? \Illuminate\Support\Str::of($visit->check_in_time)->substr(0, 5)
            : '-';
    @endphp

    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-emerald-50/40 to-sky-50/40 py-10">
        <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/90 shadow-[0_18px_50px_rgba(15,23,42,0.06)] backdrop-blur-xl">
                <div class="relative overflow-hidden bg-gradient-to-r from-emerald-700 to-teal-500 p-6 text-white">
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                        <div class="flex items-start gap-4">
                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/20">
                                <span class="material-symbols-outlined text-[30px]">how_to_reg</span>
                            </div>

                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-50">
                                    Pengunjung
                                </p>
                                <h3 class="mt-2 text-2xl font-extrabold leading-tight">
                                    {{ $visit->visitor_name }}
                                </h3>
                                <p class="mt-2 text-sm text-emerald-50">
                                    {{ $visit->identity_number ?? '-' }}
                                </p>
                            </div>
                        </div>

                        <span class="inline-flex w-fit rounded-full border border-white/25 bg-white/20 px-4 py-2 text-sm font-bold text-white">
                            {{ $visitTypeLabels[$visit->visitor_type] ?? ucwords((string) $visit->visitor_type) }}
                        </span>
                    </div>
                </div>

                <div class="space-y-6 p-6 md:p-8">
                    <section class="grid gap-4 md:grid-cols-3">
                        <div class="rounded-3xl border border-emerald-100 bg-emerald-50 p-5">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-emerald-700">Tanggal</p>
                            <p class="mt-2 text-lg font-extrabold text-emerald-900">{{ $visitDateText }}</p>
                        </div>

                        <div class="rounded-3xl border border-sky-100 bg-sky-50 p-5">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-sky-700">Jam Masuk</p>
                            <p class="mt-2 text-lg font-extrabold text-sky-900">{{ $visitTimeText }}</p>
                        </div>

                        <div class="rounded-3xl border border-amber-100 bg-amber-50 p-5">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-amber-700">Kelas</p>
                            <p class="mt-2 text-lg font-extrabold text-amber-900">{{ $visit->studentClass?->class_name ?? '-' }}</p>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm">
                        <h4 class="font-bold text-gray-900">Detail Kunjungan</h4>

                        <div class="mt-5 grid gap-4 md:grid-cols-2">
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-400">Keperluan</p>
                                <p class="mt-2 font-bold text-gray-900">{{ $visit->visit_purpose }}</p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-400">Petugas Pencatat</p>
                                <p class="mt-2 font-bold text-gray-900">{{ $visit->recorder?->name ?? '-' }}</p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-400">Anggota Terkait</p>
                                <p class="mt-2 font-bold text-gray-900">{{ $visit->member?->name ?? '-' }}</p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-400">Waktu Input</p>
                                <p class="mt-2 font-bold text-gray-900">
                                    {{ $visit->created_at ? $visit->created_at->format('d/m/Y H:i') : '-' }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4 md:col-span-2">
                                <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-400">Catatan</p>
                                <p class="mt-2 leading-6 text-gray-700">{{ $visit->notes ?? 'Tidak ada catatan tambahan.' }}</p>
                            </div>
                        </div>
                    </section>

                    <div class="flex flex-col gap-3 border-t border-gray-100 pt-6 sm:flex-row sm:justify-end">
                        @if($canManage)
                            <a href="{{ route('visits.edit', $visit) }}"
                               class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-6 py-3 text-sm font-bold text-white transition hover:bg-emerald-800">
                                <span class="material-symbols-outlined text-[18px]">edit</span>
                                Edit Kunjungan
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
