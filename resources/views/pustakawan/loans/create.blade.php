@php
    $membersData = $members->map(function ($member) {
        return [
            'id' => (string) $member->id,
            'name' => $member->name,
            'nis_nip' => $member->nis_nip,
            'class' => $member->studentClass ? $member->studentClass->class_name : 'Guru/Staff',
        ];
    })->values();

    $classesData = $classes->map(function ($class) {
        return [
            'id' => (string) $class->id,
            'class_name' => $class->class_name,
            'academic_year' => $class->academic_year,
        ];
    })->values();

    $bookItemsData = $bookItems->map(function ($item) {
        $itemCode = $item->item_code ?? '-';
        $title = $item->book->title ?? '-';
        $author = $item->book->author ?? '-';
        $condition = $item->condition ?? '-';
        $status = $item->status ?? 'tersedia';

        return [
            'id' => (string) $item->id,
            'item_code' => $itemCode,
            'title' => $title,
            'author' => $author,
            'condition' => $condition,
            'status' => $status,
            'label' => $itemCode . ' - ' . $title . ' (' . ucwords($condition) . ')',
            'search' => mb_strtolower($itemCode . ' ' . $title . ' ' . $author . ' ' . $condition),
        ];
    })->values();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
                Peminjaman Buku
            </p>
            <h2 class="text-xl font-bold text-gray-900 leading-tight">
                Form Peminjaman Baru
            </h2>
            <p class="text-sm text-gray-500">
                Pilih anggota, tentukan buku yang dipinjam, lalu sistem akan memvalidasi transaksi secara otomatis.
            </p>
        </div>
    </x-slot>

    <div
        class="py-10 bg-gradient-to-br from-slate-50 via-emerald-50/40 to-sky-50/40 min-h-screen"
        x-data="loanCreateForm()"
        x-init="init()"
        @keydown.escape.window="
            showMemberDropdown = false;
            bookDropdownOpen = [false, false, false];
            showModal = false;
        "
    >
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            @if ($errors->any())
                <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 shadow-sm">
                    <div class="font-bold mb-2">Terjadi kesalahan:</div>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/75 backdrop-blur-xl shadow-[0_20px_60px_rgba(15,23,42,0.08)]">

                <div class="relative overflow-hidden bg-gradient-to-r from-emerald-700 to-teal-500 p-6 text-white">
                    <div class="absolute -right-16 -top-20 h-52 w-52 rounded-full bg-white/10 blur-2xl"></div>
                    <div class="absolute -left-20 bottom-0 h-48 w-48 rounded-full bg-emerald-200/20 blur-2xl"></div>

                    <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/20 text-white shadow-sm">
                                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">
                                    assignment_add
                                </span>
                            </div>

                            <div>
                                <h3 class="text-lg font-bold">
                                    Input Transaksi Peminjaman
                                </h3>
                                <p class="mt-1 text-sm text-emerald-50">
                                    Maksimal 3 buku, masa pinjam 3 hari, dan hanya untuk anggota aktif.
                                </p>
                            </div>
                        </div>

                        <a
                            href="{{ route('loans.index') }}"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl border border-white/25 bg-white/15 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-white/25"
                        >
                            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                            Kembali
                        </a>
                    </div>
                </div>

                <div class="p-6 md:p-8">
                    <form method="POST" action="{{ route('loans.store') }}" class="space-y-8" @submit="validateBeforeSubmit($event)">
                        @csrf

                        <section class="rounded-3xl border border-emerald-100 bg-emerald-50/50 p-5 md:p-6">
                            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                                            <span class="material-symbols-outlined text-[20px]">person</span>
                                        </div>
                                        <h4 class="font-bold text-gray-900">
                                            Data Peminjam
                                        </h4>
                                    </div>
                                    <p class="mt-2 text-sm text-gray-500">
                                        Cari anggota berdasarkan nama atau NIS/NIP.
                                    </p>
                                </div>

                                <button
                                    type="button"
                                    @click="showModal = true"
                                    class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-white px-4 py-2.5 text-sm font-bold text-emerald-700 shadow-sm transition hover:bg-emerald-50"
                                >
                                    <span class="material-symbols-outlined text-[18px]">person_add</span>
                                    Daftar Anggota Kilat
                                </button>
                            </div>

                            <input type="hidden" name="member_id" :value="selectedMemberId">

                            <div
                                x-show="selectedMemberId"
                                x-transition
                                class="flex flex-col gap-3 rounded-2xl border border-emerald-200 bg-white p-4 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <div class="flex items-start gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                                        <span class="material-symbols-outlined">verified_user</span>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-emerald-700">
                                            Anggota Dipilih
                                        </p>
                                        <p class="mt-1 text-sm font-bold text-gray-900" x-text="selectedMemberText"></p>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    @click="selectedMemberId = ''; selectedMemberText = ''; searchMember = '';"
                                    class="inline-flex items-center justify-center gap-1 rounded-xl bg-red-50 px-3 py-2 text-xs font-bold text-red-600 transition hover:bg-red-100"
                                >
                                    <span class="material-symbols-outlined text-[16px]">close</span>
                                    Ganti
                                </button>
                            </div>

                            <div x-show="!selectedMemberId" class="relative" @click.away="showMemberDropdown = false">
                                <div class="relative">
                                    <span class="material-symbols-outlined pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-emerald-600">
                                        search
                                    </span>
                                    <input
                                        type="text"
                                        x-model="searchMember"
                                        @focus="showMemberDropdown = true"
                                        @input="showMemberDropdown = true"
                                        placeholder="Ketik nama anggota atau NIS/NIP..."
                                        class="w-full rounded-2xl border border-emerald-200 bg-white px-12 py-3.5 text-sm text-gray-800 shadow-sm transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                                    >
                                </div>

                                <div
                                    x-show="showMemberDropdown && filteredMembers.length > 0"
                                    x-transition
                                    class="absolute z-50 mt-2 max-h-64 w-full overflow-y-auto rounded-2xl border border-gray-100 bg-white shadow-xl divide-y divide-gray-50"
                                >
                                    <template x-for="member in filteredMembers" :key="member.id">
                                        <button
                                            type="button"
                                            @click="selectMember(member)"
                                            class="flex w-full items-center justify-between gap-4 px-5 py-3 text-left transition hover:bg-emerald-50"
                                        >
                                            <div>
                                                <p class="text-sm font-bold text-gray-900" x-text="member.name"></p>
                                                <p class="mt-0.5 text-xs text-gray-500" x-text="'NIS/NIP: ' + member.nis_nip"></p>
                                            </div>
                                            <span class="rounded-xl bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700" x-text="member.class"></span>
                                        </button>
                                    </template>
                                </div>

                                <div
                                    x-show="showMemberDropdown && searchMember.length > 0 && filteredMembers.length === 0"
                                    x-transition
                                    class="absolute z-50 mt-2 w-full rounded-2xl border border-gray-100 bg-white p-5 text-center text-sm text-gray-500 shadow-xl"
                                >
                                    Anggota tidak ditemukan. Klik
                                    <span class="font-bold text-emerald-700">Daftar Anggota Kilat</span>
                                    untuk menambahkan data baru.
                                </div>
                            </div>

                            @error('member_id')
                                <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </section>

                        <section class="rounded-3xl border border-gray-100 bg-white p-5 md:p-6 shadow-sm">
                            <div class="mb-5">
                                <div class="flex items-center gap-2">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                                        <span class="material-symbols-outlined text-[20px]">menu_book</span>
                                    </div>
                                    <h4 class="font-bold text-gray-900">
                                        Buku yang Dipinjam
                                    </h4>
                                </div>
                                <p class="mt-2 text-sm text-gray-500">
                                    Ketik judul, kode eksemplar, penulis, atau kondisi buku. Maksimal 3 buku dan buku yang sudah dipilih tidak bisa dipilih ulang.
                                </p>
                            </div>

                            <div class="grid grid-cols-1 gap-4">
                                <template x-for="slot in [0, 1, 2]" :key="slot">
                                    <div class="relative" @click.away="bookDropdownOpen[slot] = false">
                                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                            <span x-text="'Buku ' + (slot + 1)"></span>
                                            <span x-show="slot === 0" class="text-red-500">*</span>
                                            <span x-show="slot > 0" class="font-medium normal-case tracking-normal text-gray-400">
                                                (Opsional)
                                            </span>
                                        </label>

                                        <input type="hidden" name="book_item_ids[]" :value="selectedBooks[slot]">

                                        <div class="relative">
                                            <span class="material-symbols-outlined pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-emerald-600">
                                                search
                                            </span>

                                            <input
                                                type="text"
                                                x-model="bookSearches[slot]"
                                                @focus="openBookDropdown(slot)"
                                                @input="handleBookTyping(slot)"
                                                :required="slot === 0"
                                                autocomplete="off"
                                                :placeholder="slot === 0 ? 'Ketik judul atau kode buku 1...' : 'Ketik judul atau kode buku opsional...'"
                                                class="w-full rounded-2xl border border-emerald-200 bg-emerald-50/50 px-12 py-3 text-sm text-gray-800 shadow-sm transition focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-200"
                                            >

                                            <button
                                                type="button"
                                                x-show="bookSearches[slot] || selectedBooks[slot]"
                                                @click="clearBook(slot)"
                                                class="absolute right-3 top-1/2 flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-full bg-red-50 text-red-600 transition hover:bg-red-100"
                                            >
                                                <span class="material-symbols-outlined text-[16px]">close</span>
                                            </button>
                                        </div>

                                        <div
                                            x-show="bookDropdownOpen[slot] && filteredBooks(slot).length > 0"
                                            x-transition
                                            class="absolute z-50 mt-2 max-h-72 w-full overflow-y-auto rounded-2xl border border-gray-100 bg-white shadow-xl divide-y divide-gray-50"
                                        >
                                            <template x-for="book in filteredBooks(slot)" :key="book.id">
                                                <button
                                                    type="button"
                                                    @click="selectBook(slot, book)"
                                                    class="flex w-full items-start justify-between gap-4 px-5 py-3 text-left transition hover:bg-emerald-50"
                                                >
                                                    <div>
                                                        <p class="text-sm font-bold text-gray-900" x-text="book.title"></p>
                                                        <p class="mt-1 text-xs text-gray-500">
                                                            <span class="font-mono font-bold" x-text="book.item_code"></span>
                                                            <span> — Penulis: </span>
                                                            <span x-text="book.author"></span>
                                                        </p>
                                                    </div>

                                                    <span
                                                        class="shrink-0 rounded-xl px-3 py-1 text-xs font-bold capitalize"
                                                        :class="book.status === 'tersedia' ? 'bg-emerald-50 text-emerald-700' : (book.status === 'dipinjam' ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-700')"
                                                        x-text="book.status === 'tersedia' ? 'Tersedia' : (book.status === 'dipinjam' ? 'Dipinjam' : book.status)">
                                                    </span>
                                                    <span class="ml-2 shrink-0 rounded-xl bg-emerald-50 px-3 py-1 text-xs font-bold capitalize text-emerald-700" x-text="book.condition"></span>
                                                </button>
                                            </template>
                                        </div>

                                        <div
                                            x-show="bookDropdownOpen[slot] && bookSearches[slot].length > 0 && filteredBooks(slot).length === 0"
                                            x-transition
                                            class="absolute z-50 mt-2 w-full rounded-2xl border border-gray-100 bg-white p-5 text-center text-sm text-gray-500 shadow-xl"
                                        >
                                            Buku tidak ditemukan atau sudah dipilih pada kolom lain.
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <div
                                x-show="bookValidationMessage"
                                x-transition
                                class="mt-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700"
                                x-text="bookValidationMessage"
                            ></div>

                            @error('book_item_ids')
                                <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror

                            @error('book_item_ids.*')
                                <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </section>

                        <section class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm">
                                <label class="mb-2 block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                    Tanggal Pinjam
                                </label>
                                <input
                                    type="date"
                                    name="loan_date"
                                    value="{{ old('loan_date', date('Y-m-d')) }}"
                                    class="w-full rounded-2xl border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 shadow-sm transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                                >
                                <p class="mt-2 text-xs text-gray-400">
                                    Tanggal pinjam default hari ini; ubah untuk tes keterlambatan atau pengembalian tertunda.
                                </p>
                            </div>

                            <div class="rounded-3xl border border-emerald-100 bg-emerald-50/60 p-5 shadow-sm">
                                <label class="mb-2 block text-xs font-bold uppercase tracking-[0.12em] text-emerald-700">
                                    Batas Waktu Pengembalian
                                </label>
                                <input
                                    type="date"
                                    name="due_date"
                                    value="{{ old('due_date', date('Y-m-d', strtotime('+3 days'))) }}"
                                    required
                                    class="w-full rounded-2xl border-emerald-300 bg-white px-4 py-3 text-sm font-bold text-emerald-800 shadow-sm transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                                >
                                <p class="mt-2 text-xs text-emerald-700">
                                    Masa pinjam default adalah 3 hari. Ubah tanggal jatuh tempo untuk menguji denda keterlambatan.
                                </p>
                                @error('due_date')
                                    <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </section>

                        <div class="flex flex-col-reverse gap-3 border-t border-gray-100 pt-6 sm:flex-row sm:items-center sm:justify-end">
                            <a
                                href="{{ route('loans.index') }}"
                                class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-6 py-3 text-sm font-bold text-gray-600 transition hover:bg-gray-50"
                            >
                                Batal
                            </a>

                            <button
                                type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-700/20 transition hover:bg-emerald-800"
                            >
                                <span>Proses Peminjaman</span>
                                <span class="material-symbols-outlined text-[18px]">send</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div
            x-show="showModal"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto px-4 py-6"
            x-transition.opacity
            style="display: none;"
        >
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showModal = false"></div>

            <div
                class="relative z-50 w-full max-w-lg overflow-hidden rounded-[2rem] border border-white/70 bg-white shadow-2xl"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95 translate-y-3"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            >
                <div class="bg-gradient-to-r from-emerald-700 to-teal-500 p-5 text-white">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white/20">
                                <span class="material-symbols-outlined">person_add</span>
                            </div>
                            <div>
                                <h3 class="text-base font-bold">Registrasi Anggota Kilat</h3>
                                <p class="text-xs text-emerald-50">Tambahkan anggota tanpa meninggalkan form peminjaman.</p>
                            </div>
                        </div>

                        <button
                            type="button"
                            @click="showModal = false"
                            class="flex h-9 w-9 items-center justify-center rounded-full bg-white/15 transition hover:bg-white/25"
                        >
                            ✕
                        </button>
                    </div>
                </div>

                <div class="p-6 space-y-5">
                    <div
                        x-show="modalErrorMessage"
                        x-transition
                        class="rounded-2xl border border-red-100 bg-red-50 p-3 text-xs font-medium text-red-700"
                        x-text="modalErrorMessage"
                    ></div>

                    <div class="space-y-4 text-sm">
                        <div>
                            <label class="block font-semibold text-gray-700">
                                NIS / NIP <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                x-model="newNisNip"
                                placeholder="Masukkan nomor identitas unik..."
                                class="mt-1.5 block w-full rounded-2xl border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                            >
                        </div>

                        <div>
                            <label class="block font-semibold text-gray-700">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                x-model="newName"
                                placeholder="Nama lengkap anggota..."
                                class="mt-1.5 block w-full rounded-2xl border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                            >
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block font-semibold text-gray-700">
                                    Jenis Kelamin <span class="text-red-500">*</span>
                                </label>
                                <select
                                    x-model="newGender"
                                    class="mt-1.5 block w-full rounded-2xl border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                                >
                                    <option value="">-- Pilih --</option>
                                    <option value="laki-laki">Laki-laki</option>
                                    <option value="perempuan">Perempuan</option>
                                </select>
                            </div>

                            <div>
                                <label class="block font-semibold text-gray-700">
                                    Tipe Anggota <span class="text-red-500">*</span>
                                </label>
                                <select
                                    x-model="newMemberType"
                                    @change="if(newMemberType === 'guru') newClassId = ''"
                                    class="mt-1.5 block w-full rounded-2xl border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                                >
                                    <option value="">-- Pilih --</option>
                                    <option value="siswa">Siswa</option>
                                    <option value="guru">Guru</option>
                                </select>
                            </div>
                        </div>

                        <div x-show="newMemberType === 'siswa'" x-transition>
                            <label class="block font-semibold text-gray-700">
                                Kelas Siswa <span class="text-red-500">*</span>
                            </label>
                            <select
                                x-model="newClassId"
                                class="mt-1.5 block w-full rounded-2xl border-emerald-200 bg-emerald-50/60 px-4 py-3 text-sm transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                            >
                                <option value="">-- Pilih Kelas --</option>
                                <template x-for="cls in classesData" :key="cls.id">
                                    <option :value="cls.id" x-text="cls.class_name + ' (' + cls.academic_year + ')'"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-gray-100 pt-5 sm:flex-row sm:justify-end">
                        <button
                            type="button"
                            @click="showModal = false"
                            class="rounded-2xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-bold text-gray-600 transition hover:bg-gray-50"
                        >
                            Batal
                        </button>

                        <button
                            type="button"
                            @click="submitQuickMember()"
                            class="rounded-2xl bg-emerald-700 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-emerald-700/20 transition hover:bg-emerald-800"
                        >
                            Daftarkan Anggota
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <style>
            [x-cloak] {
                display: none !important;
            }
        </style>

        <script>
            function loanCreateForm() {
                return {
                    searchMember: '',
                    selectedMemberId: @js((string) old('member_id', '')),
                    selectedMemberText: '',
                    showMemberDropdown: false,

                    membersList: @js($membersData),
                    booksList: @js($bookItemsData),

                    selectedBooks: [
                        @js((string) old('book_item_ids.0', '')),
                        @js((string) old('book_item_ids.1', '')),
                        @js((string) old('book_item_ids.2', '')),
                    ],
                    bookSearches: ['', '', ''],
                    bookDropdownOpen: [false, false, false],
                    bookValidationMessage: '',

                    showModal: false,
                    newNisNip: '',
                    newName: '',
                    newGender: '',
                    newMemberType: '',
                    newClassId: '',
                    classesData: @js($classesData),
                    modalErrorMessage: '',

                    init() {
                        const selectedMember = this.membersList.find(member => String(member.id) === String(this.selectedMemberId));

                        if (selectedMember) {
                            this.selectedMemberText = `${selectedMember.nis_nip} - ${selectedMember.name} (${selectedMember.class})`;
                        }

                        this.selectedBooks = this.selectedBooks.map(value => String(value || ''));

                        this.selectedBooks.forEach((bookId, index) => {
                            const book = this.findBook(bookId);

                            if (book) {
                                this.bookSearches[index] = book.label;
                            }
                        });
                    },

                    normalizeText(value) {
                        return String(value || '')
                            .toLowerCase()
                            .normalize('NFD')
                            .replace(/[\u0300-\u036f]/g, '')
                            .trim();
                    },

                    get filteredMembers() {
                        const keyword = this.normalizeText(this.searchMember);

                        if (!keyword) {
                            return [];
                        }

                        return this.membersList
                            .filter(member => {
                                const text = this.normalizeText(`${member.name} ${member.nis_nip} ${member.class}`);
                                return text.includes(keyword);
                            })
                            .slice(0, 7);
                    },

                    selectMember(member) {
                        this.selectedMemberId = String(member.id);
                        this.selectedMemberText = `${member.nis_nip} - ${member.name} (${member.class})`;
                        this.searchMember = '';
                        this.showMemberDropdown = false;
                    },

                    findBook(bookId) {
                        if (!bookId) {
                            return null;
                        }

                        return this.booksList.find(book => String(book.id) === String(bookId)) || null;
                    },

                    openBookDropdown(index) {
                        this.bookValidationMessage = '';
                        this.bookDropdownOpen = this.bookDropdownOpen.map((_, slot) => slot === index);
                    },

                    handleBookTyping(index) {
                        this.selectedBooks[index] = '';
                        this.bookValidationMessage = '';
                        this.bookDropdownOpen[index] = true;
                    },

                    filteredBooks(index) {
                        const keyword = this.normalizeText(this.bookSearches[index]);

                        return this.booksList
                            .filter(book => {
                                const notSelectedElsewhere = !this.isBookSelected(book.id, index);
                                const matchKeyword = keyword === '' || this.normalizeText(book.search).includes(keyword);

                                return notSelectedElsewhere && matchKeyword;
                            })
                            .slice(0, 40);
                    },

                    selectBook(index, book) {
                        this.selectedBooks[index] = String(book.id);
                        this.bookSearches[index] = book.label;
                        this.bookDropdownOpen[index] = false;
                        this.bookValidationMessage = '';
                    },

                    clearBook(index) {
                        this.selectedBooks[index] = '';
                        this.bookSearches[index] = '';
                        this.bookDropdownOpen[index] = false;
                        this.bookValidationMessage = '';
                    },

                    isBookSelected(bookId, currentIndex) {
                        return this.selectedBooks.some((selectedBookId, index) => {
                            return index !== currentIndex && String(selectedBookId) === String(bookId);
                        });
                    },

                    validateBeforeSubmit(event) {
                        this.bookValidationMessage = '';

                        if (!this.selectedMemberId) {
                            event.preventDefault();
                            this.showMemberDropdown = true;
                            return;
                        }

                        if (!this.selectedBooks[0]) {
                            event.preventDefault();
                            this.bookValidationMessage = 'Buku 1 wajib dipilih dari daftar pencarian.';
                            this.bookDropdownOpen[0] = true;
                            return;
                        }

                        for (let index = 0; index < this.bookSearches.length; index++) {
                            const typed = this.bookSearches[index] && this.bookSearches[index].trim() !== '';
                            const selected = this.selectedBooks[index] && String(this.selectedBooks[index]).trim() !== '';

                            if (typed && !selected) {
                                event.preventDefault();
                                this.bookValidationMessage = `Buku ${index + 1} belum dipilih dari daftar. Klik salah satu hasil pencarian terlebih dahulu.`;
                                this.bookDropdownOpen[index] = true;
                                return;
                            }
                        }

                        const selectedOnly = this.selectedBooks.filter(value => value);

                        if (new Set(selectedOnly).size !== selectedOnly.length) {
                            event.preventDefault();
                            this.bookValidationMessage = 'Buku yang sama tidak boleh dipilih lebih dari satu kali.';
                        }
                    },

                    async submitQuickMember() {
                        this.modalErrorMessage = '';

                        if (!this.newNisNip || !this.newName || !this.newGender || !this.newMemberType) {
                            this.modalErrorMessage = 'Mohon lengkapi semua kolom wajib.';
                            return;
                        }

                        if (this.newMemberType === 'siswa' && !this.newClassId) {
                            this.modalErrorMessage = 'Siswa wajib memilih kelas.';
                            return;
                        }

                        try {
                            const response = await fetch('{{ route('members.store') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({
                                    nis_nip: this.newNisNip,
                                    name: this.newName,
                                    gender: this.newGender,
                                    member_type: this.newMemberType,
                                    student_class_id: this.newClassId,
                                    status: 'aktif',
                                }),
                            });

                            const result = await response.json();

                            if (response.ok && result.success) {
                                const newAddedMember = {
                                    id: String(result.member.id),
                                    name: result.member.name,
                                    nis_nip: result.member.nis_nip,
                                    class: result.class_name,
                                };

                                this.membersList.push(newAddedMember);
                                this.selectMember(newAddedMember);

                                this.newNisNip = '';
                                this.newName = '';
                                this.newGender = '';
                                this.newMemberType = '';
                                this.newClassId = '';
                                this.showModal = false;
                            } else {
                                if (result.errors && result.errors.nis_nip) {
                                    this.modalErrorMessage = 'Gagal: NIS/NIP ini sudah terdaftar di sistem.';
                                } else {
                                    this.modalErrorMessage = result.message || 'Gagal menyimpan data anggota.';
                                }
                            }
                        } catch (error) {
                            this.modalErrorMessage = 'Terjadi kesalahan sistem. Silakan refresh halaman atau coba lagi.';
                        }
                    },
                };
            }
        </script>
    </div>
</x-app-layout>