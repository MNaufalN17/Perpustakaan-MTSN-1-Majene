<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
                    Manajemen Koleksi
                </p>
                <h2 class="mt-1 text-xl font-bold text-gray-900">
                    Tambah Buku Induk
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Tambahkan data utama buku. Kode penulis dan kode judul otomatis dibuat, tetapi tetap bisa disesuaikan sebelum disimpan.
                </p>
            </div>

            <a href="{{ route('books.index') }}"
               class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-white px-5 py-3 text-sm font-semibold text-emerald-700 shadow-sm hover:bg-emerald-50">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Kembali
            </a>
        </div>
    </x-slot>

    <div
        class="min-h-screen bg-gradient-to-br from-slate-50 via-emerald-50/40 to-sky-50/40 py-10"
        x-data="{
            title: @js(old('title', '')),
            author: @js(old('author', '')),
            authorCode: @js(old('author_code', '')),
            titleCode: @js(old('title_code', '')),
            selectedDdcId: @js((string) old('ddc_class_id', '')),
            ddcClasses: @js($ddcClasses->map(fn($ddc) => ['id' => (string) $ddc->id, 'code' => $ddc->code])->values()),
            authorCodeTouched: @js(old('author_code') ? true : false),
            titleCodeTouched: @js(old('title_code') ? true : false),

            init() {
                if (!this.authorCode) {
                    this.authorCode = this.makeAuthorCode(this.author);
                }

                if (!this.titleCode) {
                    this.titleCode = this.makeTitleCode(this.title);
                }
            },

            makeAuthorCode(value) {
                const clean = (value || '').replace(/[^a-zA-Z]/g, '');

                if (!clean) {
                    return 'Pen';
                }

                const code = clean.substring(0, 3);

                return code.charAt(0).toUpperCase() + code.substring(1).toLowerCase();
            },

            makeTitleCode(value) {
                const clean = (value || '').replace(/[^a-zA-Z0-9]/g, '');

                if (!clean) {
                    return 'b';
                }

                return clean.substring(0, 1).toLowerCase();
            },

            syncAuthorCode() {
                if (!this.authorCodeTouched) {
                    this.authorCode = this.makeAuthorCode(this.author);
                }
            },

            syncTitleCode() {
                if (!this.titleCodeTouched) {
                    this.titleCode = this.makeTitleCode(this.title);
                }
            },

            useAutoAuthorCode() {
                this.authorCode = this.makeAuthorCode(this.author);
                this.authorCodeTouched = true;
            },

            useAutoTitleCode() {
                this.titleCode = this.makeTitleCode(this.title);
                this.titleCodeTouched = true;
            },

            ddcCode() {
                const selected = this.ddcClasses.find(item => item.id === String(this.selectedDdcId));

                return selected ? selected.code : 'DDC';
            },

            exampleCode() {
                return `${this.ddcCode()}-${this.authorCode || 'Pen'}-${this.titleCode || 'b'}-001`;
            }
        }"
        x-init="init()"
    >
        <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">

            @if ($errors->any())
                <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-red-700 shadow-sm">
                    <p class="text-sm font-bold">
                        Data buku belum bisa disimpan
                    </p>

                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/80 shadow-[0_20px_60px_rgba(15,23,42,0.08)] backdrop-blur-xl">
                <div class="bg-gradient-to-r from-emerald-700 to-teal-500 p-6 text-white">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/20">
                            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">
                                library_books
                            </span>
                        </div>

                        <div>
                            <h3 class="text-lg font-bold">
                                Form Tambah Buku Induk
                            </h3>
                            <p class="mt-1 text-sm text-emerald-50">
                                Data ini menjadi rujukan kode untuk seluruh eksemplar buku.
                            </p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('books.store') }}" class="space-y-8 p-6 md:p-8">
                    @csrf

                    <section class="rounded-3xl border border-emerald-100 bg-emerald-50/50 p-5 md:p-6">
                        <div class="mb-5 flex items-start gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                                <span class="material-symbols-outlined text-[20px]">menu_book</span>
                            </div>

                            <div>
                                <h4 class="font-bold text-gray-900">
                                    Informasi Utama Buku
                                </h4>
                                <p class="mt-1 text-sm text-gray-500">
                                    Isi data dasar buku yang akan tampil pada katalog.
                                </p>
                            </div>
                        </div>

                        <div class="space-y-5">
                            <div>
                                <label for="title" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                    Judul Buku <span class="text-red-500">*</span>
                                </label>

                                <input
                                    id="title"
                                    name="title"
                                    type="text"
                                    x-model="title"
                                    @input="syncTitleCode()"
                                    required
                                    placeholder="Contoh: Data Science dan Informatika"
                                    class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                                >

                                @error('title')
                                    <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid gap-5 md:grid-cols-2">
                                <div>
                                    <label for="author" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                        Penulis <span class="text-red-500">*</span>
                                    </label>

                                    <input
                                        id="author"
                                        name="author"
                                        type="text"
                                        x-model="author"
                                        @input="syncAuthorCode()"
                                        required
                                        placeholder="Contoh: Sugiarto Cokrowibowo"
                                        class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                                    >

                                    @error('author')
                                        <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="publisher" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                        Penerbit <span class="text-red-500">*</span>
                                    </label>

                                    <input
                                        id="publisher"
                                        name="publisher"
                                        type="text"
                                        value="{{ old('publisher') }}"
                                        required
                                        placeholder="Contoh: Erlangga"
                                        class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                                    >

                                    @error('publisher')
                                        <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid gap-5 md:grid-cols-2">
                                <div>
                                    <label for="publication_year" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                        Tahun Terbit
                                    </label>

                                    <input
                                        id="publication_year"
                                        name="publication_year"
                                        type="number"
                                        min="1900"
                                        max="{{ date('Y') + 1 }}"
                                        value="{{ old('publication_year') }}"
                                        placeholder="Contoh: 2024"
                                        class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                                    >

                                    @error('publication_year')
                                        <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="price" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                        Harga Buku
                                    </label>

                                    <input
                                        id="price"
                                        name="price"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        value="{{ old('price') }}"
                                        placeholder="Contoh: 70000"
                                        class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                                    >

                                    @error('price')
                                        <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm md:p-6">
                        <div class="mb-5 flex items-start gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-teal-100 text-teal-700">
                                <span class="material-symbols-outlined text-[20px]">qr_code_2</span>
                            </div>

                            <div>
                                <h4 class="font-bold text-gray-900">
                                    Kode Buku Induk
                                </h4>
                                <p class="mt-1 text-sm text-gray-500">
                                    Kode otomatis boleh langsung dipakai atau disesuaikan sebelum disimpan.
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <label for="author_code" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                    Kode Penulis <span class="text-red-500">*</span>
                                </label>

                                <div class="mt-2 flex flex-col gap-2 sm:flex-row">
                                    <input
                                        id="author_code"
                                        name="author_code"
                                        type="text"
                                        x-model="authorCode"
                                        @input="authorCodeTouched = true"
                                        required
                                        placeholder="Contoh: Sug"
                                        class="block w-full rounded-2xl border border-emerald-200 bg-emerald-50/60 px-4 py-3 font-mono text-sm font-bold text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                                    >

                                    <button
                                        type="button"
                                        @click="useAutoAuthorCode()"
                                        class="inline-flex items-center justify-center rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-xs font-bold text-emerald-700 hover:bg-emerald-50"
                                    >
                                        Ambil Otomatis
                                    </button>
                                </div>

                                @error('author_code')
                                    <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="title_code" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                    Kode Judul <span class="text-red-500">*</span>
                                </label>

                                <div class="mt-2 flex flex-col gap-2 sm:flex-row">
                                    <input
                                        id="title_code"
                                        name="title_code"
                                        type="text"
                                        x-model="titleCode"
                                        @input="titleCodeTouched = true"
                                        required
                                        placeholder="Contoh: d"
                                        class="block w-full rounded-2xl border border-emerald-200 bg-emerald-50/60 px-4 py-3 font-mono text-sm font-bold text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                                    >

                                    <button
                                        type="button"
                                        @click="useAutoTitleCode()"
                                        class="inline-flex items-center justify-center rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-xs font-bold text-emerald-700 hover:bg-emerald-50"
                                    >
                                        Ambil Otomatis
                                    </button>
                                </div>

                                @error('title_code')
                                    <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3">
                            <p class="text-sm font-semibold text-emerald-800">
                                Contoh kode eksemplar:
                                <span class="font-mono" x-text="exampleCode()"></span>
                            </p>
                            <p class="mt-1 text-xs text-emerald-700">
                                Saat buku disimpan, kode inilah yang menjadi dasar untuk semua eksemplar buku tersebut.
                            </p>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm md:p-6">
                        <div class="mb-5 flex items-start gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-cyan-100 text-cyan-700">
                                <span class="material-symbols-outlined text-[20px]">category</span>
                            </div>

                            <div>
                                <h4 class="font-bold text-gray-900">
                                    Kategori dan Klasifikasi
                                </h4>
                                <p class="mt-1 text-sm text-gray-500">
                                    DDC menjadi bagian awal dari kode eksemplar.
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <label for="category_id" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                    Kategori <span class="text-red-500">*</span>
                                </label>

                                <select
                                    id="category_id"
                                    name="category_id"
                                    required
                                    class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                                >
                                    <option value="">Pilih Kategori</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('category_id')
                                    <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="ddc_class_id" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                    Kelas DDC <span class="text-red-500">*</span>
                                </label>

                                <select
                                    id="ddc_class_id"
                                    name="ddc_class_id"
                                    x-model="selectedDdcId"
                                    required
                                    class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                                >
                                    <option value="">Pilih Kelas DDC</option>
                                    @foreach($ddcClasses as $ddc)
                                        <option value="{{ $ddc->id }}" {{ old('ddc_class_id') == $ddc->id ? 'selected' : '' }}>
                                            {{ $ddc->code }} - {{ $ddc->name }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('ddc_class_id')
                                    <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm md:p-6">
                        <div class="mb-5 flex items-start gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                                <span class="material-symbols-outlined text-[20px]">rule</span>
                            </div>

                            <div>
                                <h4 class="font-bold text-gray-900">
                                    Aturan Peminjaman
                                </h4>
                                <p class="mt-1 text-sm text-gray-500">
                                    Tentukan apakah buku ini bisa dipinjam pulang.
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <label for="borrowing_status" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                    Status Peminjaman <span class="text-red-500">*</span>
                                </label>

                                <select
                                    id="borrowing_status"
                                    name="borrowing_status"
                                    required
                                    class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                                >
                                    <option value="bisa dipinjam" {{ old('borrowing_status', 'bisa dipinjam') == 'bisa dipinjam' ? 'selected' : '' }}>
                                        Bisa Dipinjam
                                    </option>
                                    <option value="tidak bisa dipinjam" {{ old('borrowing_status') == 'tidak bisa dipinjam' ? 'selected' : '' }}>
                                        Tidak Bisa Dipinjam
                                    </option>
                                </select>

                                @error('borrowing_status')
                                    <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="description" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                    Deskripsi / Catatan
                                </label>

                                <textarea
                                    id="description"
                                    name="description"
                                    rows="3"
                                    placeholder="Catatan tambahan tentang buku..."
                                    class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                                >{{ old('description') }}</textarea>

                                @error('description')
                                    <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </section>

                    <div class="flex flex-col-reverse gap-3 border-t border-gray-100 pt-6 sm:flex-row sm:justify-end">
                        <a href="{{ route('books.index') }}"
                           class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-6 py-3 text-sm font-bold text-gray-600 hover:bg-gray-50">
                            Batal
                        </a>

                        <button
                            type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-6 py-3 text-sm font-bold text-white hover:bg-emerald-800">
                            <span>Simpan Buku</span>
                            <span class="material-symbols-outlined text-[18px]">save</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>