<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
                    Buku Tamu Digital
                </p>
                <h2 class="mt-1 text-xl font-bold text-gray-900">
                    Edit Kunjungan
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Perbarui data kunjungan yang sudah tercatat.
                </p>
            </div>

            <a href="{{ route('visits.show', $visit) }}"
               class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-white px-5 py-3 text-sm font-semibold text-emerald-700 shadow-sm transition hover:bg-emerald-50">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-emerald-50/40 to-sky-50/40 py-10">
        <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
            @include('pustakawan.visits.partials.form', [
                'formAction' => route('visits.update', $visit),
            ])
        </div>
    </div>
</x-app-layout>
