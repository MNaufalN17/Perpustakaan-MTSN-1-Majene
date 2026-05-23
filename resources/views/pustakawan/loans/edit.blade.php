<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
                    Sirkulasi
                </p>
                <h2 class="mt-1 text-xl font-bold text-gray-900">
                    Proses Pengembalian Buku
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Pengembalian harus diproses dari halaman ini agar transaksi dan stok buku tetap sesuai.
                </p>
            </div>

            <a href="{{ route('loans.index') }}"
               class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-white px-5 py-3 text-sm font-semibold text-emerald-700 shadow-sm hover:bg-emerald-50">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-emerald-50/40 to-sky-50/40 py-10">
        <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">

            @if ($errors->any())
                <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-red-700 shadow-sm">
                    <p class="text-sm font-bold">Pengembalian belum bisa diproses</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('error_title') || session('error_message') || session('error'))
                <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-red-800 shadow-sm">
                    <p class="text-sm font-bold">
                        {{ session('error_title', 'Pengembalian belum bisa diproses') }}
                    </p>
                    <p class="mt-1 text-sm">
                        {{ session('error_message', session('error')) }}
                    </p>
                    @if(session('error_detail'))
                        <p class="mt-1 text-xs text-red-700">
                            {{ session('error_detail') }}
                        </p>
                    @endif
                </div>
            @endif

            @php
                $isOverdue = \Carbon\Carbon::now()->gt(\Carbon\Carbon::parse($loan->due_date)) && in_array($loan->status, ['aktif', 'terlambat']);
                $itemsForReturn = isset($activeItems) ? $activeItems : $loan->loanItems->whereIn('status', ['dipinjam', 'terlambat']);
            @endphp

            <div class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/80 shadow-[0_20px_60px_rgba(15,23,42,0.08)] backdrop-blur-xl">
                <div class="bg-gradient-to-r from-emerald-700 to-teal-500 p-6 text-white">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-lg font-bold">
                                Nota: {{ $loan->loan_code }}
                            </h3>
                            <p class="mt-1 text-sm text-emerald-50">
                                Peminjam: {{ $loan->member->name ?? '-' }}
                            </p>
                        </div>

                        @if($isOverdue)
                            <span class="inline-flex w-fit items-center gap-2 rounded-full border border-red-200 bg-red-50 px-4 py-2 text-xs font-bold text-red-700">
                                <span class="material-symbols-outlined text-[16px]">warning</span>
                                Terlambat
                            </span>
                        @else
                            <span class="inline-flex w-fit items-center gap-2 rounded-full border border-white/20 bg-white/15 px-4 py-2 text-xs font-bold text-white">
                                Aktif
                            </span>
                        @endif
                    </div>
                </div>

                <div class="p-6 md:p-8">
                    @if($itemsForReturn->isEmpty())
                        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-amber-800">
                            <p class="text-sm font-bold">Tidak ada buku yang perlu dikembalikan.</p>
                            <p class="mt-1 text-sm">
                                Semua item pada transaksi ini sudah selesai diproses.
                            </p>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <a href="{{ route('loans.index') }}"
                               class="inline-flex items-center justify-center rounded-2xl bg-emerald-700 px-6 py-3 text-sm font-bold text-white hover:bg-emerald-800">
                                Kembali ke Daftar Peminjaman
                            </a>
                        </div>
                    @else
                        <form method="POST" action="{{ route('loans.update', $loan) }}" class="space-y-8">
                            @csrf
                            @method('PUT')

                            <section class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm md:p-6">
                                <h4 class="font-bold text-gray-900">
                                    Daftar Buku yang Dikembalikan
                                </h4>
                                <p class="mt-1 text-sm text-gray-500">
                                    Pilih kondisi akhir setiap buku. Status stok akan diperbarui otomatis setelah pengembalian diproses.
                                </p>

                                <div class="mt-5 space-y-3">
                                    @foreach($itemsForReturn as $index => $item)
                                        <div class="rounded-2xl border border-gray-100 bg-slate-50 p-4">
                                            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                                                <div class="flex items-start gap-3">
                                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-sm font-bold text-emerald-700">
                                                        {{ $index + 1 }}
                                                    </div>

                                                    <div>
                                                        <p class="text-sm font-bold text-gray-900">
                                                            {{ $item->bookItem->book->title ?? '-' }}
                                                        </p>
                                                        <p class="mt-1 text-xs text-gray-500">
                                                            Kode eksemplar:
                                                            <span class="font-mono font-bold">
                                                                {{ $item->bookItem->item_code ?? '-' }}
                                                            </span>
                                                        </p>
                                                        <p class="mt-1 text-xs text-gray-500">
                                                            Kondisi sebelum kembali:
                                                            <span class="font-semibold capitalize">
                                                                {{ $item->bookItem->condition ?? '-' }}
                                                            </span>
                                                        </p>
                                                    </div>
                                                </div>

                                                <div class="w-full md:w-64">
                                                    <label class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                                        Kondisi Saat Dikembalikan
                                                    </label>

                                                    <select
                                                        name="items[{{ $item->id }}][return_condition]"
                                                        required
                                                        class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                                                    >
                                                        <option value="baik" {{ old("items.{$item->id}.return_condition", $item->bookItem->condition) === 'baik' ? 'selected' : '' }}>
                                                            Kembali Baik
                                                        </option>
                                                        <option value="rusak ringan" {{ old("items.{$item->id}.return_condition", $item->bookItem->condition) === 'rusak ringan' ? 'selected' : '' }}>
                                                            Rusak Ringan
                                                        </option>
                                                        <option value="rusak berat" {{ old("items.{$item->id}.return_condition", $item->bookItem->condition) === 'rusak berat' ? 'selected' : '' }}>
                                                            Rusak Berat
                                                        </option>
                                                        <option value="hilang" {{ old("items.{$item->id}.return_condition", $item->bookItem->condition) === 'hilang' ? 'selected' : '' }}>
                                                            Hilang
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </section>

                            @if($isOverdue)
                                <div class="rounded-2xl border border-orange-200 bg-orange-50 p-4 text-orange-800">
                                    <div class="flex items-start gap-3">
                                        <span class="material-symbols-outlined text-[20px]">payments</span>
                                        <div>
                                            <p class="text-sm font-bold">Informasi Denda</p>
                                            <p class="mt-1 text-sm">
                                                Transaksi melewati batas kembali {{ \Carbon\Carbon::parse($loan->due_date)->format('d M Y') }}.
                                                Sistem akan menghitung denda sesuai jumlah hari keterlambatan.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="flex flex-col-reverse gap-3 border-t border-gray-100 pt-6 sm:flex-row sm:justify-end">
                                <a href="{{ route('loans.index') }}"
                                   class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-6 py-3 text-sm font-bold text-gray-600 hover:bg-gray-50">
                                    Batal
                                </a>

                                <button
                                    type="submit"
                                    class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-6 py-3 text-sm font-bold text-white hover:bg-emerald-800"
                                >
                                    <span>Selesaikan Pengembalian</span>
                                    <span class="material-symbols-outlined text-[18px]">task_alt</span>
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>