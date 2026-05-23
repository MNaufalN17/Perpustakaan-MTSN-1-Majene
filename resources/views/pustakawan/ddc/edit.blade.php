<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
                    Master Data
                </p>
                <h2 class="mt-1 text-xl font-bold text-gray-900">
                    Edit DDC
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Perbarui kode, nama klasifikasi, dan deskripsi DDC.
                </p>
            </div>

            <a href="{{ route('ddc.index') }}"
               class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-white px-5 py-3 text-sm font-semibold text-emerald-700 shadow-sm transition hover:bg-emerald-50">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-emerald-50/30 to-sky-50/30 py-10">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">

            @if ($errors->any())
                <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-red-700 shadow-sm">
                    <p class="text-sm font-bold">Data DDC belum bisa disimpan</p>

                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/80 shadow-[0_18px_50px_rgba(15,23,42,0.06)] backdrop-blur-xl">
                <div class="relative overflow-hidden bg-gradient-to-r from-emerald-700 to-teal-500 p-6 text-white">
                    <div class="absolute -right-16 -top-20 h-52 w-52 rounded-full bg-white/10 blur-2xl"></div>
                    <div class="absolute -left-20 bottom-0 h-48 w-48 rounded-full bg-emerald-200/20 blur-2xl"></div>

                    <div class="relative flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/20 text-white shadow-sm">
                            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">
                                edit_note
                            </span>
                        </div>

                        <div>
                            <h3 class="text-lg font-bold">
                                Form Edit DDC
                            </h3>
                            <p class="mt-1 text-sm text-emerald-50">
                                Saat ini mengedit DDC: {{ $ddcClass->code }} - {{ $ddcClass->name }}
                            </p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('ddc.update', $ddcClass->id) }}" class="space-y-6 p-6 md:p-8">
                    @csrf
                    @method('PUT')

                    <section class="rounded-3xl border border-emerald-100 bg-emerald-50/50 p-5 md:p-6">
                        <div class="mb-5 flex items-start gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                                <span class="material-symbols-outlined text-[20px]">category</span>
                            </div>

                            <div>
                                <h4 class="font-bold text-gray-900">
                                    Informasi DDC
                                </h4>
                                <p class="mt-1 text-sm text-gray-500">
                                    Pastikan kode DDC tidak sama dengan data DDC lain.
                                </p>
                            </div>
                        </div>

                        <div class="space-y-5">
                            <div>
                                <label for="code" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                    Kode DDC <span class="text-red-500">*</span>
                                </label>

                                <input
                                    id="code"
                                    name="code"
                                    type="text"
                                    value="{{ old('code', $ddcClass->code) }}"
                                    required
                                    class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 font-mono text-sm font-bold text-gray-900 shadow-sm transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                                >

                                @error('code')
                                    <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="name" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                    Nama Klasifikasi <span class="text-red-500">*</span>
                                </label>

                                <input
                                    id="name"
                                    name="name"
                                    type="text"
                                    value="{{ old('name', $ddcClass->name) }}"
                                    required
                                    class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                                >

                                @error('name')
                                    <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="description" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                    Deskripsi
                                </label>

                                <textarea
                                    id="description"
                                    name="description"
                                    rows="4"
                                    class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                                >{{ old('description', $ddcClass->description) }}</textarea>

                                @error('description')
                                    <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </section>

                    <div class="flex flex-col-reverse gap-3 border-t border-gray-100 pt-6 sm:flex-row sm:justify-end">
                        <a href="{{ route('ddc.index') }}"
                           class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-6 py-3 text-sm font-bold text-gray-600 transition hover:bg-gray-50">
                            Batal
                        </a>

                        <button
                            type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-700/20 transition hover:bg-emerald-800"
                        >
                            <span>Simpan Perubahan</span>
                            <span class="material-symbols-outlined text-[18px]">save</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>