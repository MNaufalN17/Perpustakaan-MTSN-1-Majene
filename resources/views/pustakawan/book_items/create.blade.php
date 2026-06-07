<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
                    Manajemen Koleksi
                </p>
                <h2 class="mt-1 text-xl font-bold text-gray-900">
                    Tambah Eksemplar Buku
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Kode eksemplar mengikuti Buku Induk. Status dan kondisi diatur per copy.
                </p>
            </div>

            <a href="{{ route('book_items.index') }}"
               class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-white px-5 py-3 text-sm font-semibold text-emerald-700 shadow-sm transition hover:bg-emerald-50">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Kembali
            </a>
        </div>
    </x-slot>

    <div
        class="min-h-screen bg-gradient-to-br from-slate-50 via-emerald-50/40 to-sky-50/40 py-10"
        x-data="bookItemCreate(@js($booksData), @js(old('book_id', '')), @js(old('items', [])))"
        x-init="init()"
    >
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/80 shadow-[0_20px_60px_rgba(15,23,42,0.08)] backdrop-blur-xl">
                <div class="relative overflow-hidden bg-gradient-to-r from-emerald-700 to-teal-500 p-6 text-white">
                    <div class="relative flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/20 text-white shadow-sm">
                            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">inventory_2</span>
                        </div>

                        <div>
                            <h3 class="text-lg font-bold">Form Tambah Eksemplar</h3>
                            <p class="mt-1 text-sm text-emerald-50">
                                Pilih Buku Induk, lalu sistem otomatis mengambil DDC, kode penulis, dan kode judul.
                            </p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('book_items.store') }}" class="space-y-8 p-6 md:p-8">
                    @csrf

                    <section class="rounded-3xl border border-emerald-100 bg-emerald-50/50 p-5 md:p-6">
                        <h4 class="font-bold text-gray-900">Buku Induk</h4>
                        <p class="mt-1 text-sm text-gray-500">Identitas kode eksemplar diambil dari data Buku Induk.</p>

                        <label for="book_id" class="mt-5 block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                            Judul Buku Induk <span class="text-red-500">*</span>
                        </label>

                        <select
                            id="book_id"
                            name="book_id"
                            x-model="selectedBookId"
                            @change="handleBookChange()"
                            required
                            class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                        >
                            <option value="">Pilih Judul Buku</option>
                            @foreach($books as $book)
                                <option value="{{ $book->id }}">{{ $book->title }}</option>
                            @endforeach
                        </select>

                        <template x-if="selectedBook">
                            <div class="mt-5 grid gap-4 md:grid-cols-5">
                                <div class="rounded-2xl bg-white p-4 shadow-sm">
                                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-gray-500">DDC</p>
                                    <p class="mt-2 font-mono text-sm font-bold text-emerald-700" x-text="selectedBook.ddc_code"></p>
                                </div>

                                <div class="rounded-2xl bg-white p-4 shadow-sm">
                                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-gray-500">Kode Penulis</p>
                                    <p class="mt-2 font-mono text-sm font-bold text-gray-900" x-text="selectedBook.author_code"></p>
                                </div>

                                <div class="rounded-2xl bg-white p-4 shadow-sm">
                                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-gray-500">Kode Judul</p>
                                    <p class="mt-2 font-mono text-sm font-bold text-gray-900" x-text="selectedBook.title_code"></p>
                                </div>

                                <div class="rounded-2xl bg-white p-4 shadow-sm">
                                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-gray-500">Penulis</p>
                                    <p class="mt-2 text-sm font-bold text-gray-900" x-text="selectedBook.author"></p>
                                </div>

                                <div class="rounded-2xl bg-white p-4 shadow-sm">
                                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-gray-500">Copy Berikutnya</p>
                                    <p class="mt-2 font-mono text-sm font-bold text-emerald-700" x-text="padNumber(selectedBook.next_index)"></p>
                                </div>
                            </div>
                        </template>
                    </section>

                    <section class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm md:p-6">
                        <h4 class="font-bold text-gray-900">Buat Banyak Eksemplar</h4>
                        <p class="mt-1 text-sm text-gray-500">
                            Tentukan rentang nomor copy. Status dan kondisi tetap bisa diubah per baris.
                        </p>

                        <div class="mt-5 grid gap-5 md:grid-cols-3">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">Copy Awal</label>
                                <input type="number" min="1" x-model.number="startIndex" class="mt-2 block w-full rounded-2xl border border-emerald-200 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">Copy Akhir</label>
                                <input type="number" min="1" x-model.number="endIndex" class="mt-2 block w-full rounded-2xl border border-emerald-200 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                            </div>

                            <div class="flex items-end">
                                <button type="button" @click="generateRows()" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-5 py-3 text-sm font-bold text-white hover:bg-emerald-800">
                                    <span class="material-symbols-outlined text-[18px]">table_rows</span>
                                    Buat Eksemplar
                                </button>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm md:p-6">
                        <h4 class="font-bold text-gray-900">Daftar Eksemplar</h4>

                        <div class="mt-5 overflow-x-auto rounded-3xl border border-gray-100">
                            <table class="min-w-[1000px] w-full divide-y divide-gray-100 text-left text-sm">
                                <thead class="bg-slate-50 text-xs uppercase tracking-wider text-gray-500">
                                    <tr>
                                        <th class="w-[70px] px-4 py-3 font-bold">No</th>
                                        <th class="w-[140px] px-4 py-3 font-bold">Nomor Copy</th>
                                        <th class="px-4 py-3 font-bold">Kode Eksemplar Otomatis</th>
                                        <th class="w-[180px] px-4 py-3 font-bold">Status</th>
                                        <th class="w-[210px] px-4 py-3 font-bold">Kondisi</th>
                                        <th class="w-[90px] px-4 py-3 text-center font-bold">Aksi</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-100 bg-white">
                                    <template x-for="(item, index) in items" :key="index">
                                        <tr>
                                            <td class="px-4 py-4">
                                                <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-50 text-xs font-bold text-emerald-700" x-text="index + 1"></span>
                                            </td>

                                            <td class="px-4 py-4">
                                                <input type="number" min="1" :name="`items[${index}][copy_number]`" x-model.number="item.copy_number" required class="block w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                                            </td>

                                            <td class="px-4 py-4">
                                                <div class="rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 font-mono text-sm font-bold text-gray-900" x-text="buildCode(item.copy_number)"></div>
                                            </td>

                                            <td class="px-4 py-4">
                                                <select :name="`items[${index}][status]`" x-model="item.status" required class="block w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                                                    <option value="tersedia">Tersedia</option>
                                                    <option value="dipinjam">Dipinjam</option>
                                                    <option value="rusak">Rusak</option>
                                                    <option value="hilang">Hilang</option>
                                                    <option value="nonaktif">Nonaktif</option>
                                                </select>
                                            </td>

                                            <td class="px-4 py-4">
                                                <select :name="`items[${index}][condition]`" x-model="item.condition" required class="block w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                                                    <option value="baik">Baik</option>
                                                    <option value="rusak ringan">Rusak Ringan</option>
                                                    <option value="rusak berat">Rusak Berat</option>
                                                    <option value="hilang">Hilang</option>
                                                </select>
                                            </td>

                                            <td class="px-4 py-4 text-center">
                                                <button type="button" @click="removeRow(index)" x-show="items.length > 1" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-red-200 bg-red-50 text-red-600 hover:bg-red-100">
                                                    <span class="material-symbols-outlined text-[18px]">close</span>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <div class="flex flex-col-reverse gap-3 border-t border-gray-100 pt-6 sm:flex-row sm:justify-end">
                        <a href="{{ route('book_items.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-6 py-3 text-sm font-bold text-gray-600 hover:bg-gray-50">
                            Batal
                        </a>

                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-6 py-3 text-sm font-bold text-white hover:bg-emerald-800">
                            <span>Simpan Eksemplar</span>
                            <span class="material-symbols-outlined text-[18px]">save</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function bookItemCreate(booksData, oldBookId, oldItems) {
                return {
                    booksData,
                    selectedBookId: oldBookId ? String(oldBookId) : '',
                    selectedBook: null,
                    startIndex: 1,
                    endIndex: 1,
                    items: oldItems && oldItems.length > 0 ? oldItems.map((item, index) => ({
                        copy_number: item.copy_number || index + 1,
                        status: item.status || 'tersedia',
                        condition: item.condition || 'baik',
                    })) : [],

                    init() {
                        if (this.selectedBookId) {
                            this.handleBookChange(false);
                        }

                        if (this.items.length === 0) {
                            this.items = [{
                                copy_number: this.startIndex,
                                status: 'tersedia',
                                condition: 'baik',
                            }];
                        }
                    },

                    getBookById(id) {
                        return this.booksData.find(book => String(book.id) === String(id)) || null;
                    },

                    handleBookChange(generate = true) {
                        this.selectedBook = this.getBookById(this.selectedBookId);

                        if (!this.selectedBook) {
                            return;
                        }

                        this.startIndex = this.selectedBook.next_index || 1;
                        this.endIndex = this.selectedBook.next_index || 1;

                        if (generate) {
                            this.generateRows();
                        }
                    },

                    padNumber(number) {
                        return String(parseInt(number || 1)).padStart(3, '0');
                    },

                    buildCode(copyNumber) {
                        if (!this.selectedBook) {
                            return 'Pilih buku induk terlebih dahulu';
                        }

                        return `${this.selectedBook.ddc_code}-${this.selectedBook.author_code}-${this.selectedBook.title_code}-${this.padNumber(copyNumber)}`;
                    },

                    generateRows() {
                        let start = parseInt(this.startIndex || 1);
                        let end = parseInt(this.endIndex || start);

                        if (start < 1) start = 1;
                        if (end < start) end = start;
                        if ((end - start + 1) > 200) end = start + 199;

                        this.startIndex = start;
                        this.endIndex = end;
                        this.items = [];

                        for (let i = start; i <= end; i++) {
                            this.items.push({
                                copy_number: i,
                                status: 'tersedia',
                                condition: 'baik',
                            });
                        }
                    },

                    removeRow(index) {
                        this.items.splice(index, 1);
                    },
                };
            }
        </script>
    </div>
</x-app-layout>
