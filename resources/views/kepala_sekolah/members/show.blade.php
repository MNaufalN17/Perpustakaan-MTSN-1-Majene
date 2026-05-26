<x-app-layout>
    @php
        $cardFile = $member->card_file
            ?? $member->card_path
            ?? $member->identity_card
            ?? $member->member_card
            ?? null;
    @endphp

    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
                    Data Anggota
                </p>
                <h2 class="mt-1 text-xl font-bold text-gray-900">
                    Detail Anggota Perpustakaan
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Halaman ini bersifat read-only untuk Kepala Sekolah/Kepala Perpustakaan.
                </p>
            </div>

            <a href="{{ route('members.index') }}"
               class="inline-flex items-center justify-center gap-2 rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-600 transition hover:bg-gray-50">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-emerald-50/40 to-sky-50/40 py-10">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">

            <div class="overflow-hidden rounded-[2rem] border border-white/70 bg-white shadow-[0_20px_60px_rgba(15,23,42,0.08)]">

                <div class="relative overflow-hidden bg-gradient-to-r from-emerald-700 to-teal-500 p-6 text-white">
                    <div class="relative flex items-start justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/20 text-white">
                                <span class="material-symbols-outlined text-[26px]">account_circle</span>
                            </div>

                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-50">
                                    Informasi Anggota
                                </p>

                                <h3 class="mt-2 text-2xl font-extrabold leading-tight text-white">
                                    {{ $member->name }}
                                </h3>

                                <p class="mt-1 text-sm text-emerald-50">
                                    Kode Anggota: {{ $member->member_code ?? '-' }}
                                </p>
                            </div>
                        </div>

                        @if($member->status === 'aktif')
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-white/30 bg-white/15 px-3 py-1.5 text-xs font-bold text-white">
                                <span class="material-symbols-outlined text-[14px]">check_circle</span>
                                Aktif
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-white/30 bg-white/15 px-3 py-1.5 text-xs font-bold text-white">
                                <span class="material-symbols-outlined text-[14px]">block</span>
                                Nonaktif
                            </span>
                        @endif
                    </div>
                </div>

                <div class="space-y-6 p-6">

                    <section class="rounded-3xl border border-emerald-100 bg-emerald-50/50 p-5">
                        <div class="mb-5 flex items-start gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                                <span class="material-symbols-outlined text-[20px]">badge</span>
                            </div>

                            <div>
                                <h4 class="font-bold text-gray-900">
                                    Identitas Anggota
                                </h4>
                                <p class="mt-1 text-xs text-gray-500">
                                    Data utama anggota yang digunakan pada proses peminjaman.
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="rounded-2xl bg-white p-4">
                                <p class="text-xs font-bold uppercase tracking-[0.14em] text-emerald-700">
                                    Kode Anggota
                                </p>
                                <p class="mt-2 font-bold text-gray-900">
                                    {{ $member->member_code ?? '-' }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-white p-4">
                                <p class="text-xs font-bold uppercase tracking-[0.14em] text-emerald-700">
                                    NIS / NIP
                                </p>
                                <p class="mt-2 font-bold text-gray-900">
                                    {{ $member->nis_nip ?? '-' }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-white p-4">
                                <p class="text-xs font-bold uppercase tracking-[0.14em] text-emerald-700">
                                    Nama Lengkap
                                </p>
                                <p class="mt-2 font-bold text-gray-900">
                                    {{ $member->name ?? '-' }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-white p-4">
                                <p class="text-xs font-bold uppercase tracking-[0.14em] text-emerald-700">
                                    No. HP / WhatsApp
                                </p>
                                <p class="mt-2 font-bold text-gray-900">
                                    {{ $member->phone ?? $member->phone_number ?? '-' }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm">
                        <div class="mb-5 flex items-start gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                                <span class="material-symbols-outlined text-[20px]">groups</span>
                            </div>

                            <div>
                                <h4 class="font-bold text-gray-900">
                                    Informasi Keanggotaan
                                </h4>
                                <p class="mt-1 text-xs text-gray-500">
                                    Jenis anggota, kelas, jenis kelamin, dan status keanggotaan.
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-400">
                                    Jenis Anggota
                                </p>
                                <p class="mt-2 font-bold text-gray-900">
                                    {{ ucfirst($member->member_type ?? '-') }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-400">
                                    Jenis Kelamin
                                </p>
                                <p class="mt-2 font-bold text-gray-900">
                                    {{ $member->gender === 'L' || $member->gender === 'laki-laki' ? 'Laki-laki' : ($member->gender === 'P' || $member->gender === 'perempuan' ? 'Perempuan' : '-') }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-400">
                                    Kelas
                                </p>
                                <p class="mt-2 font-bold text-gray-900">
                                    {{ $member->studentClass->class_name ?? 'Guru/Staff' }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-400">
                                    Status
                                </p>

                                <div class="mt-2">
                                    @if($member->status === 'aktif')
                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">
                                            <span class="material-symbols-outlined text-[14px]">check_circle</span>
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs font-bold text-gray-600">
                                            <span class="material-symbols-outlined text-[14px]">block</span>
                                            Nonaktif
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm">
                        <div class="mb-5 flex items-start gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                                <span class="material-symbols-outlined text-[20px]">badge</span>
                            </div>

                            <div>
                                <h4 class="font-bold text-gray-900">
                                    Kartu Anggota / Identitas
                                </h4>
                                <p class="mt-1 text-xs text-gray-500">
                                    Kartu anggota atau data identitas yang pernah diunggah.
                                </p>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-emerald-100 bg-emerald-50/50 p-4">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-emerald-700">
                                        <span class="material-symbols-outlined text-[20px]">image</span>
                                    </div>

                                    <div>
                                        <p class="text-sm font-bold text-gray-900">
                                            File Kartu Anggota
                                        </p>
                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ $cardFile ? 'Kartu anggota tersedia.' : 'Belum ada kartu anggota yang diunggah.' }}
                                        </p>
                                    </div>
                                </div>

                                @if($cardFile)
                                    <a href="{{ asset('storage/' . $cardFile) }}"
                                       target="_blank"
                                       class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-emerald-200 bg-white px-4 py-2 text-xs font-bold text-emerald-700 transition hover:bg-emerald-50">
                                        <span class="material-symbols-outlined text-[15px]">open_in_new</span>
                                        Lihat File
                                    </a>
                                @else
                                    <span class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-gray-200 bg-white px-4 py-2 text-xs font-bold text-gray-500">
                                        <span class="material-symbols-outlined text-[15px]">image_not_supported</span>
                                        Tidak Ada Kartu
                                    </span>
                                @endif
                            </div>
                        </div>
                    </section>

                    <div class="border-t border-gray-100 pt-5">
                        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">
                            <a href="{{ route('members.index') }}"
                               class="inline-flex items-center justify-center gap-2 rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-600 transition hover:bg-gray-50">
                                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                                Kembali
                            </a>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>