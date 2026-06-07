<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
                    Manajemen Koleksi
                </p>

                <h2 class="mt-1 text-xl font-bold text-gray-900">
                    Edit Eksemplar Buku
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Perbarui data eksemplar. Status dan kondisi dibatasi agar tidak saling bertentangan.
                </p>
            </div>

            <a href="{{ route('book_items.index') }}"
               class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-white px-5 py-3 text-sm font-semibold text-emerald-700 shadow-sm transition hover:bg-emerald-50">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Kembali
            </a>
        </div>
    </x-slot>

    @php
        $hasActiveLoan = $bookItem->loanItems()
            ->whereIn('status', ['dipinjam', 'terlambat'])
            ->whereHas('loan', function ($query) {
                $query->whereIn('status', ['aktif', 'terlambat']);
            })
            ->exists();

        $selectedBookId = old('book_id', $bookItem->book_id);
        $selectedStatus = old('status', $bookItem->status ?? 'tersedia');
        $selectedCondition = old('condition', $bookItem->condition ?? 'baik');

        $booksData = collect($books ?? [])->map(function ($book) {
            return [
                'id' => $book->id,
                'title' => $book->title,
                'author' => $book->author,
                'ddc_code' => $book->ddcClass?->code ?? 'BK',
                'author_code' => $book->author_code ?? '',
                'title_code' => $book->title_code ?? '',
            ];
        })->values();
    @endphp

    <div
        class="min-h-screen bg-gradient-to-br from-slate-50 via-emerald-50/40 to-sky-50/40 py-10"
        x-data="bookItemEdit({
            books: @js($booksData),
            selectedBookId: @js((string) $selectedBookId),
            selectedStatus: @js((string) $selectedStatus),
            selectedCondition: @js((string) $selectedCondition),
            hasActiveLoan: @js($hasActiveLoan),
        })"
        x-init="init()"
    >
        <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">

            @if($errors->any())
                <div class="mb-6 rounded-3xl border border-red-200 bg-red-50 px-5 py-4 text-red-800 shadow-sm">
                    <p class="font-extrabold">Validasi gagal</p>

                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('error_title') || session('error_message'))
                <div class="mb-6 rounded-3xl border border-red-200 bg-red-50 px-5 py-4 text-red-800 shadow-sm">
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

            <div class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/80 shadow-[0_20px_60px_rgba(15,23,42,0.08)] backdrop-blur-xl">
                <div class="relative overflow-hidden bg-gradient-to-r from-emerald-700 to-teal-500 p-6 text-white">
                    <div class="relative flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/20 text-white shadow-sm">
                            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">edit_square</span>
                        </div>

                        <div>
                            <h3 class="text-lg font-bold">
                                Form Edit Eksemplar
                            </h3>

                            <p class="mt-1 text-sm text-emerald-50">
                                Kode eksemplar: <span class="font-mono font-bold">{{ $bookItem->item_code ?? '-' }}</span>
                            </p>
                        </div>
                    </div>
                </div>

                <form
                    method="POST"
                    action="{{ route('book_items.update', $bookItem) }}"
                    class="space-y-8 p-6 md:p-8"
                    @submit="prepareSubmit($event)"
                >
                    @csrf
                    @method('PUT')

                    <section class="rounded-3xl border border-emerald-100 bg-emerald-50/50 p-5 md:p-6">
                        <h4 class="font-bold text-gray-900">
                            Buku Induk
                        </h4>

                        <p class="mt-1 text-sm text-gray-500">
                            Pilih buku induk yang sesuai untuk eksemplar ini.
                        </p>

                        <label for="book_id" class="mt-5 block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                            Judul Buku Induk <span class="text-red-500">*</span>
                        </label>

                        <select
                            id="book_id"
                            name="book_id"
                            x-model="selectedBookId"
                            @change="handleBookChange()"
                            required
                            @disabled($hasActiveLoan)
                            class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 disabled:bg-gray-100 disabled:text-gray-500"
                        >
                            <option value="">Pilih Judul Buku</option>

                            @foreach($books ?? [] as $book)
                                <option value="{{ $book->id }}" @selected((string) $selectedBookId === (string) $book->id)>
                                    {{ $book->title }}
                                </option>
                            @endforeach
                        </select>

                        @if($hasActiveLoan)
                            <input type="hidden" name="book_id" value="{{ $bookItem->book_id }}">

                            <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                                Eksemplar sedang dipinjam. Buku induk, status, dan kondisi tidak bisa diubah sampai proses pengembalian selesai.
                            </div>
                        @endif

                        <template x-if="selectedBook">
                            <div class="mt-5 grid gap-4 md:grid-cols-4">
                                <div class="rounded-2xl bg-white p-4 shadow-sm">
                                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-gray-500">DDC</p>
                                    <p class="mt-2 font-mono text-sm font-bold text-emerald-700" x-text="selectedBook.ddc_code || 'BK'"></p>
                                </div>

                                <div class="rounded-2xl bg-white p-4 shadow-sm">
                                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-gray-500">Kode Penulis</p>
                                    <p class="mt-2 font-mono text-sm font-bold text-gray-900" x-text="selectedBook.author_code || '-'"></p>
                                </div>

                                <div class="rounded-2xl bg-white p-4 shadow-sm">
                                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-gray-500">Kode Judul</p>
                                    <p class="mt-2 font-mono text-sm font-bold text-gray-900" x-text="selectedBook.title_code || '-'"></p>
                                </div>

                                <div class="rounded-2xl bg-white p-4 shadow-sm">
                                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-gray-500">Penulis</p>
                                    <p class="mt-2 text-sm font-bold text-gray-900" x-text="selectedBook.author || '-'"></p>
                                </div>
                            </div>
                        </template>
                    </section>

                    <section class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm md:p-6">
                        <h4 class="font-bold text-gray-900">
                            Data Eksemplar
                        </h4>

                        <p class="mt-1 text-sm text-gray-500">
                            Status tersedia hanya boleh kondisi baik. Status rusak hanya boleh kondisi rusak ringan atau rusak berat.
                        </p>

                        <div class="mt-5 grid gap-5 md:grid-cols-2">
                            <div>
                                <label for="item_code" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                    Kode Eksemplar <span class="text-red-500">*</span>
                                </label>

                                <input
                                    id="item_code"
                                    type="text"
                                    name="item_code"
                                    value="{{ old('item_code', $bookItem->item_code) }}"
                                    required
                                    @readonly($hasActiveLoan)
                                    class="mt-2 block w-full rounded-2xl border border-gray-200 px-4 py-3 font-mono text-sm font-bold focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 read-only:bg-gray-100 read-only:text-gray-500"
                                >
                            </div>

                            <div>
                                <label for="copy_number" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                    Nomor Copy
                                </label>

                                <input
                                    id="copy_number"
                                    type="number"
                                    name="copy_number"
                                    min="1"
                                    value="{{ old('copy_number', $bookItem->copy_number) }}"
                                    @readonly($hasActiveLoan)
                                    class="mt-2 block w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 read-only:bg-gray-100 read-only:text-gray-500"
                                >
                            </div>

                            <div>
                                <label for="classification_code" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                    Kode Klasifikasi
                                </label>

                                <input
                                    id="classification_code"
                                    type="text"
                                    name="classification_code"
                                    value="{{ old('classification_code', $bookItem->classification_code) }}"
                                    class="mt-2 block w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                                >
                            </div>

                            <div>
                                <label for="author_code" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                    Kode Penulis
                                </label>

                                <input
                                    id="author_code"
                                    type="text"
                                    name="author_code"
                                    value="{{ old('author_code', $bookItem->author_code) }}"
                                    class="mt-2 block w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                                >
                            </div>

                            <div>
                                <label for="title_code" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                    Kode Judul
                                </label>

                                <input
                                    id="title_code"
                                    type="text"
                                    name="title_code"
                                    value="{{ old('title_code', $bookItem->title_code) }}"
                                    class="mt-2 block w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                                >
                            </div>

                            <div>
                                <label for="title_initial" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                    Inisial Judul
                                </label>

                                <input
                                    id="title_initial"
                                    type="text"
                                    name="title_initial"
                                    value="{{ old('title_initial', $bookItem->title_initial) }}"
                                    class="mt-2 block w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                                >
                            </div>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm md:p-6">
                        <h4 class="font-bold text-gray-900">
                            Status dan Kondisi
                        </h4>

                        <p class="mt-1 text-sm text-gray-500">
                            Pilihan kondisi otomatis mengikuti status agar data tidak bertentangan.
                        </p>

                        <div class="mt-5 grid gap-5 md:grid-cols-2">
                            <div>
                                <label for="status" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                    Status <span class="text-red-500">*</span>
                                </label>

                                @if($hasActiveLoan)
                                    <input type="hidden" name="status" value="{{ $bookItem->status }}">

                                    <div class="mt-2 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-bold text-amber-800">
                                        {{ ucfirst($bookItem->status ?? '-') }}
                                    </div>
                                @else
                                    <select
                                        id="status"
                                        name="status"
                                        x-model="status"
                                        @change="syncConditionWithStatus()"
                                        required
                                        class="mt-2 block w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                                    >
                                        <template x-for="option in statusOptions" :key="option.value">
                                            <option :value="option.value" x-text="option.label"></option>
                                        </template>
                                    </select>
                                @endif
                            </div>

                            <div>
                                <label for="condition" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                    Kondisi <span class="text-red-500">*</span>
                                </label>

                                @if($hasActiveLoan)
                                    <input type="hidden" name="condition" value="{{ $bookItem->condition }}">

                                    <div class="mt-2 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-bold text-amber-800">
                                        {{ ucfirst($bookItem->condition ?? '-') }}
                                    </div>
                                @else
                                    <select
                                        id="condition"
                                        name="condition"
                                        x-model="condition"
                                        required
                                        class="mt-2 block w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                                    >
                                        <template x-for="option in conditionOptionsForStatus(status)" :key="option.value">
                                            <option :value="option.value" x-text="option.label"></option>
                                        </template>
                                    </select>
                                @endif

                                <p class="mt-2 text-xs text-gray-500" x-text="conditionHelpText(status)"></p>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm md:p-6">
                        <h4 class="font-bold text-gray-900">
                            Informasi Tambahan
                        </h4>

                        <div class="mt-5 grid gap-5 md:grid-cols-2">
                            <div>
                                <label for="location" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                    Lokasi Rak
                                </label>

                                <input
                                    id="location"
                                    type="text"
                                    name="location"
                                    value="{{ old('location', $bookItem->location) }}"
                                    placeholder="Contoh: Rak A1"
                                    class="mt-2 block w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                                >
                            </div>

                            <div>
                                <label for="acquisition_date" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                    Tanggal Pengadaan
                                </label>

                                <input
                                    id="acquisition_date"
                                    type="date"
                                    name="acquisition_date"
                                    value="{{ old('acquisition_date', optional($bookItem->acquisition_date)->format('Y-m-d')) }}"
                                    class="mt-2 block w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                                >
                            </div>
                        </div>
                    </section>

                    <div class="flex flex-col-reverse gap-3 border-t border-gray-100 pt-6 sm:flex-row sm:justify-end">
                        <a href="{{ route('book_items.index') }}"
                           class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-6 py-3 text-sm font-bold text-gray-600 hover:bg-gray-50">
                            Batal
                        </a>

                        <button
                            type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-6 py-3 text-sm font-bold text-white hover:bg-emerald-800"
                        >
                            <span>Simpan Perubahan</span>
                            <span class="material-symbols-outlined text-[18px]">save</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function bookItemEdit(config) {
                return {
                    books: Array.isArray(config.books) ? config.books : [],
                    selectedBookId: config.selectedBookId || '',
                    selectedBook: null,
                    status: String(config.selectedStatus || 'tersedia').toLowerCase(),
                    condition: String(config.selectedCondition || 'baik').toLowerCase(),
                    hasActiveLoan: Boolean(config.hasActiveLoan),

                    statusOptions: [
                        { value: 'tersedia', label: 'Tersedia' },
                        { value: 'rusak', label: 'Rusak' },
                        { value: 'hilang', label: 'Hilang' },
                        { value: 'nonaktif', label: 'Nonaktif' },
                    ],

                    conditionOptionsByStatus: {
                        tersedia: [
                            { value: 'baik', label: 'Baik' },
                        ],
                        rusak: [
                            { value: 'rusak ringan', label: 'Rusak Ringan' },
                            { value: 'rusak berat', label: 'Rusak Berat' },
                        ],
                        hilang: [
                            { value: 'hilang', label: 'Hilang' },
                        ],
                        nonaktif: [
                            { value: 'baik', label: 'Baik' },
                            { value: 'rusak ringan', label: 'Rusak Ringan' },
                            { value: 'rusak berat', label: 'Rusak Berat' },
                            { value: 'hilang', label: 'Hilang' },
                        ],
                    },

                    init() {
                        this.handleBookChange();

                        if (!this.hasActiveLoan) {
                            this.status = this.normalizeStatus(this.status);
                            this.syncConditionWithStatus();
                        }
                    },

                    getBookById(id) {
                        return this.books.find((book) => String(book.id) === String(id)) || null;
                    },

                    handleBookChange() {
                        this.selectedBook = this.getBookById(this.selectedBookId);
                    },

                    normalizeStatus(status) {
                        status = String(status || '').toLowerCase().trim();

                        if (['tersedia', 'rusak', 'hilang', 'nonaktif'].includes(status)) {
                            return status;
                        }

                        return 'tersedia';
                    },

                    conditionOptionsForStatus(status) {
                        status = this.normalizeStatus(status);

                        return this.conditionOptionsByStatus[status] || this.conditionOptionsByStatus.tersedia;
                    },

                    syncConditionWithStatus() {
                        this.status = this.normalizeStatus(this.status);

                        const allowedConditions = this.conditionOptionsForStatus(this.status);
                        const allowedValues = allowedConditions.map((option) => option.value);

                        if (!allowedValues.includes(this.condition)) {
                            this.condition = allowedConditions[0].value;
                        }
                    },

                    conditionHelpText(status) {
                        status = this.normalizeStatus(status);

                        if (status === 'tersedia') {
                            return 'Status tersedia hanya boleh kondisi baik.';
                        }

                        if (status === 'rusak') {
                            return 'Status rusak hanya untuk rusak ringan atau rusak berat.';
                        }

                        if (status === 'hilang') {
                            return 'Status hilang hanya boleh kondisi hilang.';
                        }

                        if (status === 'nonaktif') {
                            return 'Nonaktif adalah status administratif; kondisi fisik tetap dipilih.';
                        }

                        return '';
                    },

                    isValidStatusConditionPair() {
                        const allowedValues = this.conditionOptionsForStatus(this.status)
                            .map((option) => option.value);

                        return allowedValues.includes(this.condition);
                    },

                    prepareSubmit(event) {
                        if (this.hasActiveLoan) {
                            return;
                        }

                        if (!this.isValidStatusConditionPair()) {
                            event.preventDefault();
                            alert('Kombinasi status dan kondisi tidak valid.');
                            return;
                        }

                        const confirmed = confirm('Simpan perubahan eksemplar ini?');

                        if (!confirmed) {
                            event.preventDefault();
                        }
                    },
                };
            }
        </script>
    </div>
</x-app-layout>