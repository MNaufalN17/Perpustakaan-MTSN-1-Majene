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
                    Transaksi Perpustakaan
                </p>

                <h2 class="mt-1 text-xl font-bold text-gray-900">
                    Data Peminjaman Buku
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Pantau peminjaman aktif, terlambat, selesai, dan proses pengembalian buku.
                </p>
            </div>

            <a href="{{ route('loans.create') }}"
               class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-800">
                <span class="material-symbols-outlined text-[18px]">add_circle</span>
                Buat Peminjaman
            </a>
        </div>
    </x-slot>

    @php
        $loanCollection = method_exists($loans, 'getCollection') ? $loans->getCollection() : collect($loans ?? []);
        $loanCount = method_exists($loans, 'total') ? $loans->total() : $loanCollection->count();

        $activeCount = $loanCollection->where('status', 'aktif')->count();
        $lateCount = $loanCollection->where('status', 'terlambat')->count();
        $doneCount = $loanCollection->where('status', 'selesai')->count();
    @endphp

    <div
        x-data="loanCancelManager()"
        @keydown.escape.window="closeCancelLoanModal()"
        class="min-h-screen bg-gradient-to-br from-slate-50 via-emerald-50/40 to-sky-50/40 py-10"
    >
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

            <div class="mb-6 grid gap-4 md:grid-cols-4">
                <div class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-500">
                        Total Tampil
                    </p>
                    <p class="mt-2 text-3xl font-extrabold text-gray-900">
                        {{ number_format($loanCount, 0, ',', '.') }}
                    </p>
                </div>

                <div class="rounded-3xl border border-emerald-100 bg-emerald-50 p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">
                        Aktif
                    </p>
                    <p class="mt-2 text-3xl font-extrabold text-emerald-800">
                        {{ number_format($activeCount, 0, ',', '.') }}
                    </p>
                </div>

                <div class="rounded-3xl border border-red-100 bg-red-50 p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wider text-red-700">
                        Terlambat
                    </p>
                    <p class="mt-2 text-3xl font-extrabold text-red-800">
                        {{ number_format($lateCount, 0, ',', '.') }}
                    </p>
                </div>

                <div class="rounded-3xl border border-sky-100 bg-sky-50 p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wider text-sky-700">
                        Selesai
                    </p>
                    <p class="mt-2 text-3xl font-extrabold text-sky-800">
                        {{ number_format($doneCount, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            <div class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/90 shadow-[0_18px_50px_rgba(15,23,42,0.06)] backdrop-blur-xl">

                <div class="relative overflow-hidden bg-gradient-to-r from-emerald-700 to-teal-500 p-6">
                    <div class="absolute -right-16 -top-20 h-52 w-52 rounded-full bg-white/10 blur-2xl"></div>
                    <div class="absolute -left-20 bottom-0 h-48 w-48 rounded-full bg-emerald-200/20 blur-2xl"></div>

                    <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-white">
                                Daftar Peminjaman
                            </h3>

                            <p class="mt-1 text-sm text-emerald-50">
                                Gunakan tombol Proses untuk melihat detail dan memproses pengembalian.
                            </p>
                        </div>

                        <div class="rounded-2xl border border-white/20 bg-white/15 px-4 py-3 text-white">
                            <p class="text-xs text-emerald-50">
                                Fokus aksi
                            </p>

                            <p class="text-sm font-bold">
                                Proses / Batalkan
                            </p>
                        </div>
                    </div>

                    <div class="relative mt-5 flex flex-wrap gap-2 text-xs font-bold">
                        <span class="inline-flex items-center gap-2 rounded-full border border-white/25 bg-white/15 px-3 py-1.5 text-white">
                            <span class="h-2.5 w-2.5 rounded-full bg-white"></span>
                            Peminjaman Biasa
                        </span>

                        <span class="inline-flex items-center gap-2 rounded-full border border-amber-100 bg-amber-100 px-3 py-1.5 text-amber-800">
                            <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                            Perwakilan Kelas
                        </span>
                    </div>
                </div>

                <div class="border-b border-gray-100 bg-white/80 p-6">
                    <form method="GET" action="{{ route('loans.index') }}" class="grid gap-4 lg:grid-cols-4">
                        <div class="lg:col-span-2">
                            <label for="keyword" class="block text-sm font-bold text-gray-700">
                                Cari Transaksi
                            </label>

                            <input
                                id="keyword"
                                type="text"
                                name="keyword"
                                value="{{ $keyword ?? request('keyword') }}"
                                placeholder="Kode transaksi, nama anggota, NIS/NIP, judul buku..."
                                class="mt-2 block w-full rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                            >
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-bold text-gray-700">
                                Status
                            </label>

                            <select
                                id="status"
                                name="status"
                                class="mt-2 block w-full rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                            >
                                <option value="">Semua Status</option>

                                @foreach(['aktif', 'terlambat', 'selesai'] as $statusOption)
                                    <option value="{{ $statusOption }}" @selected(($status ?? request('status')) === $statusOption)>
                                        {{ ucfirst($statusOption) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-end gap-3">
                            <button
                                type="submit"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-800"
                            >
                                <span class="material-symbols-outlined text-[18px]">search</span>
                                Filter
                            </button>

                            <a href="{{ route('loans.index') }}"
                               class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-600 transition hover:bg-gray-50">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>

                <div class="p-6">
                    <div class="overflow-x-auto rounded-3xl border border-gray-100 bg-white">
                        <table class="w-full min-w-[1100px] divide-y divide-gray-100 text-left text-sm">
                            <thead class="bg-slate-50 text-xs uppercase tracking-wider text-gray-500">
                                <tr>
                                    <th class="w-[230px] px-5 py-4 font-bold">Transaksi</th>
                                    <th class="w-[240px] px-5 py-4 font-bold">Peminjam</th>
                                    <th class="w-[310px] px-5 py-4 font-bold">Ringkasan Buku</th>
                                    <th class="w-[210px] px-5 py-4 text-center font-bold">Jadwal</th>
                                    <th class="w-[140px] px-5 py-4 text-center font-bold">Status</th>
                                    <th class="w-[220px] px-5 py-4 text-center font-bold">Aksi</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse($loans as $loan)
                                    @php
                                        $loanCode = $loan->loan_code ?? ('TRX-' . $loan->id);

                                        $loanDateText = $loan->loan_date
                                            ? \Carbon\Carbon::parse($loan->loan_date)->format('d/m/Y')
                                            : '-';

                                        $dueDateText = $loan->due_date
                                            ? \Carbon\Carbon::parse($loan->due_date)->format('d/m/Y')
                                            : '-';

                                        $statusText = $loan->status ?? '-';

                                        $statusBadgeClass = match ($loan->status) {
                                            'aktif' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                            'terlambat' => 'border-red-200 bg-red-50 text-red-700',
                                            'selesai' => 'border-sky-200 bg-sky-50 text-sky-700',
                                            default => 'border-gray-200 bg-gray-50 text-gray-600',
                                        };

                                        $bookIds = $loan->loanItems
                                            ->map(fn ($loanItem) => $loanItem->bookItem?->book_id)
                                            ->filter()
                                            ->unique()
                                            ->values();

                                        $isClassLoan = str_contains(
                                            strtolower((string) ($loan->notes ?? '')),
                                            'peminjaman kelas/rombongan'
                                        ) || ($loan->loanItems->count() > 1 && $bookIds->count() === 1);

                                        $rowClass = $isClassLoan
                                            ? 'bg-amber-100/80 hover:bg-amber-200/70'
                                            : 'bg-white hover:bg-emerald-50/40';

                                        $loanTypeLabel = $isClassLoan
                                            ? 'Perwakilan Kelas'
                                            : 'Peminjaman Biasa';

                                        $loanTypeBadgeClass = $isClassLoan
                                            ? 'border-amber-300 bg-amber-200 text-amber-950'
                                            : 'border-slate-200 bg-slate-50 text-slate-600';

                                        $loanTypeDescription = $isClassLoan
                                            ? 'Diwakili 1 anggota untuk beberapa eksemplar buku yang sama.'
                                            : 'Transaksi mandiri anggota.';

                                        $canCancel = in_array($loan->status, ['aktif', 'terlambat'], true);
                                        $canProcess = in_array($loan->status, ['aktif', 'terlambat'], true);

                                        $bookTitles = $loan->loanItems
                                            ->map(fn ($loanItem) => $loanItem->bookItem?->book?->title)
                                            ->filter()
                                            ->values();

                                        $firstBookTitle = $bookTitles->first() ?? '-';
                                        $otherBookCount = max($bookTitles->count() - 1, 0);
                                        $otherBookLabel = $isClassLoan
                                            ? '+' . $otherBookCount . ' eksemplar lain'
                                            : '+' . $otherBookCount . ' buku lain';

                                        $cancelPayload = [
                                            'action' => route('loans.destroy', $loan),
                                            'loan_code' => $loanCode,
                                            'member_name' => $loan->member?->name ?? '-',
                                            'member_identity' => $loan->member?->nis_nip ?? $loan->member?->member_code ?? '-',
                                            'status' => ucfirst($statusText),
                                            'loan_date' => $loanDateText,
                                            'due_date' => $dueDateText,
                                            'item_count' => $loan->loanItems->count(),
                                        ];

                                        $cancelPayloadEncoded = base64_encode(json_encode($cancelPayload));
                                    @endphp

                                    <tr class="{{ $rowClass }} transition">
                                        <td class="border-l-[6px] {{ $isClassLoan ? 'border-amber-500' : 'border-transparent' }} px-5 py-5 align-middle">
                                            <div>
                                                <p class="font-mono text-sm font-extrabold text-gray-900">
                                                    {{ $loanCode }}
                                                </p>

                                                <p class="mt-1 text-xs text-gray-500">
                                                    ID Transaksi: {{ $loan->id }}
                                                </p>

                                                <span class="mt-3 inline-flex rounded-full border px-3 py-1 text-[11px] font-bold {{ $loanTypeBadgeClass }}">
                                                    {{ $loanTypeLabel }}
                                                </span>

                                                <p class="mt-2 max-w-[190px] text-[11px] leading-4 {{ $isClassLoan ? 'font-semibold text-amber-900' : 'text-gray-400' }}">
                                                    {{ $loanTypeDescription }}
                                                </p>
                                            </div>
                                        </td>

                                        <td class="px-5 py-5 align-middle">
                                            <div class="max-w-[230px]">
                                                <p class="font-bold text-gray-900">
                                                    {{ $loan->member?->name ?? '-' }}
                                                </p>

                                                <p class="mt-1 text-xs text-gray-500">
                                                    {{ $loan->member?->nis_nip ?? $loan->member?->member_code ?? '-' }}
                                                </p>

                                                <p class="mt-1 text-xs text-gray-400">
                                                    {{ $loan->member?->studentClass?->class_name ?? 'Guru/Staff' }}
                                                </p>
                                            </div>
                                        </td>

                                        <td class="px-5 py-5 align-middle">
                                            <div class="max-w-[300px] rounded-2xl border border-gray-100 bg-slate-50 px-4 py-3">
                                                <p class="font-bold leading-5 text-gray-900">
                                                    {{ $firstBookTitle }}
                                                </p>

                                                <div class="mt-2 flex flex-wrap items-center gap-2">
                                                    <span class="inline-flex rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1 text-[11px] font-bold text-emerald-700">
                                                        {{ $loan->loanItems->count() }} eksemplar
                                                    </span>

                                                    @if($otherBookCount > 0)
                                                        <span class="inline-flex rounded-full border border-sky-100 bg-sky-50 px-3 py-1 text-[11px] font-bold text-sky-700">
                                                            {{ $otherBookLabel }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>

                                        <td class="px-5 py-5 text-center align-middle">
                                            <div class="inline-flex flex-col items-center rounded-2xl border border-gray-100 bg-white px-4 py-3">
                                                <p class="text-xs font-bold text-gray-500">
                                                    {{ $loanDateText }}
                                                </p>

                                                <span class="material-symbols-outlined my-1 text-[16px] text-gray-400">
                                                    arrow_downward
                                                </span>

                                                <p class="text-xs font-extrabold text-gray-800">
                                                    {{ $dueDateText }}
                                                </p>
                                            </div>
                                        </td>

                                        <td class="px-5 py-5 text-center align-middle">
                                            <span class="inline-flex rounded-full border px-3 py-1.5 text-xs font-bold {{ $statusBadgeClass }}">
                                                {{ ucfirst($statusText) }}
                                            </span>
                                        </td>

                                        <td class="px-5 py-5 align-middle">
                                            <div class="mx-auto w-[200px] rounded-2xl border border-gray-100 bg-slate-50 p-2 shadow-sm">
                                                <div class="grid grid-cols-1 gap-2">
                                                    @if($canProcess)
                                                        <a href="{{ route('loans.show', $loan) }}"
                                                           class="inline-flex h-10 items-center justify-center gap-1.5 rounded-xl bg-emerald-700 px-3 text-xs font-bold text-white transition hover:bg-emerald-800">
                                                            <span class="material-symbols-outlined text-[16px]">task_alt</span>
                                                            Proses
                                                        </a>
                                                    @else
                                                        <a href="{{ route('loans.show', $loan) }}"
                                                           class="inline-flex h-10 items-center justify-center gap-1.5 rounded-xl border border-sky-200 bg-sky-50 px-3 text-xs font-bold text-sky-700 transition hover:bg-sky-100">
                                                            <span class="material-symbols-outlined text-[16px]">visibility</span>
                                                            Lihat
                                                        </a>
                                                    @endif

                                                    @if($canCancel)
                                                        <button
                                                            type="button"
                                                            data-cancel-payload="{{ $cancelPayloadEncoded }}"
                                                            @click="openCancelLoanModalFromButton($event.currentTarget)"
                                                            class="inline-flex h-10 items-center justify-center gap-1.5 rounded-xl border border-red-200 bg-red-50 px-3 text-xs font-bold text-red-700 transition hover:bg-red-100"
                                                        >
                                                            <span class="material-symbols-outlined text-[16px]">cancel</span>
                                                            Batalkan
                                                        </button>
                                                    @else
                                                        <button
                                                            type="button"
                                                            onclick="alert('Transaksi ini tidak bisa dibatalkan karena statusnya bukan aktif atau terlambat.')"
                                                            class="inline-flex h-10 items-center justify-center gap-1.5 rounded-xl border border-gray-200 bg-gray-50 px-3 text-xs font-bold text-gray-500"
                                                        >
                                                            <span class="material-symbols-outlined text-[16px]">block</span>
                                                            Tidak Bisa Batal
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>

                                            @if($canProcess)
                                                <p class="mx-auto mt-2 w-[200px] text-center text-[11px] leading-4 text-gray-400">
                                                    Proses untuk detail/pengembalian.
                                                </p>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-14 text-center">
                                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                                                <span class="material-symbols-outlined">receipt_long</span>
                                            </div>

                                            <p class="mt-4 text-sm font-semibold text-gray-700">
                                                Belum ada transaksi peminjaman.
                                            </p>

                                            <p class="mt-1 text-xs text-gray-500">
                                                Klik tombol Buat Peminjaman untuk membuat transaksi baru.
                                            </p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if(method_exists($loans, 'links'))
                        <div class="mt-6">
                            {{ $loans->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div
            x-show="cancelModalOpen"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 px-4 py-8 backdrop-blur-sm"
        >
            <div
                @click.outside="closeCancelLoanModal()"
                class="flex max-h-[90vh] w-full max-w-xl flex-col overflow-hidden rounded-[2rem] bg-white shadow-2xl"
            >
                <div class="shrink-0 bg-gradient-to-r from-red-700 to-rose-500 px-6 py-5 text-white">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-extrabold">
                                Konfirmasi Pembatalan Transaksi
                            </h3>

                            <p class="mt-1 text-sm text-red-50">
                                Pembatalan hanya untuk transaksi salah input atau tidak jadi dipinjam.
                            </p>
                        </div>

                        <button
                            type="button"
                            @click="closeCancelLoanModal()"
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white/15 transition hover:bg-white/25"
                        >
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>
                </div>

                <form
                    method="POST"
                    :action="cancelLoan.action"
                    @submit="validateCancelLoanSubmit($event)"
                    class="flex min-h-0 flex-1 flex-col"
                >
                    @csrf
                    @method('DELETE')

                    <div class="min-h-0 flex-1 space-y-5 overflow-y-auto p-6">
                        <div
                            x-show="cancelError"
                            x-cloak
                            class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700"
                            x-text="cancelError"
                        ></div>

                        <div class="rounded-2xl border border-gray-100 bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">
                                Detail Transaksi
                            </p>

                            <p class="mt-2 text-lg font-extrabold text-gray-900" x-text="cancelLoan.loan_code"></p>

                            <p class="mt-1 text-sm text-gray-600">
                                Peminjam:
                                <span class="font-bold" x-text="cancelLoan.member_name"></span>
                            </p>

                            <p class="mt-1 text-sm text-gray-600">
                                Identitas:
                                <span class="font-bold" x-text="cancelLoan.member_identity"></span>
                            </p>

                            <p class="mt-1 text-sm text-gray-600">
                                Status:
                                <span class="font-bold" x-text="cancelLoan.status"></span>
                            </p>

                            <p class="mt-1 text-sm text-gray-600">
                                Jumlah item:
                                <span class="font-bold" x-text="cancelLoan.item_count"></span>
                            </p>

                            <p class="mt-1 text-sm text-gray-600">
                                Tanggal pinjam:
                                <span class="font-bold" x-text="cancelLoan.loan_date"></span>
                                —
                                Batas kembali:
                                <span class="font-bold" x-text="cancelLoan.due_date"></span>
                            </p>
                        </div>

                        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                            <p class="font-bold">
                                Jangan gunakan tombol ini untuk pengembalian buku.
                            </p>

                            <p class="mt-1">
                                Jika buku memang benar dipinjam dan sudah kembali, gunakan tombol Proses lalu lakukan pengembalian dari detail transaksi.
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700">
                                Ketik kode transaksi untuk membatalkan
                            </label>

                            <input
                                type="text"
                                name="cancel_confirmation"
                                x-model="cancelTypedCode"
                                class="mt-2 block w-full rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm font-bold focus:border-red-500 focus:ring-2 focus:ring-red-100"
                                :placeholder="cancelLoan.loan_code"
                                autocomplete="off"
                            >

                            <p class="mt-2 text-xs text-gray-500">
                                Harus sama persis dengan:
                                <span class="font-bold text-gray-800" x-text="cancelLoan.loan_code"></span>
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700">
                                Catatan pembatalan
                                <span class="text-xs font-semibold text-gray-400">(opsional)</span>
                            </label>

                            <textarea
                                name="cancel_reason"
                                x-model="cancelReason"
                                rows="3"
                                class="mt-2 block w-full rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm focus:border-red-500 focus:ring-2 focus:ring-red-100"
                                placeholder="Opsional. Contoh: Salah pilih anggota / salah pilih buku / transaksi tidak jadi dipinjam."
                            ></textarea>
                        </div>

                        <label class="flex items-start gap-3 rounded-2xl border border-gray-100 bg-slate-50 p-4">
                            <input
                                type="checkbox"
                                name="cancel_agreement"
                                value="1"
                                x-model="cancelAgreement"
                                class="mt-1 rounded border-gray-300 text-red-600 focus:ring-red-500"
                            >

                            <span class="text-sm text-gray-700">
                                Saya paham bahwa transaksi ini akan dibatalkan dan dihapus dari daftar transaksi aktif.
                            </span>
                        </label>
                    </div>

                    <div class="shrink-0 border-t border-gray-100 bg-white px-6 py-5">
                        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                            <button
                                type="button"
                                @click="closeCancelLoanModal()"
                                class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-600 transition hover:bg-gray-50"
                            >
                                Tidak Jadi
                            </button>

                            <button
                                type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-red-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-red-600/20 transition hover:bg-red-700"
                            >
                                <span class="material-symbols-outlined text-[18px]">cancel</span>
                                Ya, Batalkan Transaksi
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function loanCancelManager() {
                return {
                    cancelModalOpen: false,
                    cancelError: '',
                    cancelTypedCode: '',
                    cancelReason: '',
                    cancelAgreement: false,
                    cancelLoan: {
                        action: '',
                        loan_code: '',
                        member_name: '',
                        member_identity: '',
                        status: '',
                        loan_date: '',
                        due_date: '',
                        item_count: 0,
                    },

                    openCancelLoanModalFromButton(button) {
                        try {
                            const encodedPayload = button.dataset.cancelPayload || '';
                            const payload = JSON.parse(atob(encodedPayload));
                            this.openCancelLoanModal(payload);
                        } catch (error) {
                            console.error(error);

                            this.cancelLoan = {
                                action: '',
                                loan_code: '',
                                member_name: '',
                                member_identity: '',
                                status: '',
                                loan_date: '',
                                due_date: '',
                                item_count: 0,
                            };

                            this.cancelError = 'Data transaksi gagal dibaca. Silakan refresh halaman.';
                            this.cancelModalOpen = true;
                        }
                    },

                    openCancelLoanModal(payload) {
                        this.cancelLoan = payload;
                        this.cancelTypedCode = '';
                        this.cancelReason = '';
                        this.cancelAgreement = false;
                        this.cancelError = '';
                        this.cancelModalOpen = true;
                    },

                    closeCancelLoanModal() {
                        this.cancelModalOpen = false;
                        this.cancelError = '';
                        this.cancelTypedCode = '';
                        this.cancelReason = '';
                        this.cancelAgreement = false;
                    },

                    validateCancelLoanSubmit(event) {
                        this.cancelError = '';

                        if (this.cancelTypedCode !== this.cancelLoan.loan_code) {
                            event.preventDefault();
                            this.cancelError = 'Kode transaksi tidak sesuai. Pembatalan tidak diproses.';
                            return;
                        }

                        if (!this.cancelAgreement) {
                            event.preventDefault();
                            this.cancelError = 'Centang persetujuan pembatalan terlebih dahulu.';
                            return;
                        }

                        const confirmed = confirm(
                            'Batalkan transaksi ' + this.cancelLoan.loan_code + '? ' +
                            'Aksi ini hanya untuk transaksi salah input atau tidak jadi dipinjam.'
                        );

                        if (!confirmed) {
                            event.preventDefault();
                        }
                    },
                };
            }
        </script>
    </div>
</x-app-layout>
