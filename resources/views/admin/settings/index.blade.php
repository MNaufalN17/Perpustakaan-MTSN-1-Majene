<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
                    Admin IT
                </p>

                <h2 class="mt-1 text-xl font-bold text-gray-900">
                    Pengaturan Sistem
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Atur identitas perpustakaan, denda, masa pinjam, dan batas jumlah buku.
                </p>
            </div>

            <a href="{{ route('admin.dashboard') }}"
               class="inline-flex items-center justify-center gap-2 rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-600 transition hover:bg-gray-50">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Dashboard
            </a>
        </div>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-emerald-50/40 to-sky-50/40 py-10">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">

            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                    <p class="mb-2 font-bold">Periksa kembali pengaturan:</p>

                    <ul class="list-disc space-y-1 pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form
                method="POST"
                action="{{ route('admin.settings.update') }}"
                class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/90 shadow-[0_20px_60px_rgba(15,23,42,0.08)] backdrop-blur-xl"
            >
                @csrf
                @method('PUT')

                <div class="relative overflow-hidden bg-gradient-to-r from-emerald-700 to-teal-500 p-6 text-white">
                    <div class="absolute -right-16 -top-20 h-52 w-52 rounded-full bg-white/10 blur-2xl"></div>
                    <div class="absolute -left-20 bottom-0 h-48 w-48 rounded-full bg-emerald-200/20 blur-2xl"></div>

                    <div class="relative flex items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/20">
                            <span class="material-symbols-outlined text-[26px]">settings</span>
                        </div>

                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-50">
                                Konfigurasi
                            </p>

                            <h3 class="mt-2 text-2xl font-extrabold">
                                Pengaturan Utama Perpustakaan
                            </h3>

                            <p class="mt-1 text-sm text-emerald-50">
                                Perubahan ini akan langsung digunakan pada fitur peminjaman.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="space-y-6 p-6">
                    <section class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm">
                        <h4 class="font-extrabold text-gray-900">
                            Identitas
                        </h4>

                        <div class="mt-5 grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="block text-sm font-bold text-gray-700">
                                    Nama Sekolah
                                </label>

                                <input
                                    type="text"
                                    name="school_name"
                                    value="{{ old('school_name', $settings['school_name'] ?? 'MTsN 1 Majene') }}"
                                    class="mt-2 block w-full rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                    required
                                >
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700">
                                    Nama Perpustakaan
                                </label>

                                <input
                                    type="text"
                                    name="library_name"
                                    value="{{ old('library_name', $settings['library_name'] ?? 'SIM Perpustakaan') }}"
                                    class="mt-2 block w-full rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                    required
                                >
                            </div>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm">
                        <h4 class="font-extrabold text-gray-900">
                            Peminjaman
                        </h4>

                        <div class="mt-5 grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="block text-sm font-bold text-gray-700">
                                    Masa Pinjam Default
                                </label>

                                <div class="mt-2 flex items-center gap-3">
                                    <input
                                        type="number"
                                        name="loan_duration_days"
                                        min="1"
                                        max="365"
                                        value="{{ old('loan_duration_days', $settings['loan_duration_days'] ?? 7) }}"
                                        class="block w-full rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                        required
                                    >

                                    <span class="text-sm font-bold text-gray-500">
                                        hari
                                    </span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700">
                                    Denda per Hari
                                </label>

                                <div class="mt-2 flex items-center gap-3">
                                    <span class="text-sm font-bold text-gray-500">
                                        Rp
                                    </span>

                                    <input
                                        type="number"
                                        name="fine_per_day"
                                        min="0"
                                        max="1000000"
                                        value="{{ old('fine_per_day', $settings['fine_per_day'] ?? 500) }}"
                                        class="block w-full rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                        required
                                    >
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-emerald-100 bg-emerald-50/50 p-5 shadow-sm">
                        <div class="flex items-start gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                                <span class="material-symbols-outlined">rule_settings</span>
                            </div>

                            <div>
                                <h4 class="font-extrabold text-gray-900">
                                    Batas Jumlah Buku
                                </h4>

                                <p class="mt-1 text-sm text-gray-600">
                                    Bagian ini mengatur batas peminjaman biasa dan peminjaman rombongan kelas.
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 grid gap-4 md:grid-cols-2">
                            <div class="rounded-3xl border border-white bg-white p-5">
                                <label class="block text-sm font-bold text-gray-700">
                                    Maksimal Buku Peminjaman Biasa
                                </label>

                                <input
                                    type="number"
                                    name="max_normal_loan_items"
                                    min="1"
                                    max="200"
                                    value="{{ old('max_normal_loan_items', $settings['max_normal_loan_items'] ?? 3) }}"
                                    class="mt-2 block w-full rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                    required
                                >

                                <p class="mt-2 text-xs font-semibold text-gray-500">
                                    Dipakai pada form peminjaman biasa.
                                </p>
                            </div>

                            <div class="rounded-3xl border border-white bg-white p-5">
                                <label class="block text-sm font-bold text-gray-700">
                                    Maksimal Eksemplar Peminjaman Kelas
                                </label>

                                <input
                                    type="number"
                                    name="max_class_loan_items"
                                    min="1"
                                    max="500"
                                    value="{{ old('max_class_loan_items', $settings['max_class_loan_items'] ?? 40) }}"
                                    class="mt-2 block w-full rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                    required
                                >

                                <p class="mt-2 text-xs font-semibold text-gray-500">
                                    Dipakai saat satu perwakilan siswa meminjam banyak copy untuk kelas.
                                </p>
                            </div>
                        </div>
                    </section>

                    <div class="flex justify-end border-t border-gray-100 pt-6">
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-700/20 transition hover:bg-emerald-800"
                        >
                            <span class="material-symbols-outlined text-[18px]">save</span>
                            Simpan Pengaturan
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>