    <x-app-layout>
        <x-slot name="header">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
                        Master Data
                    </p>
                    <h2 class="mt-1 text-xl font-bold text-gray-900">
                        Master Data Kelas DDC
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Kelola kode klasifikasi DDC yang digunakan pada data buku induk.
                    </p>
                </div>

                <button
                    type="button"
                    onclick="window.dispatchEvent(new CustomEvent('open-ddc-modal'))"
                    class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800"
                >
                    <span class="material-symbols-outlined text-[18px]">add_circle</span>
                    Tambah DDC
                </button>
            </div>
        </x-slot>

        @php
            $ddcCount = $ddcClasses->count();
        @endphp

        <div
            class="min-h-screen bg-gradient-to-br from-slate-50 via-emerald-50/30 to-sky-50/30 py-8"
            x-data="{
                showModal: @js($errors->any()),
                showConfirmModal: false,
                confirmType: '',
                selectedDdc: {
                    id: null,
                    code: '',
                    name: '',
                    books_count: 0
                },
                search: '',

                editUrlTemplate: @js(route('ddc.edit', ['ddc' => '__ID__'])),
                deleteUrlTemplate: @js(route('ddc.destroy', ['ddc' => '__ID__'])),

                matches(text) {
                    const keyword = this.search.toLowerCase().trim();
                    return keyword === '' || text.toLowerCase().includes(keyword);
                },

                resetSearch() {
                    this.search = '';
                },

                openConfirm(type, ddc) {
                    this.confirmType = type;
                    this.selectedDdc = ddc;
                    this.showConfirmModal = true;
                },

                closeConfirm() {
                    this.showConfirmModal = false;
                    this.confirmType = '';
                    this.selectedDdc = {
                        id: null,
                        code: '',
                        name: '',
                        books_count: 0
                    };
                },

                confirmAction() {
                    if (this.confirmType === 'edit') {
                        window.location.href = this.editUrlTemplate.replace('__ID__', this.selectedDdc.id);
                        return;
                    }

                    if (this.confirmType === 'delete') {
                        this.$refs.deleteForm.action = this.deleteUrlTemplate.replace('__ID__', this.selectedDdc.id);
                        this.$refs.deleteForm.submit();
                    }
                }
            }"
            @open-ddc-modal.window="showModal = true"
            @keydown.escape.window="showModal = false; showConfirmModal = false"
        >
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

                <div class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/80 shadow-[0_18px_50px_rgba(15,23,42,0.06)] backdrop-blur-xl">
                    <div class="border-b border-gray-100 bg-white px-6 py-6">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                                    <span class="material-symbols-outlined">category</span>
                                </div>

                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">
                                        Database DDC
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-500">
                                        Total {{ number_format($ddcCount, 0, ',', '.') }} klasifikasi DDC tersimpan.
                                    </p>
                                </div>
                            </div>

                            <div class="inline-flex w-fit items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700">
                                <span class="material-symbols-outlined text-[18px]">library_books</span>
                                {{ number_format($ddcCount, 0, ',', '.') }} DDC
                            </div>
                        </div>

                        <div class="mt-6 grid gap-3 sm:grid-cols-[1fr_auto]">
                            <div class="relative">
                                <span class="material-symbols-outlined pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[20px] text-gray-400">
                                    search
                                </span>

                                <input
                                    type="text"
                                    x-model="search"
                                    placeholder="Cari kode DDC, nama klasifikasi, atau deskripsi..."
                                    class="w-full rounded-2xl border border-gray-200 bg-slate-50 px-12 py-3 text-sm text-gray-800 outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100"
                                >
                            </div>

                            <button
                                type="button"
                                @click="resetSearch()"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-bold text-gray-600 transition hover:bg-gray-50"
                            >
                                <span class="material-symbols-outlined text-[18px]">restart_alt</span>
                                Reset
                            </button>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="mb-5 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800">
                            <div class="flex items-start gap-3">
                                <span class="material-symbols-outlined mt-0.5 text-[20px] text-amber-600">warning</span>
                                <p class="text-sm leading-6">
                                    DDC adalah data master klasifikasi yang menjadi rujukan pada buku induk.
                                    Proses edit atau hapus perlu dikonfirmasi ulang agar tidak mengganggu konsistensi data koleksi.
                                </p>
                            </div>
                        </div>

                        <div class="overflow-x-auto rounded-3xl border border-gray-100 bg-white">
                            <table class="min-w-[950px] w-full divide-y divide-gray-100 text-left text-sm">
                                <thead class="bg-slate-50 text-xs uppercase tracking-wider text-gray-500">
                                    <tr>
                                        <th class="w-[150px] px-5 py-4 font-bold">Kode</th>
                                        <th class="w-[280px] px-5 py-4 font-bold">Nama Klasifikasi</th>
                                        <th class="px-5 py-4 font-bold">Deskripsi</th>
                                        <th class="w-[150px] px-5 py-4 text-center font-bold">Dipakai</th>
                                        <th class="w-[220px] px-5 py-4 text-center font-bold">Aksi</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @forelse($ddcClasses as $ddc)
                                        @php
                                            $searchText = strtolower(
                                                ($ddc->code ?? '') . ' ' .
                                                ($ddc->name ?? '') . ' ' .
                                                ($ddc->description ?? '')
                                            );
                                        @endphp

                                        <tr
                                            class="transition hover:bg-emerald-50/40"
                                            x-show="matches(@js($searchText))"
                                        >
                                            <td class="px-5 py-4 align-middle">
                                                <span class="inline-flex items-center justify-center rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-2 font-mono text-sm font-bold text-emerald-700">
                                                    {{ $ddc->code }}
                                                </span>
                                            </td>

                                            <td class="px-5 py-4 align-middle">
                                                <p class="font-bold text-gray-900">
                                                    {{ $ddc->name }}
                                                </p>
                                                <p class="mt-1 text-xs text-gray-500">
                                                    Klasifikasi koleksi buku
                                                </p>
                                            </td>

                                            <td class="px-5 py-4 align-middle text-gray-600">
                                                {{ $ddc->description ?? '-' }}
                                            </td>

                                            <td class="px-5 py-4 text-center align-middle">
                                                <span class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-700">
                                                    {{ $ddc->books_count ?? 0 }} buku
                                                </span>
                                            </td>

                                            <td class="px-5 py-4 text-center align-middle">
                                                <div class="flex flex-wrap items-center justify-center gap-2">
                                                    <button
                                                        type="button"
                                                        @click="openConfirm('edit', {
                                                            id: @js($ddc->id),
                                                            code: @js($ddc->code),
                                                            name: @js($ddc->name),
                                                            books_count: @js($ddc->books_count ?? 0)
                                                        })"
                                                        class="inline-flex items-center gap-1 rounded-full border border-teal-200 bg-white px-3 py-1.5 text-xs font-bold text-teal-700 transition hover:bg-teal-50"
                                                    >
                                                        <span class="material-symbols-outlined text-[15px]">edit</span>
                                                        Edit
                                                    </button>

                                                    <button
                                                        type="button"
                                                        @click="openConfirm('delete', {
                                                            id: @js($ddc->id),
                                                            code: @js($ddc->code),
                                                            name: @js($ddc->name),
                                                            books_count: @js($ddc->books_count ?? 0)
                                                        })"
                                                        class="inline-flex items-center gap-1 rounded-full border border-red-200 bg-white px-3 py-1.5 text-xs font-bold text-red-600 transition hover:bg-red-50"
                                                    >
                                                        <span class="material-symbols-outlined text-[15px]">delete</span>
                                                        Hapus
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-14 text-center">
                                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                                                    <span class="material-symbols-outlined">category</span>
                                                </div>
                                                <p class="mt-4 text-sm font-semibold text-gray-700">
                                                    Data DDC masih kosong.
                                                </p>
                                                <p class="mt-1 text-xs text-gray-500">
                                                    Klik tombol Tambah DDC untuk menambahkan klasifikasi baru.
                                                </p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <form x-ref="deleteForm" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>

            {{-- Modal Tambah DDC --}}
            <div
                x-show="showModal"
                x-cloak
                x-transition.opacity
                class="fixed inset-0 z-50 overflow-y-auto px-4 py-6"
            >
                <div
                    class="fixed inset-0 bg-slate-900/60 backdrop-blur-md"
                    @click="showModal = false"
                ></div>

                <div class="relative z-10 flex min-h-full items-start justify-center py-6">
                    <div
                        x-show="showModal"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95 translate-y-3"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                        x-transition:leave-end="opacity-0 scale-95 translate-y-3"
                        class="w-full max-w-2xl overflow-hidden rounded-[2rem] border border-white/70 bg-white shadow-2xl"
                    >
                        <div class="flex items-center justify-between border-b border-gray-100 bg-gradient-to-r from-emerald-700 to-teal-500 px-6 py-5 text-white">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-50">
                                    Form DDC
                                </p>
                                <h3 class="mt-1 text-lg font-bold">
                                    Tambah DDC Baru
                                </h3>
                                <p class="mt-1 text-xs text-emerald-50">
                                    Tambahkan kode klasifikasi DDC untuk digunakan pada buku induk.
                                </p>
                            </div>

                            <button
                                type="button"
                                @click="showModal = false"
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-white/15 text-white transition hover:bg-white/25"
                            >
                                <span class="material-symbols-outlined text-[20px]">close</span>
                            </button>
                        </div>

                        <form method="POST" action="{{ route('ddc.store') }}" class="space-y-5 p-6">
                            @csrf

                            @if($errors->any())
                                <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-red-700">
                                    <p class="text-sm font-bold">Data DDC belum bisa disimpan</p>

                                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div>
                                <label for="code" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                    Kode DDC <span class="text-red-500">*</span>
                                </label>

                                <input
                                    id="code"
                                    name="code"
                                    type="text"
                                    value="{{ old('code') }}"
                                    required
                                    placeholder="Contoh: 000, 200, 2X5, 500"
                                    class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 font-mono text-sm font-bold text-gray-900 shadow-sm transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                                >
                            </div>

                            <div>
                                <label for="name" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                    Nama Klasifikasi <span class="text-red-500">*</span>
                                </label>

                                <input
                                    id="name"
                                    name="name"
                                    type="text"
                                    value="{{ old('name') }}"
                                    required
                                    placeholder="Contoh: Agama Islam, Ilmu Alam, Matematika"
                                    class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                                >
                            </div>

                            <div>
                                <label for="description" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                    Deskripsi
                                </label>

                                <textarea
                                    id="description"
                                    name="description"
                                    rows="4"
                                    placeholder="Tuliskan ruang lingkup klasifikasi DDC ini..."
                                    class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                                >{{ old('description') }}</textarea>
                            </div>

                            <div class="flex flex-col-reverse gap-3 border-t border-gray-100 pt-5 sm:flex-row sm:justify-end">
                                <button
                                    type="button"
                                    @click="showModal = false"
                                    class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-600 transition hover:bg-gray-50"
                                >
                                    Batal
                                </button>

                                <button
                                    type="submit"
                                    class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-700/20 transition hover:bg-emerald-800"
                                >
                                    <span>Simpan DDC</span>
                                    <span class="material-symbols-outlined text-[18px]">save</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Modal Konfirmasi Edit / Hapus --}}
            <div
                x-show="showConfirmModal"
                x-cloak
                x-transition.opacity
                class="fixed inset-0 z-50 overflow-y-auto px-4 py-6"
            >
                <div
                    class="fixed inset-0 bg-slate-900/60 backdrop-blur-md"
                    @click="closeConfirm()"
                ></div>

                <div class="relative z-10 flex min-h-full items-center justify-center py-6">
                    <div
                        x-show="showConfirmModal"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95 translate-y-3"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                        x-transition:leave-end="opacity-0 scale-95 translate-y-3"
                        class="w-full max-w-xl overflow-hidden rounded-[2rem] border border-white/70 bg-white shadow-2xl"
                    >
                        <div
                            class="px-6 py-5 text-white"
                            :class="confirmType === 'delete'
                                ? 'bg-gradient-to-r from-red-700 to-rose-500'
                                : 'bg-gradient-to-r from-amber-600 to-orange-500'"
                        >
                            <div class="flex items-start gap-4">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white/20">
                                    <span class="material-symbols-outlined" x-text="confirmType === 'delete' ? 'delete_forever' : 'edit_note'"></span>
                                </div>

                                <div>
                                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-white/80">
                                        Konfirmasi Data Master
                                    </p>

                                    <h3 class="mt-1 text-lg font-bold" x-text="confirmType === 'delete' ? 'Konfirmasi Hapus DDC' : 'Konfirmasi Edit DDC'"></h3>

                                    <p class="mt-1 text-sm text-white/85">
                                        Tindakan ini berkaitan dengan klasifikasi buku induk.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-5 p-6">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">
                                    DDC yang dipilih
                                </p>

                                <p class="mt-2 font-mono text-base font-bold text-slate-900">
                                    <span x-text="selectedDdc.code"></span>
                                    <span class="font-sans">-</span>
                                    <span class="font-sans" x-text="selectedDdc.name"></span>
                                </p>

                                <p class="mt-2 text-xs font-semibold text-slate-500">
                                    Digunakan oleh <span x-text="selectedDdc.books_count"></span> buku induk.
                                </p>
                            </div>

                            <div
                                x-show="confirmType === 'edit'"
                                class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-amber-800"
                            >
                                <p class="text-sm font-bold">
                                    Perhatian sebelum mengedit DDC
                                </p>
                                <p class="mt-2 text-sm leading-6">
                                    Kode dan nama DDC merupakan rujukan klasifikasi untuk buku induk.
                                    Perubahan data ini dapat memengaruhi tampilan klasifikasi pada buku yang sudah terhubung.
                                    Lanjutkan hanya jika perubahan sudah benar dan sesuai standar perpustakaan.
                                </p>
                            </div>

                            <div
                                x-show="confirmType === 'delete'"
                                class="rounded-2xl border border-red-200 bg-red-50 p-4 text-red-800"
                            >
                                <p class="text-sm font-bold">
                                    Perhatian sebelum menghapus DDC
                                </p>
                                <p class="mt-2 text-sm leading-6">
                                    DDC adalah data master untuk buku induk. Jika DDC ini masih digunakan oleh buku,
                                    sistem akan menolak penghapusan agar data koleksi tetap aman.
                                    Hapus hanya jika DDC ini sudah tidak digunakan lagi.
                                </p>
                            </div>

                            <div class="flex flex-col-reverse gap-3 border-t border-gray-100 pt-5 sm:flex-row sm:justify-end">
                                <button
                                    type="button"
                                    @click="closeConfirm()"
                                    class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-600 transition hover:bg-gray-50"
                                >
                                    Batal
                                </button>

                                <button
                                    type="button"
                                    @click="confirmAction()"
                                    class="inline-flex items-center justify-center gap-2 rounded-2xl px-6 py-3 text-sm font-bold text-white shadow-lg transition"
                                    :class="confirmType === 'delete'
                                        ? 'bg-red-600 shadow-red-600/20 hover:bg-red-700'
                                        : 'bg-amber-600 shadow-amber-600/20 hover:bg-amber-700'"
                                >
                                    <span x-text="confirmType === 'delete' ? 'Ya, Hapus DDC' : 'Ya, Lanjut Edit'"></span>
                                    <span class="material-symbols-outlined text-[18px]" x-text="confirmType === 'delete' ? 'delete' : 'arrow_forward'"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <style>
                [x-cloak] {
                    display: none !important;
                }
            </style>
        </div>
    </x-app-layout>
