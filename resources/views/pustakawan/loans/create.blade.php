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
                    Buat Peminjaman Biasa
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Tentukan jumlah baris, pilih judul buku, lalu pilih copy/eksemplar yang akan dipinjam.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                @php
                    $classBulkRoute = null;

                    if (\Illuminate\Support\Facades\Route::has('loans.class_bulk.create')) {
                        $classBulkRoute = route('loans.class_bulk.create');
                    } elseif (\Illuminate\Support\Facades\Route::has('loans.class_bulk.create')) {
                        $classBulkRoute = route('loans.class_bulk.create');
                    } elseif (\Illuminate\Support\Facades\Route::has('loans.class_bulk.create')) {
                        $classBulkRoute = route('loans.class_bulk.create');
                    }
                @endphp

                @if($classBulkRoute)
                    <a href="{{ $classBulkRoute }}"
                       class="inline-flex items-center justify-center gap-2 rounded-2xl bg-amber-500 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-amber-600">
                        <span class="material-symbols-outlined text-[18px]">groups</span>
                        Peminjaman Kelas
                    </a>
                @endif

                <a href="{{ route('loans.index') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-700 shadow-sm transition hover:bg-gray-50">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                    Kembali
                </a>
            </div>
        </div>
    </x-slot>

    @php
        $normalMaxLoanItems = max(1, (int) ($normalMaxLoanItems ?? 3));
        $loanDurationDays = max(1, (int) ($loanDurationDays ?? 7));

        $loanDateDefault = old('loan_date', now()->format('Y-m-d'));
        $dueDateDefault = old('due_date', now()->addDays($loanDurationDays)->format('Y-m-d'));

        $borrowedIds = collect($borrowedBookItemIds ?? [])
            ->map(fn ($id) => (int) $id)
            ->toArray();

        $oldSelectedBookItemIds = collect(old('book_item_ids', []))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->toArray();

        $initialRowCount = old('row_count')
            ? (int) old('row_count')
            : max(1, count($oldSelectedBookItemIds));

        $initialRowCount = max(1, min($normalMaxLoanItems, $initialRowCount));

        $availableBookItems = collect($bookItems ?? [])
            ->filter(function ($bookItem) use ($borrowedIds) {
                $bookItemId = (int) ($bookItem->id ?? 0);
                $status = strtolower((string) ($bookItem->status ?? ''));
                $condition = strtolower((string) ($bookItem->condition ?? 'baik'));

                return $bookItemId > 0
                    && $bookItem->book
                    && $status === 'tersedia'
                    && ! in_array($bookItemId, $borrowedIds, true)
                    && ! in_array($condition, ['hilang', 'rusak berat'], true);
            })
            ->values();

        $booksPayload = $availableBookItems
            ->groupBy(fn ($bookItem) => (int) $bookItem->book_id)
            ->map(function ($items, $bookId) {
                $firstItem = $items->first();
                $book = $firstItem->book;

                return [
                    'id' => (int) $bookId,
                    'title' => (string) ($book->title ?? '-'),
                    'author' => (string) ($book->author ?? '-'),
                    'publisher' => (string) ($book->publisher ?? ''),
                    'year' => (string) ($book->publication_year ?? $book->year ?? ''),
                    'copies' => $items
                        ->sortBy([
                            ['copy_number', 'asc'],
                            ['item_code', 'asc'],
                        ])
                        ->map(function ($item) {
                            return [
                                'id' => (int) $item->id,
                                'item_code' => (string) ($item->item_code ?? '-'),
                                'copy_number' => (string) ($item->copy_number ?? '-'),
                                'condition' => (string) ($item->condition ?? 'baik'),
                            ];
                        })
                        ->values()
                        ->toArray(),
                ];
            })
            ->sortBy('title')
            ->values()
            ->toArray();

        $oldSelectedRows = [];

        for ($i = 0; $i < $initialRowCount; $i++) {
            $oldSelectedRows[] = [
                'bookItemId' => $oldSelectedBookItemIds[$i] ?? '',
            ];
        }
    @endphp

    <div
        x-data="regularLoanCreateForm({
            maxRows: {{ $normalMaxLoanItems }},
            initialRowCount: {{ $initialRowCount }},
            oldRows: @js($oldSelectedRows),
            books: @js($booksPayload),
        })"
        x-init="init()"
        class="min-h-screen bg-gradient-to-br from-slate-50 via-emerald-50/40 to-sky-50/40 py-10"
    >
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

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

            <form method="POST" action="{{ route('loans.store') }}" class="space-y-6" @submit="prepareSubmit($event)">
                @csrf

                <input type="hidden" name="row_count" :value="rows.length">

                <div class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/90 shadow-[0_18px_50px_rgba(15,23,42,0.06)] backdrop-blur-xl">
                    <div class="relative overflow-hidden bg-gradient-to-r from-emerald-700 to-teal-500 p-6">
                        <div class="absolute -right-16 -top-20 h-52 w-52 rounded-full bg-white/10 blur-2xl"></div>
                        <div class="absolute -left-20 bottom-0 h-48 w-48 rounded-full bg-emerald-200/20 blur-2xl"></div>

                        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <h3 class="text-lg font-extrabold text-white">
                                    Data Peminjaman
                                </h3>

                                <p class="mt-1 text-sm text-emerald-50">
                                    Maksimal {{ $normalMaxLoanItems }} eksemplar untuk peminjaman biasa. Batas ini mengikuti pengaturan Admin IT.
                                </p>
                            </div>

                            <div class="rounded-2xl border border-white/20 bg-white/15 px-4 py-3 text-white">
                                <p class="text-xs text-emerald-50">
                                    Durasi default
                                </p>

                                <p class="text-sm font-bold">
                                    {{ $loanDurationDays }} hari
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-6 p-6 lg:grid-cols-3">
                        <div class="lg:col-span-1">
                            <label for="member_id" class="block text-sm font-bold text-gray-700">
                                Anggota Peminjam
                            </label>

                            <select
                                id="member_id"
                                name="member_id"
                                class="mt-2 block w-full rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                required
                            >
                                <option value="">Pilih anggota</option>

                                @foreach($members ?? [] as $member)
                                    <option value="{{ $member->id }}" @selected((string) old('member_id') === (string) $member->id)>
                                        {{ $member->name }}
                                        —
                                        {{ $member->nis_nip ?? $member->member_code ?? '-' }}
                                        @if($member->studentClass)
                                            —
                                            {{ $member->studentClass->class_name }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="loan_date" class="block text-sm font-bold text-gray-700">
                                Tanggal Pinjam
                            </label>

                            <input
                                id="loan_date"
                                type="date"
                                name="loan_date"
                                value="{{ $loanDateDefault }}"
                                class="mt-2 block w-full rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                required
                            >
                        </div>

                        <div>
                            <label for="due_date" class="block text-sm font-bold text-gray-700">
                                Batas Kembali
                            </label>

                            <input
                                id="due_date"
                                type="date"
                                name="due_date"
                                value="{{ $dueDateDefault }}"
                                class="mt-2 block w-full rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                required
                            >
                        </div>

                        <div class="lg:col-span-3">
                            <label for="notes" class="block text-sm font-bold text-gray-700">
                                Catatan Transaksi
                                <span class="text-xs font-semibold text-gray-400">(opsional)</span>
                            </label>

                            <textarea
                                id="notes"
                                name="notes"
                                rows="3"
                                placeholder="Contoh: Buku dipinjam untuk tugas mata pelajaran / catatan khusus pustakawan."
                                class="mt-2 block w-full rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                            >{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/90 shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-5">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <h3 class="text-lg font-extrabold text-gray-900">
                                    Input Buku yang Dipinjam
                                </h3>

                                <p class="mt-1 text-sm text-gray-500">
                                    Tentukan jumlah baris dulu, lalu pilih judul dan copy/eksemplar pada setiap baris.
                                </p>
                            </div>

                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                                <div>
                                    <label for="row_count_selector" class="block text-xs font-bold uppercase tracking-wider text-gray-500">
                                        Jumlah Baris
                                    </label>

                                    <select
                                        id="row_count_selector"
                                        x-model.number="selectedRowCount"
                                        @change="setRowCount(selectedRowCount)"
                                        class="mt-1 block w-full rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm font-bold focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 sm:w-40"
                                    >
                                        <template x-for="number in maxRows" :key="number">
                                            <option :value="number" x-text="number + ' baris'"></option>
                                        </template>
                                    </select>
                                </div>

                                <button
                                    type="button"
                                    @click="addRow()"
                                    :disabled="rows.length >= maxRows"
                                    class="mt-5 inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-800 disabled:cursor-not-allowed disabled:bg-gray-300"
                                >
                                    <span class="material-symbols-outlined text-[18px]">add</span>
                                    Tambah Baris
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <template x-if="books.length === 0">
                            <div class="rounded-3xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
                                <p class="font-extrabold">
                                    Belum ada eksemplar yang bisa dipinjam
                                </p>

                                <p class="mt-1">
                                    Pastikan ada eksemplar dengan status tersedia dan kondisi bukan hilang/rusak berat.
                                </p>
                            </div>
                        </template>

                        <div class="space-y-4">
                            <template x-for="(row, index) in rows" :key="row.uid">
                                <div class="rounded-3xl border border-gray-100 bg-slate-50 p-4">
                                    <div class="mb-4 flex items-center justify-between gap-3">
                                        <div>
                                            <p class="text-sm font-extrabold text-gray-900">
                                                Buku ke-<span x-text="index + 1"></span>
                                            </p>

                                            <p class="text-xs text-gray-500">
                                                Pilih judul terlebih dahulu, lalu pilih copy/eksemplar.
                                            </p>
                                        </div>

                                        <button
                                            type="button"
                                            @click="removeRow(index)"
                                            x-show="rows.length > 1"
                                            class="inline-flex items-center justify-center gap-1 rounded-2xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-bold text-red-700 hover:bg-red-100"
                                        >
                                            <span class="material-symbols-outlined text-[16px]">delete</span>
                                            Hapus
                                        </button>
                                    </div>

                                    <div class="grid gap-4 lg:grid-cols-2">
                                        <div class="relative">
                                            <label class="block text-sm font-bold text-gray-700">
                                                Judul Buku
                                            </label>

                                            <input
                                                type="text"
                                                x-model="row.bookSearch"
                                                @focus="row.dropdownOpen = true"
                                                @input="row.dropdownOpen = true; row.selectedBookId = null; row.selectedBookItemId = ''; row.selectedBookTitle = ''"
                                                placeholder="Ketik judul buku..."
                                                class="mt-2 block w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                                autocomplete="off"
                                            >

                                            <div
                                                x-show="row.dropdownOpen"
                                                x-cloak
                                                @click.outside="row.dropdownOpen = false"
                                                class="absolute z-30 mt-2 max-h-64 w-full overflow-y-auto rounded-2xl border border-gray-100 bg-white shadow-xl"
                                            >
                                                <template x-for="book in filteredBooks(row.bookSearch)" :key="book.id">
                                                    <button
                                                        type="button"
                                                        @click="selectBook(index, book)"
                                                        class="block w-full border-b border-gray-50 px-4 py-3 text-left hover:bg-emerald-50"
                                                    >
                                                        <p class="text-sm font-extrabold text-gray-900" x-text="book.title"></p>

                                                        <p class="mt-1 text-xs text-gray-500">
                                                            <span x-text="book.author || '-'"></span>
                                                            <span> — </span>
                                                            <span x-text="book.copies.length + ' copy tersedia'"></span>
                                                        </p>
                                                    </button>
                                                </template>

                                                <template x-if="filteredBooks(row.bookSearch).length === 0">
                                                    <div class="px-4 py-4 text-sm text-gray-500">
                                                        Judul tidak ditemukan atau tidak ada copy yang tersedia.
                                                    </div>
                                                </template>
                                            </div>

                                            <p class="mt-2 text-xs text-gray-500">
                                                Judul terpilih:
                                                <span class="font-bold text-gray-800" x-text="row.selectedBookTitle || '-'"></span>
                                            </p>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-bold text-gray-700">
                                                Copy / Eksemplar
                                            </label>

                                            <select
                                                name="book_item_ids[]"
                                                x-model="row.selectedBookItemId"
                                                :disabled="!row.selectedBookId"
                                                class="mt-2 block w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 disabled:cursor-not-allowed disabled:bg-gray-100"
                                                required
                                            >
                                                <option value="">Pilih copy/eksemplar</option>

                                                <template x-for="copy in copiesForBook(row.selectedBookId)" :key="copy.id">
                                                    <option
                                                        :value="copy.id"
                                                        :disabled="isCopySelectedInOtherRow(copy.id, index)"
                                                        x-text="'Copy ' + copy.copy_number + ' — ' + copy.item_code + ' — ' + copy.condition"
                                                    ></option>
                                                </template>
                                            </select>

                                            <p class="mt-2 text-xs text-gray-500">
                                                Copy yang sudah dipilih di baris lain otomatis tidak bisa dipilih ulang.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="mt-6 grid gap-4 lg:grid-cols-3">
                            <div class="rounded-3xl border border-emerald-100 bg-emerald-50 px-5 py-4">
                                <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">
                                    Terpilih
                                </p>

                                <p class="mt-2 text-2xl font-extrabold text-emerald-900">
                                    <span x-text="selectedCount()"></span>
                                    <span class="text-sm">eksemplar</span>
                                </p>
                            </div>

                            <div class="rounded-3xl border border-sky-100 bg-sky-50 px-5 py-4">
                                <p class="text-xs font-bold uppercase tracking-wider text-sky-700">
                                    Batas Maksimal
                                </p>

                                <p class="mt-2 text-2xl font-extrabold text-sky-900">
                                    {{ $normalMaxLoanItems }}
                                    <span class="text-sm">eksemplar</span>
                                </p>
                            </div>

                            <div class="rounded-3xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
                                <p class="font-extrabold">
                                    Validasi Aman
                                </p>

                                <p class="mt-1">
                                    Controller tetap mengecek ulang status, kondisi, dan transaksi aktif sebelum menyimpan.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <a href="{{ route('loans.index') }}"
                       class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-600 transition hover:bg-gray-50">
                        Batal
                    </a>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-700/20 transition hover:bg-emerald-800"
                    >
                        <span class="material-symbols-outlined text-[18px]">save</span>
                        Simpan Peminjaman
                    </button>
                </div>
            </form>
        </div>

        <script>
            function regularLoanCreateForm(config) {
                return {
                    maxRows: Number(config.maxRows || 3),
                    selectedRowCount: Number(config.initialRowCount || 1),
                    books: Array.isArray(config.books) ? config.books : [],
                    rows: [],

                    init() {
                        const oldRows = Array.isArray(config.oldRows) ? config.oldRows : [];

                        if (oldRows.length > 0) {
                            this.rows = oldRows.map((oldRow) => {
                                const bookItemId = oldRow.bookItemId ? String(oldRow.bookItemId) : '';
                                const book = this.findBookByCopyId(bookItemId);

                                return {
                                    uid: this.makeUid(),
                                    bookSearch: book ? book.title : '',
                                    selectedBookId: book ? book.id : null,
                                    selectedBookTitle: book ? book.title : '',
                                    selectedBookItemId: bookItemId,
                                    dropdownOpen: false,
                                };
                            });
                        }

                        if (this.rows.length === 0) {
                            this.setRowCount(this.selectedRowCount || 1);
                        }

                        this.selectedRowCount = this.rows.length;
                    },

                    makeUid() {
                        return Date.now().toString(36) + Math.random().toString(36).slice(2);
                    },

                    emptyRow() {
                        return {
                            uid: this.makeUid(),
                            bookSearch: '',
                            selectedBookId: null,
                            selectedBookTitle: '',
                            selectedBookItemId: '',
                            dropdownOpen: false,
                        };
                    },

                    setRowCount(count) {
                        count = Number(count || 1);
                        count = Math.max(1, Math.min(this.maxRows, count));

                        while (this.rows.length < count) {
                            this.rows.push(this.emptyRow());
                        }

                        while (this.rows.length > count) {
                            this.rows.pop();
                        }

                        this.selectedRowCount = this.rows.length;
                    },

                    addRow() {
                        if (this.rows.length >= this.maxRows) {
                            alert('Jumlah baris sudah mencapai batas maksimal dari Admin IT.');
                            return;
                        }

                        this.rows.push(this.emptyRow());
                        this.selectedRowCount = this.rows.length;
                    },

                    removeRow(index) {
                        if (this.rows.length <= 1) {
                            return;
                        }

                        this.rows.splice(index, 1);
                        this.selectedRowCount = this.rows.length;
                    },

                    normalizedText(value) {
                        return String(value || '').toLowerCase().trim();
                    },

                    filteredBooks(search) {
                        const keyword = this.normalizedText(search);

                        if (!keyword) {
                            return this.books.slice(0, 20);
                        }

                        return this.books
                            .filter((book) => {
                                return this.normalizedText(book.title).includes(keyword)
                                    || this.normalizedText(book.author).includes(keyword);
                            })
                            .slice(0, 20);
                    },

                    selectBook(index, book) {
                        this.rows[index].selectedBookId = book.id;
                        this.rows[index].selectedBookTitle = book.title;
                        this.rows[index].bookSearch = book.title;
                        this.rows[index].selectedBookItemId = '';
                        this.rows[index].dropdownOpen = false;
                    },

                    copiesForBook(bookId) {
                        if (!bookId) {
                            return [];
                        }

                        const book = this.books.find((item) => Number(item.id) === Number(bookId));

                        return book && Array.isArray(book.copies) ? book.copies : [];
                    },

                    isCopySelectedInOtherRow(copyId, rowIndex) {
                        return this.rows.some((row, index) => {
                            return index !== rowIndex && String(row.selectedBookItemId) === String(copyId);
                        });
                    },

                    findBookByCopyId(copyId) {
                        if (!copyId) {
                            return null;
                        }

                        return this.books.find((book) => {
                            return Array.isArray(book.copies)
                                && book.copies.some((copy) => String(copy.id) === String(copyId));
                        }) || null;
                    },

                    selectedCount() {
                        return this.rows.filter((row) => String(row.selectedBookItemId || '').trim() !== '').length;
                    },

                    prepareSubmit(event) {
                        const selectedIds = this.rows
                            .map((row) => String(row.selectedBookItemId || '').trim())
                            .filter((id) => id !== '');

                        if (selectedIds.length === 0) {
                            event.preventDefault();
                            alert('Pilih minimal satu copy/eksemplar buku.');
                            return;
                        }

                        const uniqueIds = new Set(selectedIds);

                        if (uniqueIds.size !== selectedIds.length) {
                            event.preventDefault();
                            alert('Ada copy/eksemplar yang dipilih lebih dari satu kali.');
                            return;
                        }

                        if (selectedIds.length > this.maxRows) {
                            event.preventDefault();
                            alert('Jumlah eksemplar melebihi batas maksimal dari Admin IT.');
                            return;
                        }

                        const confirmed = confirm('Simpan transaksi peminjaman ini?');

                        if (!confirmed) {
                            event.preventDefault();
                        }
                    },
                };
            }
        </script>
    </div>
</x-app-layout>