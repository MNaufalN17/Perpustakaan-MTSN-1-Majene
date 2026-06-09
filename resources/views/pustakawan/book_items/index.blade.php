<x-app-layout>
    @php
        $canManage = auth()->check() && (int) auth()->user()->role_id === 1;

        $statusLabels = [
            'tersedia' => 'Tersedia',
            'dipinjam' => 'Dipinjam',
            'terlambat' => 'Terlambat',
            'rusak' => 'Rusak',
            'hilang' => 'Hilang',
            'nonaktif' => 'Dikeluarkan dari Stok',
        ];

        $conditionLabels = [
            'baik' => 'Baik',
            'rusak ringan' => 'Rusak Ringan',
            'rusak berat' => 'Rusak Berat',
            'hilang' => 'Hilang',
        ];

        $bookItemPaginator = $bookItems ?? $items ?? collect();

        $bookItemCollection = method_exists($bookItemPaginator, 'getCollection')
            ? $bookItemPaginator->getCollection()
            : collect($bookItemPaginator);

        $bookItemCount = method_exists($bookItemPaginator, 'total')
            ? $bookItemPaginator->total()
            : $bookItemCollection->count();

        $bulkDeleteItems = $bookItemCollection->map(function ($item) use ($statusLabels, $conditionLabels) {
            $activeLoanItem = $item->activeLoanItem ?? null;
            $activeLoan = $activeLoanItem?->loan;
            $member = $activeLoan?->member;

            $isActiveLoan = (bool) $activeLoanItem
                || in_array($item->status, ['dipinjam', 'terlambat'], true);

            $hasLoanHistory = isset($item->loan_items_count)
                ? (int) $item->loan_items_count > 0
                : (method_exists($item, 'loanItems') ? $item->loanItems()->exists() : false);

            $isOutOfStock = $item->status === 'nonaktif';

            $canSelect = !$isActiveLoan && !($isOutOfStock && $hasLoanHistory);

            return [
                'id' => (string) $item->id,
                'item_code' => $item->item_code ?? '-',
                'copy_number' => $item->copy_number ?? '-',
                'book_title' => $item->book?->title ?? '-',
                'author' => $item->book?->author ?? '-',
                'status' => $item->status ?? '-',
                'status_label' => $statusLabels[$item->status] ?? ucwords((string) $item->status),
                'condition' => $item->condition ?? '-',
                'condition_label' => $conditionLabels[$item->condition] ?? ucwords((string) $item->condition),
                'location' => $item->location ?? '-',
                'is_active_loan' => $isActiveLoan,
                'is_out_of_stock' => $isOutOfStock,
                'has_loan_history' => $hasLoanHistory,
                'can_select' => $canSelect,
                'member_name' => $member?->name,
                'member_identity' => $member?->nis_nip ?? $member?->member_code,
                'due_date' => $activeLoan?->due_date
                    ? \Carbon\Carbon::parse($activeLoan->due_date)->format('d/m/Y')
                    : null,
            ];
        })->values();
    @endphp

    <style>
        [x-cloak] {
            display: none !important;
        }

        .soft-confirm-bg {
            background-image:
                radial-gradient(circle at 15% 15%, rgba(16, 185, 129, 0.14), transparent 28%),
                radial-gradient(circle at 85% 20%, rgba(14, 165, 233, 0.12), transparent 26%),
                radial-gradient(circle at 70% 85%, rgba(245, 158, 11, 0.10), transparent 24%);
        }
    </style>

    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
                    Manajemen Koleksi
                </p>

                <h2 class="mt-1 text-xl font-bold text-gray-900">
                    Eksemplar Buku
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Kelola copy fisik buku, status peminjaman, kondisi, dan stok aktif.
                </p>
            </div>

            @if($canManage)
                <a href="{{ route('book_items.create') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800">
                    <span class="material-symbols-outlined text-[18px]">add_circle</span>
                    Tambah Eksemplar
                </a>
            @endif
        </div>
    </x-slot>

    <div
        x-data="bookItemManager(@js($bulkDeleteItems))"
        @keydown.escape.window="closeAllModals()"
        class="min-h-screen bg-gradient-to-br from-slate-50 via-emerald-50/40 to-sky-50/40 py-10"
    >
        <form x-ref="singleProcessForm" method="POST" action="" class="hidden">
            @csrf
            @method('DELETE')
        </form>

        <form x-ref="restoreStockForm" method="POST" action="" class="hidden">
            @csrf
            @method('PATCH')
        </form>

        <form
            x-ref="bulkSubmitForm"
            method="POST"
            action="{{ route('book_items.bulk_destroy') }}"
            class="hidden"
        >
            @csrf
            @method('DELETE')
        </form>

        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/90 shadow-[0_18px_50px_rgba(15,23,42,0.06)] backdrop-blur-xl">

                <div class="relative overflow-hidden bg-gradient-to-r from-emerald-700 to-teal-500 px-6 py-6">
                    <div class="absolute -right-16 -top-20 h-52 w-52 rounded-full bg-white/10 blur-2xl"></div>
                    <div class="absolute -left-20 bottom-0 h-48 w-48 rounded-full bg-emerald-200/20 blur-2xl"></div>

                    <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-white">
                                Daftar Eksemplar
                            </h3>

                            <p class="mt-1 text-sm text-emerald-50">
                                Copy yang pernah dipinjam tidak dihapus permanen, tetapi dikeluarkan dari stok.
                            </p>
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                            <div class="flex w-fit items-center gap-3 rounded-2xl border border-white/20 bg-white/15 px-4 py-3 text-white">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20">
                                    <span class="material-symbols-outlined">inventory_2</span>
                                </div>

                                <div>
                                    <p class="text-xs text-emerald-50">
                                        Total Eksemplar
                                    </p>

                                    <p class="text-lg font-bold">
                                        {{ number_format($bookItemCount, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>

                            @if($canManage)
                                <button
                                    type="button"
                                    @click="openBulkModal()"
                                    class="inline-flex items-center justify-center gap-2 rounded-2xl border border-white/20 bg-white px-5 py-3 text-sm font-bold text-emerald-700 shadow-sm transition hover:bg-emerald-50"
                                >
                                    <span class="material-symbols-outlined text-[18px]">checklist</span>
                                    Proses Beberapa Copy
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="border-b border-gray-100 bg-white/80 p-6">
                    <form method="GET" action="{{ route('book_items.index') }}" class="grid gap-4 lg:grid-cols-4">
                        <div class="lg:col-span-2">
                            <label for="keyword" class="block text-sm font-bold text-gray-700">
                                Cari Eksemplar
                            </label>

                            <input
                                id="keyword"
                                type="text"
                                name="keyword"
                                value="{{ $keyword ?? request('keyword') }}"
                                placeholder="Kode eksemplar, judul buku, penulis, lokasi..."
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

                                @foreach(['tersedia', 'dipinjam', 'rusak', 'hilang', 'nonaktif'] as $statusOption)
                                    <option value="{{ $statusOption }}" @selected(($status ?? request('status')) === $statusOption)>
                                        {{ $statusLabels[$statusOption] ?? ucfirst($statusOption) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="condition" class="block text-sm font-bold text-gray-700">
                                Kondisi
                            </label>

                            <select
                                id="condition"
                                name="condition"
                                class="mt-2 block w-full rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                            >
                                <option value="">Semua Kondisi</option>

                                @foreach(['baik', 'rusak ringan', 'rusak berat', 'hilang'] as $conditionOption)
                                    <option value="{{ $conditionOption }}" @selected(($condition ?? request('condition')) === $conditionOption)>
                                        {{ $conditionLabels[$conditionOption] ?? ucwords($conditionOption) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row lg:col-span-4">
                            <button
                                type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-800"
                            >
                                <span class="material-symbols-outlined text-[18px]">search</span>
                                Filter
                            </button>

                            <a href="{{ route('book_items.index') }}"
                               class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-600 transition hover:bg-gray-50">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>

                <div class="p-6">
                    <div class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm">
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[1180px] table-fixed divide-y divide-gray-100 text-sm">
                                <thead class="bg-slate-50">
                                    <tr class="text-xs uppercase tracking-[0.14em] text-gray-500">
                                        <th class="w-[17%] px-5 py-4 text-left font-extrabold">Kode Copy</th>
                                        <th class="w-[28%] px-5 py-4 text-left font-extrabold">Buku</th>
                                        <th class="w-[15%] px-5 py-4 text-center font-extrabold">Status</th>
                                        <th class="w-[15%] px-5 py-4 text-center font-extrabold">Kondisi</th>
                                        <th class="w-[15%] px-5 py-4 text-left font-extrabold">Keterangan</th>
                                        <th class="w-[10%] px-5 py-4 text-center font-extrabold">Aksi</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @forelse($bookItemPaginator as $bookItem)
                                        @php
                                            $activeLoanItem = $bookItem->activeLoanItem ?? null;
                                            $activeLoan = $activeLoanItem?->loan;
                                            $member = $activeLoan?->member;

                                            $isActiveLoan = (bool) $activeLoanItem
                                                || in_array($bookItem->status, ['dipinjam', 'terlambat'], true);

                                            $hasLoanHistory = isset($bookItem->loan_items_count)
                                                ? (int) $bookItem->loan_items_count > 0
                                                : (method_exists($bookItem, 'loanItems') ? $bookItem->loanItems()->exists() : false);

                                            $isOutOfStock = $bookItem->status === 'nonaktif';

                                            $canProcessSingle = !$isActiveLoan && !($isOutOfStock && $hasLoanHistory);

                                            $isRestorable = $bookItem->status === 'nonaktif'
                                                && !$isActiveLoan
                                                && in_array($bookItem->condition, ['baik', 'rusak ringan'], true);

                                            $needsConditionRepair = $bookItem->status === 'nonaktif'
                                                && in_array($bookItem->condition, ['hilang', 'rusak berat'], true);

                                            $statusLabel = $statusLabels[$bookItem->status] ?? ucwords((string) $bookItem->status);
                                            $conditionLabel = $conditionLabels[$bookItem->condition] ?? ucwords((string) $bookItem->condition);

                                            if ($isActiveLoan) {
                                                $infoTitle = 'Sedang Dipinjam';
                                                $infoText = 'Selesaikan pengembalian terlebih dahulu sebelum copy ini diproses.';
                                                $infoClass = 'border-amber-200 bg-amber-50 text-amber-700';
                                                $infoIcon = 'schedule';
                                            } elseif ($isRestorable) {
                                                $infoTitle = 'Keluar dari Stok';
                                                $infoText = 'Copy ini bisa dimasukkan kembali ke stok.';
                                                $infoClass = 'border-emerald-200 bg-emerald-50 text-emerald-700';
                                                $infoIcon = 'inventory_2';
                                            } elseif ($needsConditionRepair) {
                                                $infoTitle = 'Kondisi Belum Layak';
                                                $infoText = 'Ubah kondisi terlebih dahulu sebelum masuk stok.';
                                                $infoClass = 'border-amber-200 bg-amber-50 text-amber-700';
                                                $infoIcon = 'build';
                                            } elseif ($isOutOfStock && $hasLoanHistory) {
                                                $infoTitle = 'Keluar dari Stok';
                                                $infoText = 'Copy ini disimpan hanya untuk riwayat transaksi.';
                                                $infoClass = 'border-gray-200 bg-gray-50 text-gray-600';
                                                $infoIcon = 'inventory_2';
                                            } elseif ($hasLoanHistory) {
                                                $infoTitle = 'Pernah Dipinjam';
                                                $infoText = 'Jika diproses, copy hanya dikeluarkan dari stok.';
                                                $infoClass = 'border-sky-200 bg-sky-50 text-sky-700';
                                                $infoIcon = 'history';
                                            } else {
                                                $infoTitle = 'Bisa Dihapus';
                                                $infoText = 'Belum punya riwayat, aman dihapus permanen.';
                                                $infoClass = 'border-emerald-200 bg-emerald-50 text-emerald-700';
                                                $infoIcon = 'check_circle';
                                            }

                                            if ($isRestorable) {
                                                $approvalPayload = [
                                                    'type' => 'restore',
                                                    'action' => route('book_items.restore_to_stock', $bookItem),
                                                    'title' => $bookItem->item_code ?? 'Eksemplar',
                                                    'subtitle' => $bookItem->book?->title ?? '-',
                                                    'message' => 'Copy ini akan dimasukkan kembali ke stok dan bisa digunakan lagi.',
                                                    'confirm_text' => 'Masukkan ke Stok',
                                                    'tone' => 'emerald',
                                                    'icon' => 'inventory_2',
                                                ];

                                                $actionLabel = 'Masukkan';
                                                $actionIcon = 'inventory_2';
                                                $actionClass = 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100';
                                            } elseif ($needsConditionRepair) {
                                                $approvalPayload = [
                                                    'type' => 'info',
                                                    'action' => '',
                                                    'title' => $bookItem->item_code ?? 'Eksemplar',
                                                    'subtitle' => $bookItem->book?->title ?? '-',
                                                    'message' => 'Copy ini belum bisa dimasukkan ke stok karena kondisinya masih hilang atau rusak berat. Ubah kondisi melalui menu Edit terlebih dahulu.',
                                                    'confirm_text' => 'Mengerti',
                                                    'tone' => 'amber',
                                                    'icon' => 'build',
                                                ];

                                                $actionLabel = 'Info';
                                                $actionIcon = 'info';
                                                $actionClass = 'border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100';
                                            } elseif ($canProcessSingle) {
                                                $approvalPayload = [
                                                    'type' => 'single',
                                                    'action' => route('book_items.destroy', $bookItem),
                                                    'title' => $bookItem->item_code ?? 'Eksemplar',
                                                    'subtitle' => $bookItem->book?->title ?? '-',
                                                    'message' => $hasLoanHistory
                                                        ? 'Copy ini pernah dipinjam. Sistem tidak akan menghapus permanen, tetapi mengeluarkannya dari stok agar riwayat tetap aman.'
                                                        : 'Copy ini belum memiliki riwayat peminjaman. Data copy akan dihapus permanen.',
                                                    'confirm_text' => $hasLoanHistory ? 'Keluarkan dari Stok' : 'Hapus Permanen',
                                                    'tone' => $hasLoanHistory ? 'sky' : 'red',
                                                    'icon' => $hasLoanHistory ? 'archive' : 'delete_forever',
                                                ];

                                                $actionLabel = $hasLoanHistory ? 'Keluarkan' : 'Hapus';
                                                $actionIcon = $hasLoanHistory ? 'archive' : 'delete';
                                                $actionClass = $hasLoanHistory
                                                    ? 'border-sky-200 bg-sky-50 text-sky-700 hover:bg-sky-100'
                                                    : 'border-red-200 bg-red-50 text-red-700 hover:bg-red-100';
                                            } else {
                                                $approvalPayload = [
                                                    'type' => 'info',
                                                    'action' => '',
                                                    'title' => $bookItem->item_code ?? 'Eksemplar',
                                                    'subtitle' => $bookItem->book?->title ?? '-',
                                                    'message' => $isActiveLoan
                                                        ? 'Copy ini sedang dipinjam. Selesaikan pengembalian terlebih dahulu.'
                                                        : 'Copy ini sudah dikeluarkan dari stok dan masih menyimpan riwayat transaksi.',
                                                    'confirm_text' => 'Mengerti',
                                                    'tone' => $isActiveLoan ? 'amber' : 'gray',
                                                    'icon' => $isActiveLoan ? 'schedule' : 'inventory_2',
                                                ];

                                                $actionLabel = 'Info';
                                                $actionIcon = 'info';
                                                $actionClass = 'border-gray-200 bg-gray-50 text-gray-500 hover:bg-gray-100';
                                            }

                                            $approvalPayloadEncoded = base64_encode(json_encode($approvalPayload));
                                        @endphp

                                        <tr class="align-middle transition hover:bg-emerald-50/40">
                                            <td class="px-5 py-5">
                                                <p class="font-mono text-sm font-black text-gray-950">
                                                    {{ $bookItem->item_code ?? '-' }}
                                                </p>

                                                <p class="mt-1 text-xs font-semibold text-gray-500">
                                                    Copy: {{ $bookItem->copy_number ?? '-' }}
                                                </p>

                                                <p class="mt-1 text-xs text-gray-400">
                                                    Lokasi: {{ $bookItem->location ?? '-' }}
                                                </p>
                                            </td>

                                            <td class="px-5 py-5">
                                                <p class="break-words text-sm font-extrabold leading-5 text-gray-950">
                                                    {{ $bookItem->book?->title ?? '-' }}
                                                </p>

                                                <p class="mt-1 break-words text-xs text-gray-500">
                                                    {{ $bookItem->book?->author ?? '-' }}
                                                </p>
                                            </td>

                                            <td class="px-5 py-5 text-center">
                                                @php
                                                    $statusBadgeClass = match ($bookItem->status) {
                                                        'tersedia' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                                        'dipinjam' => 'border-amber-200 bg-amber-50 text-amber-700',
                                                        'terlambat' => 'border-red-200 bg-red-50 text-red-700',
                                                        'rusak' => 'border-red-200 bg-red-50 text-red-700',
                                                        'hilang' => 'border-slate-300 bg-slate-100 text-slate-700',
                                                        'nonaktif' => 'border-gray-200 bg-gray-50 text-gray-600',
                                                        default => 'border-gray-200 bg-gray-50 text-gray-600',
                                                    };
                                                @endphp

                                                <span class="inline-flex rounded-full border px-3 py-2 text-xs font-extrabold {{ $statusBadgeClass }}">
                                                    {{ $statusLabel }}
                                                </span>
                                            </td>

                                            <td class="px-5 py-5 text-center">
                                                <span class="inline-flex rounded-full border border-gray-200 bg-white px-3 py-2 text-xs font-extrabold text-gray-700">
                                                    {{ $conditionLabel }}
                                                </span>
                                            </td>

                                            <td class="px-5 py-5">
                                                <div class="rounded-2xl border px-3 py-3 {{ $infoClass }}">
                                                    <p class="inline-flex items-center gap-1.5 text-xs font-black">
                                                        <span class="material-symbols-outlined text-[15px]">{{ $infoIcon }}</span>
                                                        {{ $infoTitle }}
                                                    </p>

                                                    <p class="mt-1 text-xs leading-5">
                                                        {{ $infoText }}
                                                    </p>

                                                    @if($isActiveLoan)
                                                        <p class="mt-2 text-xs font-bold">
                                                            {{ $member->name ?? '-' }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </td>

                                            <td class="px-5 py-5">
                                                <div class="mx-auto grid w-[130px] grid-cols-1 gap-2">
                                                    <a href="{{ route('book_items.show', $bookItem) }}"
                                                       class="inline-flex h-9 items-center justify-center gap-1 rounded-xl border border-emerald-200 bg-white px-2 text-xs font-extrabold text-emerald-700 transition hover:bg-emerald-50">
                                                        <span class="material-symbols-outlined text-[15px]">visibility</span>
                                                        Lihat
                                                    </a>

                                                    @if($canManage)
                                                        <a href="{{ route('book_items.edit', $bookItem) }}"
                                                           class="inline-flex h-9 items-center justify-center gap-1 rounded-xl border border-teal-200 bg-white px-2 text-xs font-extrabold text-teal-700 transition hover:bg-teal-50">
                                                            <span class="material-symbols-outlined text-[15px]">edit</span>
                                                            Edit
                                                        </a>

                                                        <button
                                                            type="button"
                                                            data-approval-payload="{{ $approvalPayloadEncoded }}"
                                                            @click="openApprovalFromButton($event.currentTarget)"
                                                            class="inline-flex h-9 items-center justify-center gap-1 rounded-xl border px-2 text-xs font-extrabold transition {{ $actionClass }}"
                                                        >
                                                            <span class="material-symbols-outlined text-[15px]">
                                                                {{ $actionIcon }}
                                                            </span>

                                                            {{ $actionLabel }}
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-14 text-center">
                                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                                                    <span class="material-symbols-outlined">inventory_2</span>
                                                </div>

                                                <p class="mt-4 text-sm font-semibold text-gray-700">
                                                    Belum ada data eksemplar.
                                                </p>

                                                <p class="mt-1 text-xs text-gray-500">
                                                    Tambahkan eksemplar buku untuk mulai mengelola stok fisik.
                                                </p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if(method_exists($bookItemPaginator, 'links'))
                        <div class="mt-6">
                            {{ $bookItemPaginator->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div
            x-show="bulkModalOpen"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 px-4 py-8 backdrop-blur-md"
        >
            <div
                @click.outside="closeBulkModal()"
                x-show="bulkModalOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                class="flex max-h-[90vh] w-full max-w-5xl flex-col overflow-hidden rounded-[2rem] bg-white shadow-[0_30px_90px_rgba(15,23,42,0.35)]"
            >
                <div class="shrink-0 bg-gradient-to-r from-emerald-700 to-teal-500 px-6 py-5 text-white">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-black">
                                Proses Beberapa Copy
                            </h3>

                            <p class="mt-1 text-sm text-emerald-50">
                                Pilih copy yang ingin dihapus permanen atau dikeluarkan dari stok.
                            </p>
                        </div>

                        <button
                            type="button"
                            @click="closeBulkModal()"
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white/15 transition hover:bg-white/25"
                        >
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>
                </div>

                <form
                    x-ref="bulkProcessForm"
                    @submit.prevent="requestBulkProcess()"
                    class="flex min-h-0 flex-1 flex-col"
                >
                    <div class="shrink-0 border-b border-gray-100 bg-slate-50 px-6 py-4">
                        <div class="grid gap-3 md:grid-cols-4">
                            <div class="rounded-2xl border border-white bg-white px-4 py-3 shadow-sm">
                                <p class="text-xs font-bold uppercase tracking-wider text-gray-500">
                                    Total Tampil
                                </p>

                                <p class="mt-1 text-2xl font-black text-gray-900" x-text="items.length"></p>
                            </div>

                            <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 shadow-sm">
                                <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">
                                    Bisa Diproses
                                </p>

                                <p class="mt-1 text-2xl font-black text-emerald-800" x-text="eligibleItems().length"></p>
                            </div>

                            <div class="rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3 shadow-sm">
                                <p class="text-xs font-bold uppercase tracking-wider text-amber-700">
                                    Sedang Dipinjam
                                </p>

                                <p class="mt-1 text-2xl font-black text-amber-800" x-text="borrowedItems().length"></p>
                            </div>

                            <div class="rounded-2xl border border-sky-100 bg-sky-50 px-4 py-3 shadow-sm">
                                <p class="text-xs font-bold uppercase tracking-wider text-sky-700">
                                    Terpilih
                                </p>

                                <p class="mt-1 text-2xl font-black text-sky-800" x-text="selectedIds.length"></p>
                            </div>
                        </div>

                        <div
                            x-show="bulkError"
                            x-cloak
                            class="mt-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700"
                            x-text="bulkError"
                        ></div>

                        <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-xs font-semibold text-gray-500">
                                Copy yang sedang dipinjam tidak bisa dipilih.
                            </p>

                            <div class="flex gap-2">
                                <button
                                    type="button"
                                    @click="selectAllEligible()"
                                    class="inline-flex items-center justify-center rounded-xl border border-emerald-200 bg-white px-4 py-2 text-xs font-bold text-emerald-700 hover:bg-emerald-50"
                                >
                                    Pilih Semua
                                </button>

                                <button
                                    type="button"
                                    @click="clearSelected()"
                                    class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-xs font-bold text-gray-600 hover:bg-gray-50"
                                >
                                    Kosongkan
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="min-h-0 flex-1 overflow-y-auto p-6">
                        <div class="grid gap-3 md:grid-cols-2">
                            <template x-for="item in items" :key="item.id">
                                <label
                                    class="flex items-start gap-4 rounded-3xl border p-4 transition"
                                    :class="item.can_select
                                        ? 'cursor-pointer border-gray-100 bg-white hover:border-emerald-200 hover:bg-emerald-50/40'
                                        : 'cursor-not-allowed border-gray-200 bg-gray-50 opacity-80'"
                                >
                                    <input
                                        type="checkbox"
                                        :value="String(item.id)"
                                        x-model="selectedIds"
                                        :disabled="!item.can_select"
                                        class="mt-1 h-5 w-5 rounded border-gray-300 text-emerald-700 focus:ring-emerald-500 disabled:cursor-not-allowed disabled:opacity-40"
                                    >

                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="truncate font-black text-gray-900" x-text="item.book_title"></p>

                                                <p class="mt-1 font-mono text-xs font-extrabold text-gray-600" x-text="item.item_code"></p>
                                            </div>

                                            <span
                                                class="inline-flex shrink-0 items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-bold"
                                                :class="badgeClass(item)"
                                            >
                                                <span class="material-symbols-outlined text-[14px]" x-text="badgeIcon(item)"></span>
                                                <span x-text="badgeText(item)"></span>
                                            </span>
                                        </div>

                                        <p class="mt-2 text-xs leading-5 text-gray-500">
                                            Kondisi:
                                            <span class="font-bold" x-text="item.condition_label"></span>
                                            —
                                            Lokasi:
                                            <span class="font-bold" x-text="item.location || '-'"></span>
                                        </p>

                                        <template x-if="item.is_active_loan">
                                            <div class="mt-3 rounded-2xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                                                <p class="font-bold">
                                                    Sedang dipinjam oleh <span x-text="item.member_name || '-'"></span>
                                                </p>

                                                <p class="mt-1" x-show="item.due_date">
                                                    Batas kembali:
                                                    <span class="font-bold" x-text="item.due_date"></span>
                                                </p>
                                            </div>
                                        </template>
                                    </div>
                                </label>
                            </template>
                        </div>

                        <div
                            x-show="items.length === 0"
                            class="rounded-2xl border border-gray-100 bg-slate-50 px-4 py-10 text-center text-sm text-gray-500"
                        >
                            Tidak ada data eksemplar pada halaman ini.
                        </div>
                    </div>

                    <div class="shrink-0 border-t border-gray-100 bg-white px-6 py-5">
                        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                            <button
                                type="button"
                                @click="closeBulkModal()"
                                class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-600 transition hover:bg-gray-50"
                            >
                                Batal
                            </button>

                            <button
                                type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-700/20 transition hover:bg-emerald-800"
                            >
                                <span class="material-symbols-outlined text-[18px]">task_alt</span>
                                Lanjutkan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div
            x-show="approvalModalOpen"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-950/70 px-4 py-8 backdrop-blur-md"
        >
            <div
                @click.outside="closeApprovalModal()"
                x-show="approvalModalOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                class="w-full max-w-lg overflow-hidden rounded-[2rem] bg-white shadow-[0_30px_90px_rgba(15,23,42,0.35)]"
            >
                <div class="soft-confirm-bg relative overflow-hidden px-7 pb-6 pt-7">
                    <button
                        type="button"
                        @click="closeApprovalModal()"
                        class="absolute right-5 top-5 flex h-10 w-10 items-center justify-center rounded-2xl bg-white/80 text-gray-500 shadow-sm transition hover:bg-white hover:text-gray-800"
                    >
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>

                    <div
                        class="flex h-16 w-16 items-center justify-center rounded-3xl text-white shadow-lg"
                        :class="approvalIconClass()"
                    >
                        <span class="material-symbols-outlined text-[34px]" x-text="approval.icon"></span>
                    </div>

                    <p
                        class="mt-5 text-xs font-extrabold uppercase tracking-[0.22em]"
                        :class="approvalTextClass()"
                        x-text="approval.type === 'info' ? 'Informasi' : 'Persetujuan'"
                    ></p>

                    <h3 class="mt-2 text-2xl font-black leading-tight text-gray-950" x-text="approval.title"></h3>

                    <p class="mt-1 text-sm font-semibold text-gray-500" x-text="approval.subtitle"></p>

                    <p class="mt-4 text-sm leading-6 text-gray-600" x-text="approval.message"></p>
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-gray-100 bg-white px-7 py-5 sm:flex-row sm:justify-end">
                    <button
                        type="button"
                        @click="closeApprovalModal()"
                        class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-600 transition hover:bg-gray-50"
                    >
                        Batal
                    </button>

                    <button
                        type="button"
                        @click="submitApproval()"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl px-5 py-3 text-sm font-bold text-white shadow-lg transition"
                        :class="approvalButtonClass()"
                    >
                        <span class="material-symbols-outlined text-[18px]" x-text="approval.icon"></span>
                        <span x-text="approval.confirm_text"></span>
                    </button>
                </div>
            </div>
        </div>

        <script>
            function bookItemManager(initialItems) {
                return {
                    bulkModalOpen: false,
                    approvalModalOpen: false,
                    bulkError: '',
                    selectedIds: [],
                    items: initialItems || [],
                    approval: {
                        type: 'info',
                        action: '',
                        title: '',
                        subtitle: '',
                        message: '',
                        confirm_text: 'Mengerti',
                        tone: 'emerald',
                        icon: 'info',
                    },

                    openBulkModal() {
                        this.bulkModalOpen = true;
                        this.bulkError = '';
                        this.selectedIds = [];
                    },

                    closeBulkModal() {
                        this.bulkModalOpen = false;
                        this.bulkError = '';
                        this.selectedIds = [];
                    },

                    closeAllModals() {
                        this.closeBulkModal();
                        this.closeApprovalModal();
                    },

                    eligibleItems() {
                        return this.items.filter((item) => item.can_select);
                    },

                    borrowedItems() {
                        return this.items.filter((item) => item.is_active_loan);
                    },

                    selectedItems() {
                        const selectedSet = new Set(this.selectedIds.map((id) => String(id)));

                        return this.items.filter((item) => selectedSet.has(String(item.id)));
                    },

                    selectAllEligible() {
                        this.selectedIds = this.eligibleItems().map((item) => String(item.id));
                        this.bulkError = '';
                    },

                    clearSelected() {
                        this.selectedIds = [];
                        this.bulkError = '';
                    },

                    requestBulkProcess() {
                        this.bulkError = '';

                        this.selectedIds = [...new Set(
                            this.selectedIds
                                .map((id) => String(id))
                                .filter((id) => id !== '')
                        )];

                        if (this.selectedIds.length < 1) {
                            this.bulkError = 'Pilih minimal satu copy yang bisa diproses.';
                            return;
                        }

                        const selected = this.selectedItems();

                        if (selected.length < 1) {
                            this.bulkError = 'Copy yang dipilih tidak valid. Silakan pilih ulang.';
                            return;
                        }

                        const invalidSelected = selected.filter((item) => !item.can_select);

                        if (invalidSelected.length > 0) {
                            this.bulkError = 'Ada copy yang tidak bisa diproses. Silakan pilih ulang.';
                            return;
                        }

                        const permanentCount = selected.filter((item) => !item.has_loan_history).length;
                        const outOfStockCount = selected.filter((item) => item.has_loan_history && !item.is_out_of_stock).length;

                        let message = this.selectedIds.length + ' copy akan diproses. ';

                        if (permanentCount > 0) {
                            message += permanentCount + ' copy yang belum pernah dipinjam akan dihapus permanen. ';
                        }

                        if (outOfStockCount > 0) {
                            message += outOfStockCount + ' copy yang pernah dipinjam akan dikeluarkan dari stok.';
                        }

                        this.openApproval({
                            type: 'bulk',
                            action: '',
                            title: 'Proses copy terpilih?',
                            subtitle: this.selectedIds.length + ' copy dipilih',
                            message: message,
                            confirm_text: 'Ya, Proses',
                            tone: 'emerald',
                            icon: 'task_alt',
                        });
                    },

                    openApprovalFromButton(button) {
                        try {
                            const payload = JSON.parse(atob(button.dataset.approvalPayload || ''));
                            this.openApproval(payload);
                        } catch (error) {
                            console.error(error);

                            this.openApproval({
                                type: 'info',
                                action: '',
                                title: 'Data gagal dibaca',
                                subtitle: '',
                                message: 'Silakan refresh halaman lalu coba lagi.',
                                confirm_text: 'Mengerti',
                                tone: 'gray',
                                icon: 'info',
                            });
                        }
                    },

                    openApproval(payload) {
                        this.approval = payload;
                        this.approvalModalOpen = true;
                    },

                    closeApprovalModal() {
                        this.approvalModalOpen = false;

                        this.approval = {
                            type: 'info',
                            action: '',
                            title: '',
                            subtitle: '',
                            message: '',
                            confirm_text: 'Mengerti',
                            tone: 'emerald',
                            icon: 'info',
                        };
                    },

                    submitApproval() {
                        if (this.approval.type === 'info') {
                            this.closeApprovalModal();
                            return;
                        }

                        if (this.approval.type === 'bulk') {
                            this.submitBulkSelectedIds();
                            return;
                        }

                        if (this.approval.type === 'restore') {
                            this.$nextTick(() => {
                                this.$refs.restoreStockForm.setAttribute('action', this.approval.action);
                                this.$refs.restoreStockForm.submit();
                            });

                            return;
                        }

                        if (this.approval.type === 'single') {
                            this.$nextTick(() => {
                                this.$refs.singleProcessForm.setAttribute('action', this.approval.action);
                                this.$refs.singleProcessForm.submit();
                            });
                        }
                    },

                    submitBulkSelectedIds() {
                        this.selectedIds = [...new Set(
                            this.selectedIds
                                .map((id) => String(id))
                                .filter((id) => id !== '')
                        )];

                        if (this.selectedIds.length < 1) {
                            this.closeApprovalModal();
                            this.bulkError = 'Pilih minimal satu copy yang bisa diproses.';
                            this.bulkModalOpen = true;
                            return;
                        }

                        const form = this.$refs.bulkSubmitForm;

                        form.querySelectorAll('input[data-bulk-dynamic="1"]').forEach((input) => {
                            input.remove();
                        });

                        this.selectedIds.forEach((id) => {
                            const input = document.createElement('input');

                            input.type = 'hidden';
                            input.name = 'book_item_ids[]';
                            input.value = String(id);
                            input.setAttribute('data-bulk-dynamic', '1');

                            form.appendChild(input);
                        });

                        form.submit();
                    },

                    badgeText(item) {
                        if (item.is_active_loan) {
                            return 'Sedang Dipinjam';
                        }

                        if (item.is_out_of_stock && item.has_loan_history) {
                            return 'Keluar Stok';
                        }

                        if (item.has_loan_history) {
                            return 'Pernah Dipinjam';
                        }

                        return 'Bisa Hapus';
                    },

                    badgeIcon(item) {
                        if (item.is_active_loan) {
                            return 'schedule';
                        }

                        if (item.is_out_of_stock && item.has_loan_history) {
                            return 'inventory_2';
                        }

                        if (item.has_loan_history) {
                            return 'history';
                        }

                        return 'check_circle';
                    },

                    badgeClass(item) {
                        if (item.is_active_loan) {
                            return 'border-amber-200 bg-amber-50 text-amber-700';
                        }

                        if (item.is_out_of_stock && item.has_loan_history) {
                            return 'border-gray-200 bg-gray-50 text-gray-600';
                        }

                        if (item.has_loan_history) {
                            return 'border-sky-200 bg-sky-50 text-sky-700';
                        }

                        return 'border-emerald-200 bg-emerald-50 text-emerald-700';
                    },

                    approvalIconClass() {
                        if (this.approval.tone === 'red') {
                            return 'bg-red-600 shadow-red-600/25';
                        }

                        if (this.approval.tone === 'amber') {
                            return 'bg-amber-500 shadow-amber-500/25';
                        }

                        if (this.approval.tone === 'sky') {
                            return 'bg-sky-600 shadow-sky-600/25';
                        }

                        if (this.approval.tone === 'gray') {
                            return 'bg-gray-600 shadow-gray-600/25';
                        }

                        return 'bg-emerald-600 shadow-emerald-600/25';
                    },

                    approvalTextClass() {
                        if (this.approval.tone === 'red') {
                            return 'text-red-600';
                        }

                        if (this.approval.tone === 'amber') {
                            return 'text-amber-600';
                        }

                        if (this.approval.tone === 'sky') {
                            return 'text-sky-600';
                        }

                        if (this.approval.tone === 'gray') {
                            return 'text-gray-600';
                        }

                        return 'text-emerald-600';
                    },

                    approvalButtonClass() {
                        if (this.approval.tone === 'red') {
                            return 'bg-red-600 shadow-red-600/25 hover:bg-red-700';
                        }

                        if (this.approval.tone === 'amber') {
                            return 'bg-ambser-500 shadow-amber-500/25 hover:bg-amber-600';
                        }

                        if (this.approval.tone === 'sky') {
                            return 'bg-sky-600 shadow-sky-600/25 hover:bg-sky-700';
                        }

                        if (this.approval.tone === 'gray') {
                            return 'bg-gray-600 shadow-gray-600/25 hover:bg-gray-700';
                        }

                        return 'bg-emerald-700 shadow-emerald-700/25 hover:bg-emerald-800';
                    },
                };
            }
        </script>
    </div>
</x-app-layout>