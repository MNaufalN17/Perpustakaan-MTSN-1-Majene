<x-app-layout>
    @php
        $classCollection = collect($studentClasses ?? $classes ?? []);

        if ($classCollection->isEmpty()) {
            try {
                $classCollection = \App\Models\StudentClass::orderBy('level')
                    ->orderBy('class_name')
                    ->get();
            } catch (\Throwable $e) {
                $classCollection = collect();
            }
        }

        $normalMaxLoanItems = (int) ($normalMaxLoanItems ?? \App\Models\SystemSetting::intValue('max_normal_loan_items', 3));
        $loanDurationDays = (int) ($loanDurationDays ?? \App\Models\SystemSetting::intValue('loan_duration_days', 7));

        $borrowedIds = collect($borrowedBookItemIds ?? [])
            ->map(fn ($id) => (string) $id)
            ->values();

        $memberOptions = collect($members ?? [])->map(function ($member) {
            $className = $member->studentClass->class_name ?? 'Guru/Staff';

            return [
                'id' => (string) $member->id,
                'name' => $member->name,
                'nis_nip' => $member->nis_nip,
                'member_code' => $member->member_code,
                'member_type' => $member->member_type,
                'class' => $className,
                'search' => strtolower(
                    ($member->name ?? '') . ' ' .
                    ($member->nis_nip ?? '') . ' ' .
                    ($member->member_code ?? '') . ' ' .
                    ($member->member_type ?? '') . ' ' .
                    $className
                ),
            ];
        })->values();

        $bookItemOptions = collect($bookItems ?? [])->map(function ($item) use ($borrowedIds) {
            $titleCode = $item->title_code ?? $item->title_initial ?? null;

            return [
                'id' => (string) $item->id,
                'item_code' => $item->item_code,
                'copy_number' => $item->copy_number,
                'status' => $item->status,
                'condition' => $item->condition,
                'book_title' => $item->book->title ?? '-',
                'author' => $item->book->author ?? '-',
                'publisher' => $item->book->publisher ?? '-',
                'is_borrowed_active' => $borrowedIds->contains((string) $item->id),
                'search' => strtolower(
                    ($item->item_code ?? '') . ' ' .
                    ($item->copy_number ?? '') . ' ' .
                    ($item->status ?? '') . ' ' .
                    ($item->condition ?? '') . ' ' .
                    ($item->book->title ?? '') . ' ' .
                    ($item->book->author ?? '') . ' ' .
                    ($item->book->publisher ?? '')
                ),
            ];
        })->values();
    @endphp

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
                    Buat Peminjaman Buku
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Peminjaman biasa maksimal {{ $normalMaxLoanItems }} eksemplar. Batas ini diatur oleh Admin IT.
                </p>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row">
                <a href="{{ route('loans.class_bulk.create') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-2xl bg-amber-500 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-amber-500/20 transition hover:bg-amber-600">
                    <span class="material-symbols-outlined text-[18px]">groups</span>
                    Peminjaman Kelas
                </a>

                <a href="{{ route('loans.index') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-600 transition hover:bg-gray-50">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                    Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div
        class="min-h-screen bg-gradient-to-br from-slate-50 via-emerald-50/40 to-sky-50/40 py-10"
        x-data="loanCreateForm(
            @js($memberOptions),
            @js($bookItemOptions),
            @js([
                'quickMemberUrl' => url('/members/quick-store'),
                'csrfToken' => csrf_token(),
                'oldMemberId' => old('member_id'),
                'oldBookItemIds' => old('book_item_ids', []),
                'maxNormalLoanItems' => $normalMaxLoanItems,
            ])
        )"
    >
        <div class="mx-auto max-w-6xl sm:px-6 lg:px-8">

            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                    <div class="mb-2 font-bold">Terjadi kesalahan:</div>

                    <ul class="list-disc space-y-1 pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form
                method="POST"
                action="{{ route('loans.store') }}"
                class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/90 shadow-[0_20px_60px_rgba(15,23,42,0.08)] backdrop-blur-xl"
                @submit="validateMainForm"
            >
                @csrf

                <div class="relative overflow-hidden bg-gradient-to-r from-emerald-700 to-teal-500 p-6 text-white">
                    <div class="absolute -right-16 -top-20 h-52 w-52 rounded-full bg-white/10 blur-2xl"></div>
                    <div class="absolute -left-20 bottom-0 h-48 w-48 rounded-full bg-emerald-200/20 blur-2xl"></div>

                    <div class="relative flex items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/20">
                            <span class="material-symbols-outlined text-[26px]">assignment_add</span>
                        </div>

                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-50">
                                Form Peminjaman
                            </p>

                            <h3 class="mt-2 text-2xl font-extrabold leading-tight">
                                Data Transaksi Baru
                            </h3>

                            <p class="mt-1 text-sm text-emerald-50">
                                Untuk peminjaman banyak copy satu kelas, gunakan tombol Peminjaman Kelas.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="space-y-6 p-6">
                    <div
                        x-show="formError"
                        x-cloak
                        class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700"
                        x-text="formError"
                    ></div>

                    <section class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm">
                        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h4 class="font-bold text-gray-900">
                                    Pilih Anggota
                                </h4>

                                <p class="mt-1 text-xs text-gray-500">
                                    Cari berdasarkan nama, NIS/NIP, kode anggota, atau kelas.
                                </p>
                            </div>

                            <button
                                type="button"
                                @click="openQuickMemberModal()"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-700/20 transition hover:bg-emerald-800"
                            >
                                <span class="material-symbols-outlined text-[18px]">person_add</span>
                                Registrasi Anggota Kilat
                            </button>
                        </div>

                        <input type="hidden" name="member_id" :value="selectedMember ? selectedMember.id : ''">

                        <div class="relative">
                            <span class="material-symbols-outlined pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[20px] text-gray-400">
                                search
                            </span>

                            <input
                                type="text"
                                x-model="memberSearch"
                                @focus="memberDropdownOpen = true"
                                @input="memberDropdownOpen = true"
                                placeholder="Ketik nama anggota, NIS/NIP, atau kelas..."
                                class="w-full rounded-2xl border border-gray-200 bg-slate-50 px-12 py-3 text-sm focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-100"
                                autocomplete="off"
                            >

                            <button
                                type="button"
                                x-show="selectedMember"
                                x-cloak
                                @click="clearSelectedMember()"
                                class="absolute right-3 top-1/2 -translate-y-1/2 rounded-xl px-2 py-1 text-xs font-bold text-gray-500 hover:bg-gray-100"
                            >
                                Reset
                            </button>

                            <div
                                x-show="memberDropdownOpen"
                                x-cloak
                                @click.outside="memberDropdownOpen = false"
                                class="absolute z-30 mt-2 max-h-72 w-full overflow-y-auto rounded-2xl border border-gray-100 bg-white p-2 shadow-2xl"
                            >
                                <template x-for="member in filteredMembers()" :key="member.id">
                                    <button
                                        type="button"
                                        @click="selectMember(member)"
                                        class="flex w-full items-start justify-between gap-3 rounded-xl px-4 py-3 text-left transition hover:bg-emerald-50"
                                    >
                                        <div>
                                            <p class="font-bold text-gray-900" x-text="member.name"></p>

                                            <p class="mt-1 text-xs text-gray-500">
                                                <span x-text="member.nis_nip || '-'"></span>
                                                <span> — </span>
                                                <span x-text="member.class || 'Guru/Staff'"></span>
                                            </p>
                                        </div>

                                        <span
                                            class="rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700"
                                            x-text="formatText(member.member_type || '-')"
                                        ></span>
                                    </button>
                                </template>

                                <div
                                    x-show="filteredMembers().length === 0"
                                    class="px-4 py-8 text-center text-sm text-gray-500"
                                >
                                    Anggota tidak ditemukan.
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm">
                        <h4 class="font-bold text-gray-900">
                            Tanggal Peminjaman
                        </h4>

                        <div class="mt-5 grid gap-4 md:grid-cols-2">
                            <div>
                                <label for="loan_date" class="block text-sm font-bold text-gray-700">
                                    Tanggal Pinjam
                                </label>

                                <input
                                    id="loan_date"
                                    name="loan_date"
                                    type="date"
                                    value="{{ old('loan_date', now()->format('Y-m-d')) }}"
                                    required
                                    class="mt-2 block w-full rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                >
                            </div>

                            <div>
                                <label for="due_date" class="block text-sm font-bold text-gray-700">
                                    Batas Kembali
                                </label>

                                <input
                                    id="due_date"
                                    name="due_date"
                                    type="date"
                                    value="{{ old('due_date', now()->addDays($loanDurationDays)->format('Y-m-d')) }}"
                                    required
                                    class="mt-2 block w-full rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                >
                            </div>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm">
                        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h4 class="font-bold text-gray-900">
                                    Pilih Buku
                                </h4>

                                <p class="mt-1 text-xs text-gray-500">
                                    Maksimal <span class="font-bold">{{ $normalMaxLoanItems }}</span> eksemplar untuk peminjaman biasa.
                                </p>
                            </div>

                            <button
                                type="button"
                                @click="addBookRow()"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700 transition hover:bg-emerald-100"
                            >
                                <span class="material-symbols-outlined text-[18px]">add_circle</span>
                                Tambah Baris Buku
                            </button>
                        </div>

                        <div class="space-y-4">
                            <template x-for="(row, index) in bookRows" :key="row.key">
                                <div class="rounded-3xl border border-gray-100 bg-slate-50 p-4">
                                    <div class="mb-3 flex items-center justify-between gap-3">
                                        <p class="text-sm font-bold text-gray-800">
                                            Buku <span x-text="index + 1"></span>
                                        </p>

                                        <button
                                            type="button"
                                            x-show="bookRows.length > 1"
                                            x-cloak
                                            @click="removeBookRow(index)"
                                            class="inline-flex items-center gap-1 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-bold text-red-700 transition hover:bg-red-100"
                                        >
                                            <span class="material-symbols-outlined text-[15px]">delete</span>
                                            Hapus
                                        </button>
                                    </div>

                                    <template x-if="row.book_item_id">
                                        <input type="hidden" name="book_item_ids[]" :value="row.book_item_id">
                                    </template>

                                    <div class="relative">
                                        <span class="material-symbols-outlined pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[20px] text-gray-400">
                                            search
                                        </span>

                                        <input
                                            type="text"
                                            x-model="row.search"
                                            @focus="row.open = true"
                                            @input="row.open = true"
                                            placeholder="Ketik judul buku, kode eksemplar, atau penulis..."
                                            class="w-full rounded-2xl border border-gray-200 bg-white px-12 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                            autocomplete="off"
                                        >

                                        <button
                                            type="button"
                                            x-show="row.book_item_id"
                                            x-cloak
                                            @click="clearBookRow(row)"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 rounded-xl px-2 py-1 text-xs font-bold text-gray-500 hover:bg-gray-100"
                                        >
                                            Reset
                                        </button>

                                        <div
                                            x-show="row.open"
                                            x-cloak
                                            @click.outside="row.open = false"
                                            class="absolute z-20 mt-2 max-h-72 w-full overflow-y-auto rounded-2xl border border-gray-100 bg-white p-2 shadow-2xl"
                                        >
                                            <template x-for="item in filteredBookItems(row)" :key="item.id">
                                                <button
                                                    type="button"
                                                    @click="selectBook(row, item)"
                                                    class="flex w-full items-start justify-between gap-3 rounded-xl px-4 py-3 text-left transition hover:bg-emerald-50"
                                                >
                                                    <div>
                                                        <p class="font-bold text-gray-900" x-text="item.book_title"></p>

                                                        <p class="mt-1 text-xs text-gray-500">
                                                            <span x-text="item.item_code || '-'"></span>
                                                            <span> — Copy </span>
                                                            <span x-text="item.copy_number || '-'"></span>
                                                            <span> — </span>
                                                            <span x-text="item.author || '-'"></span>
                                                        </p>
                                                    </div>

                                                    <span class="rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                                                        Tersedia
                                                    </span>
                                                </button>
                                            </template>

                                            <div
                                                x-show="filteredBookItems(row).length === 0"
                                                class="px-4 py-8 text-center text-sm text-gray-500"
                                            >
                                                Buku tidak ditemukan atau sudah dipilih.
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        x-show="selectedBook(row)"
                                        x-cloak
                                        class="mt-4 rounded-2xl border border-emerald-100 bg-white p-4"
                                    >
                                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-emerald-700">
                                            Buku Terpilih
                                        </p>

                                        <p class="mt-2 font-extrabold text-gray-900" x-text="selectedBook(row)?.book_title"></p>

                                        <p class="mt-1 text-sm text-gray-600">
                                            <span x-text="selectedBook(row)?.item_code || '-'"></span>
                                            <span> — Copy </span>
                                            <span x-text="selectedBook(row)?.copy_number || '-'"></span>
                                        </p>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm">
                        <label for="notes" class="block text-sm font-bold text-gray-700">
                            Catatan
                        </label>

                        <textarea
                            id="notes"
                            name="notes"
                            rows="3"
                            class="mt-2 block w-full rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                            placeholder="Opsional"
                        >{{ old('notes') }}</textarea>
                    </section>

                    <div class="flex flex-col-reverse gap-3 border-t border-gray-100 pt-6 sm:flex-row sm:justify-end">
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
                </div>
            </form>
        </div>

        <div
            x-show="showModal"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 px-4 py-8 backdrop-blur-sm"
        >
            <div
                @click.outside="closeQuickMemberModal()"
                class="w-full max-w-xl overflow-hidden rounded-[2rem] bg-white shadow-2xl"
            >
                <div class="flex items-center justify-between bg-gradient-to-r from-emerald-700 to-teal-500 px-6 py-5 text-white">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white/20">
                            <span class="material-symbols-outlined">person_add</span>
                        </div>

                        <div>
                            <h3 class="text-base font-extrabold">
                                Registrasi Anggota Kilat
                            </h3>

                            <p class="mt-0.5 text-xs font-medium text-emerald-50">
                                Tambahkan anggota tanpa meninggalkan form peminjaman.
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="closeQuickMemberModal()"
                        class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white/15 transition hover:bg-white/25"
                    >
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form class="space-y-5 p-6" @submit.prevent="submitQuickMember()">
                    <div
                        x-show="modalErrorMessage"
                        x-cloak
                        class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700"
                        x-text="modalErrorMessage"
                    ></div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700">
                            NIS / NIP <span class="text-red-500">*</span>
                        </label>

                        <input
                            type="text"
                            x-model="newNisNip"
                            required
                            class="mt-2 block w-full rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>

                        <input
                            type="text"
                            x-model="newName"
                            required
                            class="mt-2 block w-full rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                        >
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-bold text-gray-700">
                                Jenis Kelamin
                            </label>

                            <select
                                x-model="newGender"
                                class="mt-2 block w-full rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                            >
                                <option value="laki-laki">Laki-laki</option>
                                <option value="perempuan">Perempuan</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700">
                                Tipe Anggota
                            </label>

                            <select
                                x-model="newMemberType"
                                class="mt-2 block w-full rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                            >
                                <option value="siswa">Siswa</option>
                                <option value="guru">Guru</option>
                            </select>
                        </div>
                    </div>

                    <div x-show="newMemberType === 'siswa'" x-cloak>
                        <label class="block text-sm font-bold text-gray-700">
                            Kelas Siswa
                        </label>

                        <select
                            x-model="newClassId"
                            class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                        >
                            <option value="">Pilih kelas siswa</option>

                            @foreach($classCollection as $class)
                                <option value="{{ $class->id }}">
                                    {{ $class->class_name ?? $class->name ?? '-' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700">
                            No. HP / WhatsApp
                        </label>

                        <input
                            type="text"
                            x-model="newPhone"
                            class="mt-2 block w-full rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                            placeholder="Opsional"
                        >
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5">
                        <button
                            type="button"
                            @click="closeQuickMemberModal()"
                            class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-600 transition hover:bg-gray-50"
                        >
                            Batal
                        </button>

                        <button
                            type="submit"
                            :disabled="quickSaving"
                            class="inline-flex items-center justify-center rounded-2xl bg-emerald-700 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-700/20 transition hover:bg-emerald-800 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            <span x-show="!quickSaving">Daftarkan Anggota</span>
                            <span x-show="quickSaving" x-cloak>Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function loanCreateForm(members, bookItems, config) {
            return {
                membersList: members || [],
                bookItemsList: bookItems || [],
                config: config || {},

                memberSearch: '',
                memberDropdownOpen: false,
                selectedMember: null,

                bookRows: [
                    {
                        key: Date.now(),
                        book_item_id: '',
                        search: '',
                        open: false,
                    }
                ],

                formError: '',
                showModal: false,
                quickSaving: false,
                modalErrorMessage: '',

                newNisNip: '',
                newName: '',
                newGender: 'laki-laki',
                newMemberType: 'siswa',
                newClassId: '',
                newPhone: '',

                init() {
                    if (this.config.oldMemberId) {
                        const oldMember = this.membersList.find(member => String(member.id) === String(this.config.oldMemberId));

                        if (oldMember) {
                            this.selectMember(oldMember);
                        }
                    }

                    const oldBookItemIds = Array.isArray(this.config.oldBookItemIds)
                        ? this.config.oldBookItemIds
                        : [];

                    if (oldBookItemIds.length > 0) {
                        this.bookRows = oldBookItemIds.map((id) => {
                            const item = this.bookItemsList.find(bookItem => String(bookItem.id) === String(id));

                            return {
                                key: Date.now() + Math.random(),
                                book_item_id: item ? String(item.id) : '',
                                search: item ? `${item.book_title} - ${item.item_code || '-'}` : '',
                                open: false,
                            };
                        });
                    }
                },

                filteredMembers() {
                    const keyword = (this.memberSearch || '').toLowerCase().trim();

                    if (!keyword) {
                        return this.membersList.slice(0, 25);
                    }

                    return this.membersList
                        .filter(member => (member.search || '').includes(keyword))
                        .slice(0, 25);
                },

                selectMember(member) {
                    this.selectedMember = member;
                    this.memberSearch = `${member.name} - ${member.nis_nip || '-'}`;
                    this.memberDropdownOpen = false;
                    this.formError = '';
                },

                clearSelectedMember() {
                    this.selectedMember = null;
                    this.memberSearch = '';
                    this.memberDropdownOpen = false;
                },

                addBookRow() {
                    const max = parseInt(this.config.maxNormalLoanItems || 3);

                    if (this.bookRows.length >= max) {
                        this.formError = `Maksimal ${max} eksemplar untuk peminjaman biasa. Gunakan Peminjaman Kelas untuk jumlah besar.`;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        return;
                    }

                    this.bookRows.push({
                        key: Date.now() + Math.random(),
                        book_item_id: '',
                        search: '',
                        open: false,
                    });
                },

                removeBookRow(index) {
                    this.bookRows.splice(index, 1);

                    if (this.bookRows.length === 0) {
                        this.addBookRow();
                    }
                },

                selectedBook(row) {
                    return this.bookItemsList.find(item => String(item.id) === String(row.book_item_id)) || null;
                },

                selectedBookIdsExcept(currentRow) {
                    return this.bookRows
                        .filter(row => row !== currentRow && row.book_item_id)
                        .map(row => String(row.book_item_id));
                },

                filteredBookItems(row) {
                    const keyword = (row.search || '').toLowerCase().trim();
                    const selectedIds = this.selectedBookIdsExcept(row);

                    return this.bookItemsList
                        .filter(item => {
                            const status = String(item.status || '').toLowerCase();

                            return status === 'tersedia'
                                && !item.is_borrowed_active
                                && !selectedIds.includes(String(item.id))
                                && (!keyword || (item.search || '').includes(keyword));
                        })
                        .slice(0, 30);
                },

                selectBook(row, item) {
                    row.book_item_id = String(item.id);
                    row.search = `${item.book_title} - ${item.item_code || '-'}`;
                    row.open = false;
                    this.formError = '';
                },

                clearBookRow(row) {
                    row.book_item_id = '';
                    row.search = '';
                    row.open = false;
                },

                selectedBookCount() {
                    return this.bookRows.filter(row => row.book_item_id).length;
                },

                validateMainForm(event) {
                    this.formError = '';

                    const max = parseInt(this.config.maxNormalLoanItems || 3);

                    if (!this.selectedMember) {
                        event.preventDefault();
                        this.formError = 'Pilih anggota terlebih dahulu.';
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        return;
                    }

                    if (this.selectedBookCount() < 1) {
                        event.preventDefault();
                        this.formError = 'Minimal pilih satu buku untuk dipinjam.';
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        return;
                    }

                    if (this.selectedBookCount() > max) {
                        event.preventDefault();
                        this.formError = `Maksimal ${max} eksemplar untuk peminjaman biasa.`;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                },

                openQuickMemberModal() {
                    this.showModal = true;
                    this.modalErrorMessage = '';
                },

                closeQuickMemberModal() {
                    if (this.quickSaving) {
                        return;
                    }

                    this.showModal = false;
                    this.modalErrorMessage = '';
                },

                resetQuickForm() {
                    this.newNisNip = '';
                    this.newName = '';
                    this.newGender = 'laki-laki';
                    this.newMemberType = 'siswa';
                    this.newClassId = '';
                    this.newPhone = '';
                    this.modalErrorMessage = '';
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

                    this.quickSaving = true;

                    try {
                        const response = await fetch(this.config.quickMemberUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.config.csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                nis_nip: this.newNisNip,
                                name: this.newName,
                                gender: this.newGender,
                                member_type: this.newMemberType,
                                student_class_id: this.newMemberType === 'siswa' ? this.newClassId : null,
                                phone: this.newPhone || null,
                                status: 'aktif',
                            }),
                        });

                        const result = await response.json();

                        if (!response.ok || !result.success || !result.member) {
                            throw new Error(result.message || 'Gagal menyimpan data anggota.');
                        }

                        const newAddedMember = {
                            id: String(result.member.id),
                            name: result.member.name,
                            nis_nip: result.member.nis_nip,
                            member_code: result.member.member_code,
                            member_type: result.member.member_type,
                            class: result.class_name || result.member.student_class || 'Guru/Staff',
                            search: (
                                (result.member.name || '') + ' ' +
                                (result.member.nis_nip || '') + ' ' +
                                (result.member.member_code || '') + ' ' +
                                (result.member.member_type || '') + ' ' +
                                (result.class_name || result.member.student_class || 'Guru/Staff')
                            ).toLowerCase(),
                        };

                        this.membersList.push(newAddedMember);
                        this.selectMember(newAddedMember);

                        setTimeout(() => {
                            this.resetQuickForm();
                            this.showModal = false;
                        }, 400);
                    } catch (error) {
                        this.modalErrorMessage = error.message || 'Terjadi kesalahan sistem.';
                    } finally {
                        this.quickSaving = false;
                    }
                },

                formatText(value) {
                    if (!value) return '-';

                    return String(value)
                        .replace(/_/g, ' ')
                        .replace(/\b\w/g, char => char.toUpperCase());
                },
            };
        }
    </script>
</x-app-layout>