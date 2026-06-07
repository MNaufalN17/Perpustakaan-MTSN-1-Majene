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
                    Detail Transaksi
                </p>

                <h2 class="mt-1 text-xl font-bold text-gray-900">
                    Proses Peminjaman / Pengembalian
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Lihat detail transaksi, catatan, status buku, denda, dan proses pengembalian.
                </p>
            </div>

            <a href="{{ route('loans.index') }}"
               class="inline-flex items-center justify-center gap-2 rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-700 shadow-sm transition hover:bg-gray-50">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Kembali
            </a>
        </div>
    </x-slot>

    @php
        $loanCode = $loan->loan_code ?? ('TRX-' . $loan->id);

        $isClassLoan = ($loan->loan_type ?? 'regular') === 'class_bulk';

        $loanTypeLabel = $isClassLoan
            ? 'Perwakilan Kelas'
            : 'Peminjaman Biasa';

        $loanTypeBadgeClass = $isClassLoan
            ? 'border-amber-300 bg-amber-100 text-amber-800'
            : 'border-emerald-200 bg-emerald-50 text-emerald-700';

        $statusLabel = ucfirst($loan->status ?? '-');

        $statusBadgeClass = match ($loan->status) {
            'aktif' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'terlambat' => 'border-red-200 bg-red-50 text-red-700',
            'selesai' => 'border-sky-200 bg-sky-50 text-sky-700',
            default => 'border-gray-200 bg-gray-50 text-gray-600',
        };

        $loanDateText = $loan->loan_date
            ? \Carbon\Carbon::parse($loan->loan_date)->format('d/m/Y')
            : '-';

        $dueDateText = $loan->due_date
            ? \Carbon\Carbon::parse($loan->due_date)->format('d/m/Y')
            : '-';

        $returnDateText = $loan->return_date
            ? \Carbon\Carbon::parse($loan->return_date)->format('d/m/Y')
            : '-';

        $todayInput = now()->format('Y-m-d');

        $activeLoanItems = $loan->loanItems
            ->filter(fn ($loanItem) => in_array($loanItem->status, ['dipinjam', 'terlambat'], true))
            ->values();

        $returnedLoanItems = $loan->loanItems
            ->filter(fn ($loanItem) => ! in_array($loanItem->status, ['dipinjam', 'terlambat'], true))
            ->values();

        $canReturn = in_array($loan->status, ['aktif', 'terlambat'], true) && $activeLoanItems->count() > 0;

        $storedFineAmount = (int) ($loan->stored_fine_amount ?? 0);
        $runningFineAmount = (int) ($loan->running_fine_amount ?? 0);
        $totalFineAmount = (int) ($loan->total_fine_amount ?? 0);
        $currentLateDays = (int) ($loan->current_late_days ?? 0);
    @endphp

    <div
        x-data="returnConfirmation()"
        @keydown.escape.window="closeReturnModal()"
        class="min-h-screen bg-gradient-to-br from-slate-50 via-emerald-50/40 to-sky-50/40 py-10"
    >
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

            @if(session('success_title') || session('success_message'))
                <div class="rounded-3xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-800 shadow-sm">
                    <p class="font-extrabold">
                        {{ session('success_title', 'Berhasil') }}
                    </p>

                    <p class="mt-1 text-sm">
                        {{ session('success_message') }}
                    </p>

                    @if(session('success_detail'))
                        <p class="mt-1 text-xs text-emerald-700">
                            {{ session('success_detail') }}
                        </p>
                    @endif
                </div>
            @endif

            @if(session('error_title') || session('error_message'))
                <div class="rounded-3xl border border-red-200 bg-red-50 px-5 py-4 text-red-800 shadow-sm">
                    <p class="font-extrabold">
                        {{ session('error_title', 'Terjadi Kesalahan') }}
                    </p>

                    <p class="mt-1 text-sm">
                        {{ session('error_message') }}
                    </p>

                    @if(session('error_detail'))
                        <p class="mt-1 text-xs text-red-700">
                            {{ session('error_detail') }}
                        </p>
                    @endif
                </div>
            @endif

            @if($errors->any())
                <div class="rounded-3xl border border-red-200 bg-red-50 px-5 py-4 text-red-800 shadow-sm">
                    <p class="font-extrabold">
                        Validasi gagal
                    </p>

                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/90 shadow-[0_18px_50px_rgba(15,23,42,0.06)] backdrop-blur-xl">
                <div class="relative overflow-hidden bg-gradient-to-r from-emerald-700 to-teal-500 p-6">
                    <div class="absolute -right-16 -top-20 h-52 w-52 rounded-full bg-white/10 blur-2xl"></div>
                    <div class="absolute -left-20 bottom-0 h-48 w-48 rounded-full bg-emerald-200/20 blur-2xl"></div>

                    <div class="relative flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <p class="text-sm font-semibold text-emerald-50">
                                Kode Transaksi
                            </p>

                            <h3 class="mt-1 font-mono text-3xl font-extrabold text-white">
                                {{ $loanCode }}
                            </h3>

                            <div class="mt-4 flex flex-wrap gap-2">
                                <span class="inline-flex rounded-full border px-3 py-1 text-xs font-bold {{ $loanTypeBadgeClass }}">
                                    {{ $loanTypeLabel }}
                                </span>

                                <span class="inline-flex rounded-full border px-3 py-1 text-xs font-bold {{ $statusBadgeClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </div>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-3">
                            <div class="rounded-2xl border border-white/20 bg-white/15 px-4 py-3 text-white">
                                <p class="text-xs text-emerald-50">Tanggal Pinjam</p>
                                <p class="mt-1 text-sm font-extrabold">{{ $loanDateText }}</p>
                            </div>

                            <div class="rounded-2xl border border-white/20 bg-white/15 px-4 py-3 text-white">
                                <p class="text-xs text-emerald-50">Batas Kembali</p>
                                <p class="mt-1 text-sm font-extrabold">{{ $dueDateText }}</p>
                            </div>

                            <div class="rounded-2xl border border-white/20 bg-white/15 px-4 py-3 text-white">
                                <p class="text-xs text-emerald-50">Tanggal Selesai</p>
                                <p class="mt-1 text-sm font-extrabold">{{ $returnDateText }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid gap-6 p-6 lg:grid-cols-3">
                    <div class="rounded-3xl border border-gray-100 bg-slate-50 p-5">
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-500">
                            Peminjam
                        </p>

                        <p class="mt-3 text-lg font-extrabold text-gray-900">
                            {{ $loan->member?->name ?? '-' }}
                        </p>

                        <p class="mt-1 text-sm text-gray-500">
                            {{ $loan->member?->nis_nip ?? $loan->member?->member_code ?? '-' }}
                        </p>

                        <p class="mt-1 text-sm text-gray-500">
                            {{ $loan->member?->studentClass?->class_name ?? 'Guru/Staff' }}
                        </p>
                    </div>

                    <div class="rounded-3xl border border-gray-100 bg-slate-50 p-5">
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-500">
                            Kelas Transaksi
                        </p>

                        <p class="mt-3 text-lg font-extrabold text-gray-900">
                            {{ $loan->studentClass?->class_name ?? $loan->member?->studentClass?->class_name ?? '-' }}
                        </p>

                        <p class="mt-1 text-sm text-gray-500">
                            {{ $isClassLoan ? 'Digunakan untuk peminjaman kelas/perwakilan.' : 'Transaksi peminjaman biasa.' }}
                        </p>
                    </div>

                    <div class="rounded-3xl border border-gray-100 bg-slate-50 p-5">
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-500">
                            Ringkasan Buku
                        </p>

                        <p class="mt-3 text-lg font-extrabold text-gray-900">
                            {{ $loan->loanItems->count() }} Eksemplar
                        </p>

                        <p class="mt-1 text-sm text-gray-500">
                            {{ $activeLoanItems->count() }} masih dipinjam,
                            {{ $returnedLoanItems->count() }} sudah diproses.
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-4">
                <div class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-500">
                        Hari Terlambat Aktif
                    </p>

                    <p class="mt-2 text-3xl font-extrabold {{ $currentLateDays > 0 ? 'text-red-700' : 'text-gray-900' }}">
                        {{ $currentLateDays }}
                    </p>
                </div>

                <div class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-500">
                        Denda Tersimpan
                    </p>

                    <p class="mt-2 text-2xl font-extrabold text-gray-900">
                        Rp {{ number_format($storedFineAmount, 0, ',', '.') }}
                    </p>
                </div>

                <div class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-500">
                        Denda Berjalan
                    </p>

                    <p class="mt-2 text-2xl font-extrabold {{ $runningFineAmount > 0 ? 'text-red-700' : 'text-gray-900' }}">
                        Rp {{ number_format($runningFineAmount, 0, ',', '.') }}
                    </p>
                </div>

                <div class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-500">
                        Total Denda
                    </p>

                    <p class="mt-2 text-2xl font-extrabold {{ $totalFineAmount > 0 ? 'text-red-700' : 'text-gray-900' }}">
                        Rp {{ number_format($totalFineAmount, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-[2rem] border border-white/70 bg-white/90 p-6 shadow-sm">
                    <div class="flex items-start gap-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700">
                            <span class="material-symbols-outlined">sticky_note_2</span>
                        </div>

                        <div>
                            <h3 class="text-base font-extrabold text-gray-900">
                                Catatan Transaksi
                            </h3>

                            <p class="mt-1 text-sm leading-6 text-gray-600">
                                {{ $loan->notes ?: 'Tidak ada catatan transaksi.' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="rounded-[2rem] border border-white/70 bg-white/90 p-6 shadow-sm">
                    <div class="flex items-start gap-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-sky-50 text-sky-700">
                            <span class="material-symbols-outlined">assignment_turned_in</span>
                        </div>

                        <div>
                            <h3 class="text-base font-extrabold text-gray-900">
                                Catatan Pengembalian
                            </h3>

                            <p class="mt-1 text-sm leading-6 text-gray-600">
                                {{ $loan->return_notes ?: 'Belum ada catatan pengembalian.' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/90 shadow-sm">
                <div class="border-b border-gray-100 px-6 py-5">
                    <h3 class="text-lg font-extrabold text-gray-900">
                        Daftar Eksemplar
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Semua eksemplar yang masuk dalam transaksi ini.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1000px] divide-y divide-gray-100 text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-wider text-gray-500">
                            <tr>
                                <th class="px-5 py-4 font-bold">Kode Eksemplar</th>
                                <th class="px-5 py-4 font-bold">Judul Buku</th>
                                <th class="px-5 py-4 text-center font-bold">Status</th>
                                <th class="px-5 py-4 text-center font-bold">Kondisi Kembali</th>
                                <th class="px-5 py-4 text-center font-bold">Tgl Kembali</th>
                                <th class="px-5 py-4 text-right font-bold">Denda</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($loan->loanItems as $loanItem)
                                @php
                                    $itemStatus = $loanItem->status ?? '-';

                                    $itemStatusClass = match ($itemStatus) {
                                        'dipinjam' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                        'terlambat' => 'border-red-200 bg-red-50 text-red-700',
                                        'dikembalikan' => 'border-sky-200 bg-sky-50 text-sky-700',
                                        default => 'border-gray-200 bg-gray-50 text-gray-600',
                                    };

                                    $itemReturnDateText = $loanItem->return_date
                                        ? \Carbon\Carbon::parse($loanItem->return_date)->format('d/m/Y')
                                        : '-';
                                @endphp

                                <tr class="hover:bg-emerald-50/30">
                                    <td class="px-5 py-4 align-middle">
                                        <p class="font-mono font-extrabold text-gray-900">
                                            {{ $loanItem->bookItem?->item_code ?? '-' }}
                                        </p>

                                        <p class="mt-1 text-xs text-gray-500">
                                            Kondisi saat ini:
                                            {{ $loanItem->bookItem?->condition ?? '-' }}
                                        </p>
                                    </td>

                                    <td class="px-5 py-4 align-middle">
                                        <p class="font-bold text-gray-900">
                                            {{ $loanItem->bookItem?->book?->title ?? '-' }}
                                        </p>

                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ $loanItem->bookItem?->book?->author ?? '-' }}
                                        </p>
                                    </td>

                                    <td class="px-5 py-4 text-center align-middle">
                                        <span class="inline-flex rounded-full border px-3 py-1 text-xs font-bold {{ $itemStatusClass }}">
                                            {{ ucfirst($itemStatus) }}
                                        </span>
                                    </td>

                                    <td class="px-5 py-4 text-center align-middle">
                                        {{ $loanItem->return_condition ?? '-' }}
                                    </td>

                                    <td class="px-5 py-4 text-center align-middle">
                                        {{ $itemReturnDateText }}
                                    </td>

                                    <td class="px-5 py-4 text-right align-middle font-extrabold text-gray-900">
                                        Rp {{ number_format((int) ($loanItem->fine_amount ?? 0), 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500">
                                        Tidak ada eksemplar pada transaksi ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($canReturn)
                <div class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/90 shadow-sm">
                    <div class="border-b border-gray-100 bg-gradient-to-r from-emerald-700 to-teal-500 px-6 py-5 text-white">
                        <h3 class="text-lg font-extrabold">
                            Proses Pengembalian
                        </h3>

                        <p class="mt-1 text-sm text-emerald-50">
                            Pilih eksemplar yang dikembalikan. Bisa pengembalian sebagian atau semua.
                        </p>
                    </div>

                    <form
                        method="POST"
                        action="{{ route('loans.update', $loan) }}"
                        x-ref="returnForm"
                        @submit.prevent="openReturnModal($event)"
                        class="p-6"
                    >
                        @csrf
                        @method('PUT')

                        <div class="grid gap-5 lg:grid-cols-3">
                            <div>
                                <label for="return_date" class="block text-sm font-bold text-gray-700">
                                    Tanggal Pengembalian
                                </label>

                                <input
                                    id="return_date"
                                    type="date"
                                    name="return_date"
                                    value="{{ old('return_date', $todayInput) }}"
                                    class="mt-2 block w-full rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                    required
                                >
                            </div>

                            <div class="lg:col-span-2">
                                <label for="notes" class="block text-sm font-bold text-gray-700">
                                    Catatan Pengembalian
                                    <span class="text-xs font-semibold text-gray-400">(opsional)</span>
                                </label>

                                <input
                                    id="notes"
                                    type="text"
                                    name="notes"
                                    value="{{ old('notes') }}"
                                    placeholder="Contoh: Dikembalikan oleh ketua kelas / buku rusak ringan / lengkap."
                                    class="mt-2 block w-full rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                >
                            </div>
                        </div>

                        <div class="mt-6 overflow-x-auto rounded-3xl border border-gray-100">
                            <table class="w-full min-w-[950px] divide-y divide-gray-100 text-left text-sm">
                                <thead class="bg-slate-50 text-xs uppercase tracking-wider text-gray-500">
                                    <tr>
                                        <th class="w-[90px] px-5 py-4 text-center font-bold">Pilih</th>
                                        <th class="px-5 py-4 font-bold">Eksemplar</th>
                                        <th class="px-5 py-4 font-bold">Judul Buku</th>
                                        <th class="w-[210px] px-5 py-4 font-bold">Kondisi Saat Kembali</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @foreach($activeLoanItems as $loanItem)
                                        <tr class="hover:bg-emerald-50/30">
                                            <td class="px-5 py-4 text-center align-middle">
                                                <input
                                                    type="checkbox"
                                                    name="loan_item_ids[]"
                                                    value="{{ $loanItem->id }}"
                                                    class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                                    checked
                                                >
                                            </td>

                                            <td class="px-5 py-4 align-middle">
                                                <p class="font-mono font-extrabold text-gray-900">
                                                    {{ $loanItem->bookItem?->item_code ?? '-' }}
                                                </p>

                                                <p class="mt-1 text-xs text-gray-500">
                                                    Status: {{ ucfirst($loanItem->status ?? '-') }}
                                                </p>
                                            </td>

                                            <td class="px-5 py-4 align-middle">
                                                <p class="font-bold text-gray-900">
                                                    {{ $loanItem->bookItem?->book?->title ?? '-' }}
                                                </p>

                                                <p class="mt-1 text-xs text-gray-500">
                                                    {{ $loanItem->bookItem?->book?->author ?? '-' }}
                                                </p>
                                            </td>

                                            <td class="px-5 py-4 align-middle">
                                                <select
                                                    name="return_conditions[{{ $loanItem->id }}]"
                                                    class="block w-full rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                                >
                                                    <option value="baik" @selected(old('return_conditions.' . $loanItem->id, 'baik') === 'baik')>
                                                        Baik
                                                    </option>
                                                    <option value="rusak ringan" @selected(old('return_conditions.' . $loanItem->id) === 'rusak ringan')>
                                                        Rusak Ringan
                                                    </option>
                                                    <option value="rusak berat" @selected(old('return_conditions.' . $loanItem->id) === 'rusak berat')>
                                                        Rusak Berat
                                                    </option>
                                                    <option value="hilang" @selected(old('return_conditions.' . $loanItem->id) === 'hilang')>
                                                        Hilang
                                                    </option>
                                                </select>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6 rounded-3xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
                            <p class="font-extrabold">
                                Catatan penting
                            </p>

                            <p class="mt-1">
                                Jika kondisi kembali dipilih <strong>Rusak Berat</strong> atau <strong>Hilang</strong>, eksemplar tidak akan menjadi tersedia untuk peminjaman berikutnya.
                            </p>
                        </div>

                        <div
                            x-show="returnError"
                            x-cloak
                            class="mt-6 rounded-3xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-semibold text-red-700"
                            x-text="returnError"
                        ></div>

                        <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                            <a href="{{ route('loans.index') }}"
                               class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-600 transition hover:bg-gray-50">
                                Batal
                            </a>

                            <button
                                type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-700/20 transition hover:bg-emerald-800"
                            >
                                <span class="material-symbols-outlined text-[18px]">assignment_turned_in</span>
                                Proses Pengembalian
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <div class="rounded-[2rem] border border-sky-200 bg-sky-50 p-6 text-sky-800 shadow-sm">
                    <div class="flex items-start gap-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white text-sky-700">
                            <span class="material-symbols-outlined">info</span>
                        </div>

                        <div>
                            <h3 class="font-extrabold">
                                Tidak ada item aktif untuk dikembalikan
                            </h3>

                            <p class="mt-1 text-sm">
                                Transaksi ini sudah selesai atau semua eksemplar pada transaksi ini sudah diproses.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div
            x-show="returnModalOpen"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 px-4 py-8 backdrop-blur-sm"
        >
            <div
                @click.outside="closeReturnModal()"
                class="w-full max-w-xl overflow-hidden rounded-[2rem] bg-white shadow-2xl"
            >
                <div class="bg-gradient-to-r from-emerald-700 to-teal-500 px-6 py-5 text-white">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-extrabold">
                                Konfirmasi Pengembalian
                            </h3>

                            <p class="mt-1 text-sm text-emerald-50">
                                Pastikan item dan kondisi kembali sudah sesuai sebelum diproses.
                            </p>
                        </div>

                        <button
                            type="button"
                            @click="closeReturnModal()"
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white/15 transition hover:bg-white/25"
                        >
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>
                </div>

                <div class="space-y-5 p-6">
                    <div class="rounded-2xl border border-gray-100 bg-slate-50 p-4">
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-500">
                            Detail Transaksi
                        </p>

                        <p class="mt-2 font-mono text-lg font-extrabold text-gray-900">
                            {{ $loanCode }}
                        </p>

                        <p class="mt-1 text-sm text-gray-600">
                            Peminjam:
                            <span class="font-bold">{{ $loan->member?->name ?? '-' }}</span>
                        </p>

                        <p class="mt-1 text-sm text-gray-600">
                            Tanggal pinjam:
                            <span class="font-bold">{{ $loanDateText }}</span>
                            sampai
                            <span class="font-bold">{{ $dueDateText }}</span>
                        </p>

                        <p class="mt-1 text-sm text-gray-600">
                            Item yang dipilih:
                            <span class="font-bold" x-text="selectedReturnCount"></span>
                            eksemplar
                        </p>
                    </div>

                    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        <p class="font-bold">
                            Data akan langsung mengubah status eksemplar.
                        </p>

                        <p class="mt-1">
                            Kondisi rusak atau hilang akan membuat eksemplar tidak tersedia untuk peminjaman berikutnya.
                        </p>
                    </div>
                </div>

                <div class="border-t border-gray-100 bg-white px-6 py-5">
                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <button
                            type="button"
                            @click="closeReturnModal()"
                            class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-600 transition hover:bg-gray-50"
                        >
                            Periksa Lagi
                        </button>

                        <button
                            type="button"
                            @click="submitReturnForm()"
                            :disabled="returnSubmitting"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-700/20 transition hover:bg-emerald-800 disabled:cursor-not-allowed disabled:bg-gray-400"
                        >
                            <span class="material-symbols-outlined text-[18px]">assignment_turned_in</span>
                            Ya, Proses Pengembalian
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function returnConfirmation() {
                return {
                    returnModalOpen: false,
                    returnError: '',
                    returnSubmitting: false,
                    selectedReturnCount: 0,

                    openReturnModal(event) {
                        const form = event.target;
                        const selectedItems = form.querySelectorAll('input[name="loan_item_ids[]"]:checked');

                        if (selectedItems.length < 1) {
                            this.returnError = 'Pilih minimal satu eksemplar yang ingin dikembalikan.';
                            return;
                        }

                        this.returnError = '';
                        this.selectedReturnCount = selectedItems.length;
                        this.returnModalOpen = true;
                    },

                    closeReturnModal() {
                        this.returnModalOpen = false;
                    },

                    submitReturnForm() {
                        if (this.returnSubmitting) {
                            return;
                        }

                        this.returnSubmitting = true;
                        this.$refs.returnForm.submit();
                    },
                };
            }
        </script>
    </div>
</x-app-layout>
