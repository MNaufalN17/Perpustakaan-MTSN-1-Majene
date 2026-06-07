<x-app-layout>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-700">
                    Transaksi Perpustakaan
                </p>

                <h2 class="mt-1 text-xl font-bold text-gray-900">
                    Buat Peminjaman Kelas
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Untuk peminjaman banyak eksemplar dari satu judul buku oleh satu perwakilan kelas.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('loans.create') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-800">
                    <span class="material-symbols-outlined text-[18px]">person</span>
                    Peminjaman Biasa
                </a>

                <a href="{{ route('loans.index') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-700 shadow-sm transition hover:bg-gray-50">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                    Kembali
                </a>
            </div>
        </div>
    </x-slot>

    @php
        $maxClassLoanItems = max(1, (int) ($maxClassLoanItems ?? 40));
        $loanDurationDays = max(1, (int) ($loanDurationDays ?? 7));

        $loanDateDefault = old('loan_date', now()->format('Y-m-d'));
        $dueDateDefault = old('due_date', now()->addDays($loanDurationDays)->format('Y-m-d'));

        $oldSelectedBookItemIds = collect(old('book_item_ids', []))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->toArray();

        $oldBookId = old('book_id');

        $availableBookItems = collect($bookItems ?? [])
            ->filter(function ($bookItem) {
                $bookItemId = (int) ($bookItem->id ?? 0);
                $status = strtolower((string) ($bookItem->status ?? ''));
                $condition = strtolower((string) ($bookItem->condition ?? 'baik'));

                return $bookItemId > 0
                    && $bookItem->book
                    && $status === 'tersedia'
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

        $classBulkStoreRoute = null;

        if (\Illuminate\Support\Facades\Route::has('loans.class-bulk.store')) {
            $classBulkStoreRoute = route('loans.class-bulk.store');
        } elseif (\Illuminate\Support\Facades\Route::has('loans.classBulk.store')) {
            $classBulkStoreRoute = route('loans.classBulk.store');
        } elseif (\Illuminate\Support\Facades\Route::has('loans.class_bulk.store')) {
            $classBulkStoreRoute = route('loans.class_bulk.store');
        }
    @endphp

    <div
        x-data="classBulkLoanCreateForm({
            maxItems: {{ $maxClassLoanItems }},
            books: @js($booksPayload),
            oldBookId: @js($oldBookId),
            oldSelectedBookItemIds: @js($oldSelectedBookItemIds),
        })"
        x-init="init()"
        class="min-h-screen bg-gradient-to-br from-slate-50 via-amber-50/50 to-emerald-50/40 py-10"
    >
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

            @if(! $classBulkStoreRoute)
                <div class="rounded-3xl border border-red-200 bg-red-50 px-5 py-4 text-red-800 shadow-sm">
                    <p class="font-extrabold">
                        Route peminjaman kelas belum ditemukan
                    </p>

                    <p class="mt-1 text-sm">
                        Pastikan route store untuk peminjaman kelas memiliki salah satu nama:
                        <strong>loans.class-bulk.store</strong>,
                        <strong>loans.classBulk.store</strong>, atau
                        <strong>loans.class_bulk.store</strong>.
                    </p>
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

            <form method="POST"
                  action="{{ $classBulkStoreRoute ?? '#' }}"
                  class="space-y-6"
                  @submit="prepareSubmit($event)"
            >
                @csrf

                <input type="hidden" name="book_id" :value="selectedBookId">

                <template x-for="copyId in selectedCopyIds" :key="'selected-copy-' + copyId">
                    <input type="hidden" name="book_item_ids[]" :value="copyId">
                </template>

                <div class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/90 shadow-[0_18px_50px_rgba(15,23,42,0.06)] backdrop-blur-xl">
                    <div class="relative overflow-hidden bg-gradient-to-r from-amber-600 to-orange-500 p-6">
                        <div class="absolute -right-16 -top-20 h-52 w-52 rounded-full bg-white/10 blur-2xl"></div>
                        <div class="absolute -left-20 bottom-0 h-48 w-48 rounded-full bg-amber-200/20 blur-2xl"></div>

                        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <h3 class="text-lg font-extrabold text-white">
                                    Data Perwakilan Kelas
                                </h3>

                                <p class="mt-1 text-sm text-amber-50">
                                    Sistem akan menyimpan transaksi ini sebagai <strong>loan_type = class_bulk</strong>, bukan menebak dari jumlah buku.
                                </p>
                            </div>

                            <div class="rounded-2xl border border-white/20 bg-white/15 px-4 py-3 text-white">
                                <p class="text-xs text-amber-50">
                                    Batas maksimal
                                </p>

                                <p class="text-sm font-bold">
                                    {{ $maxClassLoanItems }} eksemplar
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-6 p-6 lg:grid-cols-4">
                        <div class="lg:col-span-2">
                            <label for="member_id" class="block text-sm font-bold text-gray-700">
                                Perwakilan Peminjam
                            </label>

                            <select
                                id="member_id"
                                name="member_id"
                                class="mt-2 block w-full rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-100"
                                required
                            >
                                <option value="">Pilih siswa/perwakilan</option>

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

                            <p class="mt-2 text-xs text-gray-500">
                                Nama ini menjadi penanggung jawab transaksi peminjaman kelas.
                            </p>
                        </div>

                        <div class="lg:col-span-2">
                            <label for="student_class_id" class="block text-sm font-bold text-gray-700">
                                Kelas
                                <span class="text-xs font-semibold text-gray-400">(opsional jika sudah ada di data anggota)</span>
                            </label>

                            <select
                                id="student_class_id"
                                name="student_class_id"
                                class="mt-2 block w-full rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-100"
                            >
                                <option value="">Ikuti kelas anggota / Tidak ada</option>

                                @foreach($studentClasses ?? [] as $studentClass)
                                    <option value="{{ $studentClass->id }}" @selected((string) old('student_class_id') === (string) $studentClass->id)>
                                        {{ $studentClass->class_name ?? $studentClass->name ?? ('Kelas #' . $studentClass->id) }}
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
                                class="mt-2 block w-full rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-100"
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
                                class="mt-2 block w-full rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-100"
                                required
                            >
                        </div>

                        <div class="lg:col-span-2">
                            <label for="notes" class="block text-sm font-bold text-gray-700">
                                Catatan Transaksi
                                <span class="text-xs font-semibold text-gray-400">(opsional)</span>
                            </label>

                            <input
                                id="notes"
                                type="text"
                                name="notes"
                                value="{{ old('notes') }}"
                                placeholder="Contoh: Buku dipakai untuk mata pelajaran Bahasa Indonesia."
                                class="mt-2 block w-full rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-100"
                            >
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/90 shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-5">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <h3 class="text-lg font-extrabold text-gray-900">
                                    Pilih Judul dan Banyak Eksemplar
                                </h3>

                                <p class="mt-1 text-sm text-gray-500">
                                    Untuk peminjaman kelas, satu transaksi memakai satu judul buku dengan banyak copy/eksemplar.
                                </p>
                            </div>

                            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3">
                                <p class="text-xs font-bold uppercase tracking-wider text-amber-700">
                                    Terpilih
                                </p>

                                <p class="text-sm font-extrabold text-amber-900">
                                    <span x-text="selectedCopyIds.length"></span> eksemplar
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-6 p-6 lg:grid-cols-3">
                        <div class="relative lg:col-span-2">
                            <label class="block text-sm font-bold text-gray-700">
                                Cari Judul Buku
                            </label>

                            <input
                                type="text"
                                x-model="bookSearch"
                                @focus="dropdownOpen = true"
                                @input="dropdownOpen = true"
                                placeholder="Ketik judul atau penulis..."
                                class="mt-2 block w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-100"
                                autocomplete="off"
                            >

                            <div
                                x-show="dropdownOpen"
                                x-cloak
                                @click.outside="dropdownOpen = false"
                                class="absolute z-30 mt-2 max-h-72 w-full overflow-y-auto rounded-2xl border border-gray-100 bg-white shadow-xl"
                            >
                                <template x-for="book in filteredBooks()" :key="book.id">
                                    <button
                                        type="button"
                                        @click="selectBook(book)"
                                        class="block w-full border-b border-gray-50 px-4 py-3 text-left hover:bg-amber-50"
                                    >
                                        <p class="text-sm font-extrabold text-gray-900" x-text="book.title"></p>

                                        <p class="mt-1 text-xs text-gray-500">
                                            <span x-text="book.author || '-'"></span>
                                            <span> — </span>
                                            <span x-text="book.copies.length + ' copy tersedia'"></span>
                                        </p>
                                    </button>
                                </template>

                                <template x-if="filteredBooks().length === 0">
                                    <div class="px-4 py-4 text-sm text-gray-500">
                                        Judul tidak ditemukan atau tidak punya copy tersedia.
                                    </div>
                                </template>
                            </div>

                            <p class="mt-2 text-xs text-gray-500">
                                Judul terpilih:
                                <span class="font-bold text-gray-800" x-text="selectedBook ? selectedBook.title : '-'"></span>
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700">
                                Jumlah Eksemplar
                            </label>

                            <input
                                type="number"
                                min="1"
                                :max="maxAllowedForSelectedBook()"
                                x-model.number="wantedCount"
                                class="mt-2 block w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-100"
                                placeholder="Contoh: 30"
                            >

                            <button
                                type="button"
                                @click="autoSelectCopies()"
                                :disabled="!selectedBook"
                                class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-amber-500 px-5 py-3 text-sm font-bold text-white transition hover:bg-amber-600 disabled:cursor-not-allowed disabled:bg-gray-300"
                            >
                                <span class="material-symbols-outlined text-[18px]">done_all</span>
                                Pilih Otomatis
                            </button>

                            <p class="mt-2 text-xs text-gray-500">
                                Sistem akan memilih copy tersedia sesuai urutan copy number/kode eksemplar.
                            </p>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 p-6">
                        <template x-if="!selectedBook">
                            <div class="rounded-3xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
                                <p class="font-extrabold">
                                    Pilih judul buku terlebih dahulu
                                </p>

                                <p class="mt-1">
                                    Setelah judul dipilih, daftar copy/eksemplar akan muncul di bawah.
                                </p>
                            </div>
                        </template>

                        <template x-if="selectedBook">
                            <div>
                                <div class="mb-4 grid gap-4 lg:grid-cols-3">
                                    <div class="rounded-3xl border border-emerald-100 bg-emerald-50 px-5 py-4">
                                        <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">
                                            Copy Tersedia
                                        </p>

                                        <p class="mt-2 text-2xl font-extrabold text-emerald-900">
                                            <span x-text="selectedBook.copies.length"></span>
                                            <span class="text-sm">eksemplar</span>
                                        </p>
                                    </div>

                                    <div class="rounded-3xl border border-amber-100 bg-amber-50 px-5 py-4">
                                        <p class="text-xs font-bold uppercase tracking-wider text-amber-700">
                                            Dipilih
                                        </p>

                                        <p class="mt-2 text-2xl font-extrabold text-amber-900">
                                            <span x-text="selectedCopyIds.length"></span>
                                            <span class="text-sm">eksemplar</span>
                                        </p>
                                    </div>

                                    <div class="rounded-3xl border border-sky-100 bg-sky-50 px-5 py-4">
                                        <p class="text-xs font-bold uppercase tracking-wider text-sky-700">
                                            Batas Admin IT
                                        </p>

                                        <p class="mt-2 text-2xl font-extrabold text-sky-900">
                                            {{ $maxClassLoanItems }}
                                            <span class="text-sm">eksemplar</span>
                                        </p>
                                    </div>
                                </div>

                                <div class="overflow-x-auto rounded-3xl border border-gray-100">
                                    <table class="w-full min-w-[850px] divide-y divide-gray-100 text-left text-sm">
                                        <thead class="bg-slate-50 text-xs uppercase tracking-wider text-gray-500">
                                            <tr>
                                                <th class="w-[90px] px-5 py-4 text-center font-bold">Pilih</th>
                                                <th class="w-[170px] px-5 py-4 font-bold">Copy</th>
                                                <th class="px-5 py-4 font-bold">Kode Eksemplar</th>
                                                <th class="w-[180px] px-5 py-4 text-center font-bold">Kondisi</th>
                                            </tr>
                                        </thead>

                                        <tbody class="divide-y divide-gray-100 bg-white">
                                            <template x-for="copy in selectedBook.copies" :key="copy.id">
                                                <tr class="hover:bg-amber-50/40">
                                                    <td class="px-5 py-4 text-center align-middle">
                                                        <input
                                                            type="checkbox"
                                                            :value="copy.id"
                                                            :checked="isSelected(copy.id)"
                                                            @change="toggleCopy(copy.id)"
                                                            class="rounded border-gray-300 text-amber-600 focus:ring-amber-500"
                                                        >
                                                    </td>

                                                    <td class="px-5 py-4 align-middle">
                                                        <p class="font-extrabold text-gray-900">
                                                            Copy <span x-text="copy.copy_number"></span>
                                                        </p>
                                                    </td>

                                                    <td class="px-5 py-4 align-middle">
                                                        <p class="font-mono font-extrabold text-gray-900" x-text="copy.item_code"></p>
                                                    </td>

                                                    <td class="px-5 py-4 text-center align-middle">
                                                        <span class="inline-flex rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-bold text-sky-700" x-text="copy.condition"></span>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mt-5 flex flex-wrap gap-3">
                                    <button
                                        type="button"
                                        @click="selectAllAllowed()"
                                        class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-bold text-emerald-700 transition hover:bg-emerald-100"
                                    >
                                        <span class="material-symbols-outlined text-[18px]">select_all</span>
                                        Pilih Maksimal
                                    </button>

                                    <button
                                        type="button"
                                        @click="clearSelection()"
                                        class="inline-flex items-center justify-center gap-2 rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-600 transition hover:bg-gray-50"
                                    >
                                        <span class="material-symbols-outlined text-[18px]">backspace</span>
                                        Kosongkan Pilihan
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="border-t border-gray-100 bg-slate-50 px-6 py-5">
                        <div class="rounded-3xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
                            <p class="font-extrabold">
                                Aturan keamanan data
                            </p>

                            <p class="mt-1">
                                Form ini hanya menampilkan copy yang tersedia dan layak pinjam. Controller tetap melakukan validasi ulang agar buku dipinjam, hilang, atau rusak berat tidak bisa masuk transaksi.
                            </p>
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
                        @disabled(! $classBulkStoreRoute)
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-amber-500 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-amber-500/20 transition hover:bg-amber-600 disabled:cursor-not-allowed disabled:bg-gray-300"
                    >
                        <span class="material-symbols-outlined text-[18px]">save</span>
                        Simpan Peminjaman Kelas
                    </button>
                </div>
            </form>
        </div>

        <script>
            function classBulkLoanCreateForm(config) {
                return {
                    maxItems: Number(config.maxItems || 40),
                    books: Array.isArray(config.books) ? config.books : [],
                    selectedBookId: '',
                    selectedBook: null,
                    bookSearch: '',
                    dropdownOpen: false,
                    wantedCount: 1,
                    selectedCopyIds: [],

                    init() {
                        const oldBookId = config.oldBookId ? Number(config.oldBookId) : null;
                        const oldSelectedBookItemIds = Array.isArray(config.oldSelectedBookItemIds)
                            ? config.oldSelectedBookItemIds.map((id) => Number(id))
                            : [];

                        if (oldBookId) {
                            const book = this.books.find((item) => Number(item.id) === Number(oldBookId));

                            if (book) {
                                this.selectedBookId = book.id;
                                this.selectedBook = book;
                                this.bookSearch = book.title;
                            }
                        }

                        if (!this.selectedBook && oldSelectedBookItemIds.length > 0) {
                            const book = this.books.find((item) => {
                                return Array.isArray(item.copies)
                                    && item.copies.some((copy) => oldSelectedBookItemIds.includes(Number(copy.id)));
                            });

                            if (book) {
                                this.selectedBookId = book.id;
                                this.selectedBook = book;
                                this.bookSearch = book.title;
                            }
                        }

                        if (this.selectedBook) {
                            const allowedIds = this.selectedBook.copies.map((copy) => Number(copy.id));

                            this.selectedCopyIds = oldSelectedBookItemIds
                                .filter((id) => allowedIds.includes(Number(id)))
                                .map((id) => String(id));

                            this.wantedCount = Math.max(1, this.selectedCopyIds.length || 1);
                        }
                    },

                    normalizedText(value) {
                        return String(value || '').toLowerCase().trim();
                    },

                    filteredBooks() {
                        const keyword = this.normalizedText(this.bookSearch);

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

                    selectBook(book) {
                        this.selectedBookId = book.id;
                        this.selectedBook = book;
                        this.bookSearch = book.title;
                        this.dropdownOpen = false;
                        this.selectedCopyIds = [];
                        this.wantedCount = Math.min(1, this.maxAllowedForSelectedBook());
                    },

                    maxAllowedForSelectedBook() {
                        if (!this.selectedBook || !Array.isArray(this.selectedBook.copies)) {
                            return 1;
                        }

                        return Math.max(1, Math.min(this.maxItems, this.selectedBook.copies.length));
                    },

                    autoSelectCopies() {
                        if (!this.selectedBook) {
                            alert('Pilih judul buku terlebih dahulu.');
                            return;
                        }

                        let count = Number(this.wantedCount || 0);

                        if (count < 1) {
                            alert('Jumlah eksemplar minimal 1.');
                            return;
                        }

                        const maxAllowed = this.maxAllowedForSelectedBook();

                        if (count > maxAllowed) {
                            alert('Jumlah eksemplar melebihi copy tersedia atau batas Admin IT. Sistem akan memilih maksimal ' + maxAllowed + ' eksemplar.');
                            count = maxAllowed;
                            this.wantedCount = maxAllowed;
                        }

                        this.selectedCopyIds = this.selectedBook.copies
                            .slice(0, count)
                            .map((copy) => String(copy.id));
                    },

                    isSelected(copyId) {
                        return this.selectedCopyIds.includes(String(copyId));
                    },

                    toggleCopy(copyId) {
                        copyId = String(copyId);

                        if (this.isSelected(copyId)) {
                            this.selectedCopyIds = this.selectedCopyIds.filter((id) => id !== copyId);
                            return;
                        }

                        if (this.selectedCopyIds.length >= this.maxAllowedForSelectedBook()) {
                            alert('Pilihan sudah mencapai batas maksimal.');
                            return;
                        }

                        this.selectedCopyIds.push(copyId);
                    },

                    selectAllAllowed() {
                        if (!this.selectedBook) {
                            return;
                        }

                        const maxAllowed = this.maxAllowedForSelectedBook();

                        this.selectedCopyIds = this.selectedBook.copies
                            .slice(0, maxAllowed)
                            .map((copy) => String(copy.id));

                        this.wantedCount = this.selectedCopyIds.length;
                    },

                    clearSelection() {
                        this.selectedCopyIds = [];
                    },

                    prepareSubmit(event) {
                        if (!this.selectedBookId) {
                            event.preventDefault();
                            alert('Pilih judul buku terlebih dahulu.');
                            return;
                        }

                        if (this.selectedCopyIds.length === 0) {
                            event.preventDefault();
                            alert('Pilih minimal satu copy/eksemplar.');
                            return;
                        }

                        if (this.selectedCopyIds.length > this.maxItems) {
                            event.preventDefault();
                            alert('Jumlah eksemplar melebihi batas maksimal dari Admin IT.');
                            return;
                        }

                        const uniqueIds = new Set(this.selectedCopyIds);

                        if (uniqueIds.size !== this.selectedCopyIds.length) {
                            event.preventDefault();
                            alert('Ada copy/eksemplar yang terpilih lebih dari satu kali.');
                            return;
                        }

                        const confirmed = confirm(
                            'Simpan peminjaman kelas untuk ' +
                            this.selectedCopyIds.length +
                            ' eksemplar buku "' +
                            (this.selectedBook ? this.selectedBook.title : '') +
                            '"?'
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