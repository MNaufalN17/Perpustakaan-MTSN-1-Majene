<x-app-layout>
    @php
    $loan->loadMissing([
    'member.studentClass',
    'loanItems.bookItem.book',
    ]);

    $loanCode = $loan->loan_code ?? ('TRX-' . $loan->id);

    $loanDateText = $loan->loan_date
    ? \Carbon\Carbon::parse($loan->loan_date)->format('d M Y')
    : '-';

    $dueDateText = $loan->due_date
    ? \Carbon\Carbon::parse($loan->due_date)->format('d M Y')
    : '-';

    $todayReturnDate = now()->format('Y-m-d');
    $todayReturnDateText = now()->format('d M Y');

    $statusText = $loan->status ?? '-';

    $statusBadgeClass = match ($loan->status) {
    'aktif' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
    'terlambat' => 'border-red-200 bg-red-50 text-red-700',
    'selesai' => 'border-sky-200 bg-sky-50 text-sky-700',
    default => 'border-gray-200 bg-gray-50 text-gray-600',
    };

    $activeLoanItems = $loan->loanItems->filter(function ($loanItem) {
    return in_array($loanItem->status, ['dipinjam', 'terlambat'], true);
    });

    $isProcessable = in_array($loan->status, ['aktif', 'terlambat'], true) && $activeLoanItems->count() > 0;

    $totalFine = $loan->loanItems->sum(function ($loanItem) {
    return (int) ($loanItem->fine_amount ?? 0);
    });

    $oldSelectedLoanItemIds = collect(old('loan_item_ids', []))
    ->map(fn ($id) => (int) $id)
    ->values();
    @endphp

    <style>
        @media print {
            body {
                background: #ffffff !important;
            }

            nav,
            header,
            .no-print {
                display: none !important;
            }

            .print-area {
                box-shadow: none !important;
                border: 1px solid #e5e7eb !important;
            }
        }
    </style>

    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
                    Peminjaman & Pengembalian
                </p>

                <h2 class="mt-1 text-xl font-bold text-gray-900">
                    Detail Transaksi
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Lihat detail transaksi dan proses pengembalian buku.
                </p>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row">
                <button
                    type="button"
                    onclick="window.print()"
                    class="no-print inline-flex items-center justify-center gap-2 rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-600 transition hover:bg-gray-50">
                    <span class="material-symbols-outlined text-[18px]">print</span>
                    Cetak
                </button>

                <a href="{{ route('loans.index') }}"
                    class="no-print inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-white px-5 py-3 text-sm font-bold text-emerald-700 transition hover:bg-emerald-50">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                    Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-emerald-50/40 to-sky-50/40 py-10">
        <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">

            <div class="print-area overflow-hidden rounded-[2rem] border border-white/70 bg-white shadow-[0_18px_50px_rgba(15,23,42,0.06)]">
                <div class="relative overflow-hidden bg-gradient-to-r from-emerald-700 to-teal-500 p-7 text-white">
                    <div class="absolute -right-16 -top-20 h-52 w-52 rounded-full bg-white/10 blur-2xl"></div>
                    <div class="absolute -left-20 bottom-0 h-48 w-48 rounded-full bg-emerald-200/20 blur-2xl"></div>

                    <div class="relative flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
                        <div class="flex items-center gap-4">
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/20">
                                <span class="material-symbols-outlined text-[30px]">local_library</span>
                            </div>

                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-50">
                                    Perpustakaan MTS Negeri 1 Majene
                                </p>

                                <h3 class="mt-2 text-2xl font-extrabold">
                                    MTs Negeri 1 Majene
                                </h3>

                                <p class="mt-1 text-sm text-emerald-50">
                                    Struk transaksi peminjaman dan pengembalian buku.
                                </p>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-white/20 bg-white/15 px-5 py-4 text-right">
                            <p class="text-xs text-emerald-50">
                                Kode Transaksi
                            </p>

                            <p class="mt-1 font-mono text-xl font-extrabold">
                                {{ $loanCode }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="p-7">
                    <div class="grid gap-5 md:grid-cols-2">
                        <div class="rounded-3xl border border-emerald-100 bg-emerald-50/60 p-5">
                            <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-emerald-700">
                                Data Peminjam
                            </p>

                            <h4 class="mt-4 text-lg font-extrabold text-gray-900">
                                {{ $loan->member->name ?? '-' }}
                            </h4>

                            <p class="mt-2 text-sm text-gray-600">
                                NIS/NIP:
                                <span class="font-bold">
                                    {{ $loan->member->nis_nip ?? $loan->member->member_code ?? '-' }}
                                </span>
                            </p>

                            <p class="mt-1 text-sm text-gray-600">
                                Kelas/Jenis:
                                <span class="font-bold">
                                    {{ $loan->member->studentClass->class_name ?? $loan->member->type ?? 'Guru/Staff' }}
                                </span>
                            </p>
                        </div>

                        <div class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm">
                            <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-gray-500">
                                Status Transaksi
                            </p>

                            <div class="mt-4">
                                <span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-bold {{ $statusBadgeClass }}">
                                    <span class="material-symbols-outlined text-[15px]">
                                        {{ $loan->status === 'selesai' ? 'check_circle' : ($loan->status === 'terlambat' ? 'warning' : 'sync') }}
                                    </span>
                                    {{ ucfirst($statusText) }}
                                </span>
                            </div>

                            <div class="mt-5 grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400">
                                        Tanggal Pinjam
                                    </p>

                                    <p class="mt-2 text-sm font-extrabold text-gray-900">
                                        {{ $loanDateText }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400">
                                        Batas Kembali
                                    </p>

                                    <p class="mt-2 text-sm font-extrabold {{ $loan->status === 'terlambat' ? 'text-red-700' : 'text-emerald-700' }}">
                                        {{ $dueDateText }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($isProcessable)
                    <div class="no-print mt-7 rounded-3xl border border-emerald-100 bg-white p-6 shadow-sm">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <h3 class="text-lg font-extrabold text-gray-900">
                                    Proses Pengembalian
                                </h3>

                                <p class="mt-1 text-sm text-gray-500">
                                    Pilih buku yang dikembalikan dengan checkbox. Tanggal pengembalian otomatis memakai tanggal hari ini.
                                </p>
                            </div>

                            <div class="flex flex-col gap-2 sm:items-end">
                                <span class="inline-flex w-fit rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">
                                    {{ $activeLoanItems->count() }} item aktif
                                </span>

                                <span class="inline-flex w-fit rounded-full border border-sky-200 bg-sky-50 px-3 py-1.5 text-xs font-bold text-sky-700">
                                    Kembali: {{ $todayReturnDateText }}
                                </span>
                            </div>
                        </div>

                        @if($errors->any())
                        <div class="mt-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3">
                            <p class="text-sm font-bold text-red-700">
                                Periksa kembali data pengembalian.
                            </p>

                            <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-700">
                                @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        <form
                            method="POST"
                            action="{{ route('loans.update', $loan) }}"
                            class="mt-6 space-y-5"
                            onsubmit="return confirm('Proses pengembalian untuk transaksi {{ $loanCode }}?')">
                            @csrf
                            @method('PUT')

                            <input type="hidden" name="return_date" value="{{ $todayReturnDate }}">

                            <div class="rounded-2xl border border-sky-100 bg-sky-50 px-4 py-3">
                                <p class="text-sm font-bold text-sky-700">
                                    Tanggal kembali: {{ $todayReturnDateText }}
                                </p>

                                <p class="mt-1 text-xs text-sky-700">
                                    Tanggal ini dipakai otomatis saat proses pengembalian.
                                </p>
                            </div>

                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <p class="text-sm font-bold text-gray-700">
                                    Pilih buku yang dikembalikan
                                </p>

                                <div class="flex gap-2">
                                    <button
                                        type="button"
                                        onclick="document.querySelectorAll('.return-item-checkbox').forEach(function (checkbox) { checkbox.checked = true; });"
                                        class="inline-flex items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-xs font-bold text-emerald-700 transition hover:bg-emerald-100">
                                        Pilih Semua
                                    </button>

                                    <button
                                        type="button"
                                        onclick="document.querySelectorAll('.return-item-checkbox').forEach(function (checkbox) { checkbox.checked = false; });"
                                        class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-xs font-bold text-gray-600 transition hover:bg-gray-50">
                                        Kosongkan
                                    </button>
                                </div>
                            </div>

                            <div class="space-y-3">
                                @foreach($activeLoanItems as $loanItem)
                                @php
                                $bookItem = $loanItem->bookItem;
                                $book = $bookItem?->book;
                                $defaultCondition = old('return_conditions.' . $loanItem->id, $bookItem->condition ?? 'baik');
                                $isChecked = $oldSelectedLoanItemIds->contains((int) $loanItem->id);
                                @endphp

                                <div class="rounded-3xl border border-gray-100 bg-slate-50 p-4">
                                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                                        <label class="flex cursor-pointer items-start gap-3">
                                            <input
                                                type="checkbox"
                                                name="loan_item_ids[]"
                                                value="{{ $loanItem->id }}"
                                                @checked($isChecked)
                                                class="return-item-checkbox mt-1 rounded border-gray-300 text-emerald-700 focus:ring-emerald-500">

                                            <span>
                                                <span class="block font-bold text-gray-900">
                                                    {{ $book->title ?? '-' }}
                                                </span>

                                                <span class="mt-1 block text-xs text-gray-500">
                                                    Kode eksemplar:
                                                    <span class="font-mono font-bold">
                                                        {{ $bookItem->item_code ?? '-' }}
                                                    </span>
                                                    —
                                                    Status:
                                                    <span class="font-bold">
                                                        {{ ucfirst($loanItem->status ?? '-') }}
                                                    </span>
                                                </span>
                                            </span>
                                        </label>

                                        <div class="w-full md:w-56">
                                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500">
                                                Kondisi Saat Kembali
                                            </label>

                                            <select
                                                name="return_conditions[{{ $loanItem->id }}]"
                                                class="mt-2 block w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                                <option value="baik" @selected($defaultCondition==='baik' )>Baik</option>
                                                <option value="rusak ringan" @selected($defaultCondition==='rusak ringan' )>Rusak Ringan</option>
                                                <option value="rusak berat" @selected($defaultCondition==='rusak berat' )>Rusak Berat</option>
                                                <option value="hilang" @selected($defaultCondition==='hilang' )>Hilang</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            <div>
                                <label for="notes" class="block text-sm font-bold text-gray-700">
                                    Catatan Pengembalian
                                    <span class="text-xs font-semibold text-gray-400">(opsional)</span>
                                </label>

                                <textarea
                                    id="notes"
                                    name="notes"
                                    rows="3"
                                    class="mt-2 block w-full rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                    placeholder="Opsional. Contoh: Buku dikembalikan lengkap dan dalam kondisi baik.">{{ old('notes') }}</textarea>
                            </div>

                            <div class="flex flex-col-reverse gap-3 border-t border-gray-100 pt-5 sm:flex-row sm:justify-end">
                                <a href="{{ route('loans.index') }}"
                                    class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-600 transition hover:bg-gray-50">
                                    Batal
                                </a>

                                <button
                                    type="submit"
                                    class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-700/20 transition hover:bg-emerald-800">
                                    <span class="material-symbols-outlined text-[18px]">task_alt</span>
                                    Proses Pengembalian
                                </button>
                            </div>
                        </form>
                    </div>
                    @else
                    <div class="no-print mt-7 rounded-3xl border border-sky-100 bg-sky-50 p-5">
                        <p class="inline-flex items-center gap-2 text-sm font-bold text-sky-700">
                            <span class="material-symbols-outlined text-[18px]">info</span>
                            Transaksi ini tidak memiliki item aktif untuk diproses.
                        </p>

                        <p class="mt-1 text-sm text-sky-700">
                            Jika status transaksi sudah selesai, data hanya dapat dilihat dan dicetak sebagai riwayat.
                        </p>
                    </div>
                    @endif

                    <div class="mt-7 rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700">
                                <span class="material-symbols-outlined">format_list_bulleted</span>
                            </div>

                            <div>
                                <h3 class="font-extrabold text-gray-900">
                                    Daftar Buku
                                </h3>

                                <p class="text-sm text-gray-500">
                                    Buku yang tercatat pada transaksi ini.
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 overflow-x-auto rounded-2xl border border-gray-100">
                            <table class="w-full min-w-[850px] divide-y divide-gray-100 text-left text-sm">
                                <thead class="bg-emerald-50 text-xs uppercase tracking-wider text-emerald-800">
                                    <tr>
                                        <th class="px-4 py-3 font-bold">No</th>
                                        <th class="px-4 py-3 font-bold">Judul Buku</th>
                                        <th class="px-4 py-3 font-bold">Kode Eksemplar</th>
                                        <th class="px-4 py-3 text-center font-bold">Kondisi</th>
                                        <th class="px-4 py-3 text-center font-bold">Status</th>
                                        <th class="px-4 py-3 text-right font-bold">Denda</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @forelse($loan->loanItems as $loanItem)
                                    @php
                                    $bookItem = $loanItem->bookItem;
                                    $book = $bookItem?->book;

                                    $itemStatusClass = match ($loanItem->status) {
                                    'dipinjam' => 'border-amber-200 bg-amber-50 text-amber-700',
                                    'terlambat' => 'border-red-200 bg-red-50 text-red-700',
                                    'dikembalikan' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                    default => 'border-gray-200 bg-gray-50 text-gray-600',
                                    };
                                    @endphp

                                    <tr>
                                        <td class="px-4 py-4 font-bold text-gray-700">
                                            {{ $loop->iteration }}
                                        </td>

                                        <td class="px-4 py-4">
                                            <p class="font-bold text-gray-900">
                                                {{ $book->title ?? '-' }}
                                            </p>

                                            <p class="mt-1 text-xs text-gray-500">
                                                {{ $book->author ?? '-' }}
                                            </p>
                                        </td>

                                        <td class="px-4 py-4">
                                            <span class="font-mono text-xs font-bold text-gray-700">
                                                {{ $bookItem->item_code ?? '-' }}
                                            </span>
                                        </td>

                                        <td class="px-4 py-4 text-center">
                                            <span class="inline-flex rounded-full border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs font-bold text-gray-700">
                                                {{ ucwords($loanItem->return_condition ?? $bookItem->condition ?? '-') }}
                                            </span>
                                        </td>

                                        <td class="px-4 py-4 text-center">
                                            <span class="inline-flex rounded-full border px-3 py-1.5 text-xs font-bold {{ $itemStatusClass }}">
                                                {{ ucfirst($loanItem->status ?? '-') }}
                                            </span>
                                        </td>

                                        <td class="px-4 py-4 text-right font-bold text-gray-900">
                                            Rp {{ number_format((int) ($loanItem->fine_amount ?? 0), 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">
                                            Tidak ada buku pada transaksi ini.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>

                                <tfoot class="bg-slate-50">
                                    <tr>
                                        <td colspan="5" class="px-4 py-4 text-right text-sm font-extrabold text-gray-700">
                                            Total Denda
                                        </td>

                                        <td class="px-4 py-4 text-right text-sm font-extrabold text-gray-900">
                                            Rp {{ number_format($totalFine, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="mt-7 rounded-3xl border border-gray-100 bg-slate-50 p-5">
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-500">
                            Catatan
                        </p>

                        <p class="mt-2 text-sm text-gray-600">
                            {{ $loan->notes ?? 'Tidak ada catatan transaksi.' }}
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>