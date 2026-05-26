<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
                    Staff IT Admin
                </p>
                <h2 class="mt-1 text-xl font-bold text-gray-900">
                    Pengaturan Sistem
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Atur identitas perpustakaan, aturan peminjaman, dan nominal denda.
                </p>
            </div>

            <a href="{{ route('admin.dashboard') }}"
               class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-white px-5 py-3 text-sm font-semibold text-emerald-700 shadow-sm transition hover:bg-emerald-50">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Dashboard Admin
            </a>
        </div>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-emerald-50/40 to-sky-50/40 py-10">
        <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">

            @if(session('success') || session('success_title') || session('success_message'))
                <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800 shadow-sm">
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                            <span class="material-symbols-outlined text-[20px]">check_circle</span>
                        </div>

                        <div>
                            <p class="text-sm font-bold">
                                {{ session('success_title', 'Berhasil') }}
                            </p>

                            <p class="mt-1 text-sm">
                                {{ session('success_message', session('success')) }}
                            </p>

                            @if(session('success_detail'))
                                <p class="mt-1 text-xs text-emerald-700">
                                    {{ session('success_detail') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-red-700 shadow-sm">
                    <p class="text-sm font-bold">
                        Pengaturan belum bisa disimpan
                    </p>

                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/90 shadow-[0_20px_60px_rgba(15,23,42,0.08)] backdrop-blur-xl">
                    <div class="bg-gradient-to-r from-emerald-700 to-teal-500 p-6 text-white">
                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/20">
                                <span class="material-symbols-outlined">account_balance</span>
                            </div>

                            <div>
                                <h3 class="text-lg font-bold">
                                    Identitas Sekolah dan Perpustakaan
                                </h3>
                                <p class="mt-1 text-sm text-emerald-50">
                                    Data ini dapat digunakan pada struk, laporan, dan tampilan sistem.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-5 p-6 md:grid-cols-2">
                        @foreach($identitySettings as $setting)
                            <div class="{{ $setting->type === 'textarea' ? 'md:col-span-2' : '' }}">
                                <label class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                    {{ $setting->label }}
                                </label>

                                @if($setting->type === 'textarea')
                                    <textarea
                                        name="settings[{{ $setting->key }}]"
                                        rows="4"
                                        class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                                    >{{ old('settings.' . $setting->key, $setting->value) }}</textarea>
                                @else
                                    <input
                                        type="text"
                                        name="settings[{{ $setting->key }}]"
                                        value="{{ old('settings.' . $setting->key, $setting->value) }}"
                                        class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                                    >
                                @endif

                                @if($setting->description)
                                    <p class="mt-2 text-xs text-gray-500">
                                        {{ $setting->description }}
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/90 shadow-[0_20px_60px_rgba(15,23,42,0.08)] backdrop-blur-xl">
                    <div class="bg-gradient-to-r from-emerald-700 to-teal-500 p-6 text-white">
                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/20">
                                <span class="material-symbols-outlined">settings</span>
                            </div>

                            <div>
                                <h3 class="text-lg font-bold">
                                    Aturan Sirkulasi
                                </h3>
                                <p class="mt-1 text-sm text-emerald-50">
                                    Atur batas peminjaman dan nominal denda keterlambatan.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-5 p-6 md:grid-cols-3">
                        @foreach($circulationSettings as $setting)
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                    {{ $setting->label }}
                                </label>

                                <input
                                    type="number"
                                    name="settings[{{ $setting->key }}]"
                                    value="{{ old('settings.' . $setting->key, $setting->value) }}"
                                    min="0"
                                    class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm font-bold text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                                >

                                @if($setting->description)
                                    <p class="mt-2 text-xs text-gray-500">
                                        {{ $setting->description }}
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">
                    <a href="{{ route('admin.dashboard') }}"
                       class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-6 py-3 text-sm font-bold text-gray-600 transition hover:bg-gray-50">
                        Batal
                    </a>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-700/20 transition hover:bg-emerald-800"
                    >
                        <span>Simpan Pengaturan</span>
                        <span class="material-symbols-outlined text-[18px]">save</span>
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>