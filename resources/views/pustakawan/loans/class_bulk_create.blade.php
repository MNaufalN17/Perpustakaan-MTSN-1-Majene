<x-app-layout>
    @php
        $maxClassLoanItems = (int) ($maxClassLoanItems ?? \App\Models\SystemSetting::intValue('max_class_loan_items', 40));
        $loanDurationDays = (int) ($loanDurationDays ?? \App\Models\SystemSetting::intValue('loan_duration_days', 7));

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
                    ($className ?? '')
                ),
            ];
        })->values();

        $bookGroups = collect($bookItems ?? [])
            ->groupBy('book_id')
            ->map(function ($items) {
                $first = $items->first();

                return [
                    'id' => (string) $first->book_id,
                    'title' => $first->book->title ?? '-',
                    'author' => $first->book->author ?? '-',
                    'publisher' => $first->book->publisher ?? '-',
                    'available_count' => $items->count(),
                    'search' => strtolower(
                        ($first->book->title ?? '') . ' ' .
                        ($first->book->author ?? '') . ' ' .
                        ($first->book->publisher ?? '')
                    ),
                    'items' => $items->map(function ($item) {
                        return [
                            'id' => (string) $item->id,
                            'item_code' => $item->item_code ?? '-',
                            'copy_number' => $item->copy_number ?? '-',
                            'condition' => $item->condition ?? '-',
                            'location' => $item->location ?? '-',
                        ];
                    })->values(),
                ];
            })
            ->values();

        $classOptions = collect($studentClasses ?? [])->map(function ($class) {
            return [
                'id' => (string) $class->id,
                'name' => $class->class_name ?? $class->name ?? '-',
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
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-600">
                    Peminjaman Rombongan
                </p>

                <h2 class="mt-1 text-xl font-bold text-gray-900">
                    Peminjaman Kelas
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Untuk satu perwakilan siswa yang meminjam banyak eksemplar buku pelajaran.
                </p>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row">
                <a href="{{ route('loans.create') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-white px-5 py-3 text-sm font-bold text-emerald-700 transition hover:bg-emerald-50">
                    <span class="material-symbols-outlined text-[18px]">assignment_add</span>
                    Peminjaman Biasa
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
        class="min-h-screen bg-gradient-to-br from-slate-50 via-amber-50/40 to-emerald-50/40 py-10"
        x-data="classBulkLoanForm(
            @js($memberOptions),
            @js($bookGroups),
            @js($classOptions),
            @js([
                'maxClassLoanItems' => $maxClassLoanItems,
                'oldMemberId' => old('member_id'),
                'oldClassId' => old('student_class_id'),
                'oldBookId' => old('book_id'),
                'oldBookItemIds' => old('book_item_ids', []),
            ])
        )"
    >
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

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
                action="{{ route('loans.class_bulk.store') }}"
                class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/90 shadow-[0_20px_60px_rgba(15,23,42,0.08)] backdrop-blur-xl"
                @submit="validateForm"
            >
                @csrf

                <template x-for="id in selectedIds" :key="'selected-' + id">
                    <input type="hidden" name="book_item_ids[]" :value="id">
                </template>

                <input type="hidden" name="member_id" :value="selectedMember ? selectedMember.id : ''">
                <input type="hidden" name="book_id" :value="selectedBook ? selectedBook.id : ''">

                <div class="relative overflow-hidden bg-gradient-to-r from-amber-500 to-emerald-600 p-6 text-white">
                    <div class="absolute -right-16 -top-20 h-52 w-52 rounded-full bg-white/10 blur-2xl"></div>
                    <div class="absolute -left-20 bottom-0 h-48 w-48 rounded-full bg-amber-200/20 blur-2xl"></div>

                    <div class="relative flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/20">
                                <span class="material-symbols-outlined text-[28px]">groups</span>
                            </div>

                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-50">
                                    Mode Kelas
                                </p>

                                <h3 class="mt-2 text-2xl font-extrabold">
                                    Pinjam Banyak Copy Sekaligus
                                </h3>

                                <p class="mt-1 text-sm text-amber-50">
                                    Maksimal {{ $maxClassLoanItems }} eksemplar. Batas ini diatur oleh Admin IT.
                                </p>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-white/20 bg-white/15 px-4 py-3">
                            <p class="text-xs text-amber-50">
                                Copy Dipilih
                            </p>

                            <p class="text-2xl font-black" x-text="selectedIds.length"></p>
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

                    <section class="grid gap-5 lg:grid-cols-2">
                        <div class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm">
                            <h4 class="font-extrabold text-gray-900">
                                Perwakilan Peminjam
                            </h4>

                            <p class="mt-1 text-xs text-gray-500">
                                Pilih siswa atau anggota yang menjadi perwakilan peminjaman.
                            </p>

                            <div class="relative mt-4">
                                <span class="material-symbols-outlined pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[20px] text-gray-400">
                                    search
                                </span>

                                <input
                                    type="text"
                                    x-model="memberSearch"
                                    @focus="memberDropdownOpen = true"
                                    @input="memberDropdownOpen = true"
                                    placeholder="Cari nama, NIS/NIP, atau kelas..."
                                    class="w-full rounded-2xl border border-gray-200 bg-slate-50 px-12 py-3 text-sm focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-100"
                                    autocomplete="off"
                                >

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

                                            <span class="rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                                                Pilih
                                            </span>
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
                        </div>

                        <div class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm">
                            <h4 class="font-extrabold text-gray-900">
                                Informasi Kelas
                            </h4>

                            <p class="mt-1 text-xs text-gray-500">
                                Opsional, dipakai untuk catatan transaksi.
                            </p>

                            <div class="mt-4">
                                <label class="block text-sm font-bold text-gray-700">
                                    Kelas
                                </label>

                                <select
                                    name="student_class_id"
                                    x-model="selectedClassId"
                                    class="mt-2 block w-full rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                >
                                    <option value="">Tidak memilih kelas</option>

                                    <template x-for="classItem in classesList" :key="classItem.id">
                                        <option :value="classItem.id" x-text="classItem.name"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm">
                        <h4 class="font-extrabold text-gray-900">
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
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                            <div>
                                <h4 class="font-extrabold text-gray-900">
                                    Pilih Jenis Buku
                                </h4>

                                <p class="mt-1 text-xs text-gray-500">
                                    Pilih satu judul buku, lalu pilih copy yang akan dipinjam.
                                </p>
                            </div>

                            <div class="w-full lg:w-80">
                                <label class="block text-sm font-bold text-gray-700">
                                    Cari Judul
                                </label>

                                <input
                                    type="text"
                                    x-model="bookSearch"
                                    placeholder="Cari judul atau penulis..."
                                    class="mt-2 block w-full rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                >
                            </div>
                        </div>

                        <div class="mt-5 grid gap-3 md:grid-cols-2 lg:grid-cols-3">
                            <template x-for="book in filteredBooks()" :key="book.id">
                                <button
                                    type="button"
                                    @click="selectBook(book)"
                                    class="rounded-3xl border p-4 text-left transition"
                                    :class="selectedBook && selectedBook.id === book.id
                                        ? 'border-emerald-300 bg-emerald-50 shadow-sm'
                                        : 'border-gray-100 bg-white hover:border-emerald-200 hover:bg-emerald-50/50'"
                                >
                                    <p class="font-extrabold leading-5 text-gray-900" x-text="book.title"></p>

                                    <p class="mt-1 text-xs text-gray-500" x-text="book.author || '-'"></p>

                                    <div class="mt-4 inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">
                                        <span x-text="book.available_count"></span>
                                        <span class="ml-1">copy tersedia</span>
                                    </div>
                                </button>
                            </template>

                            <div
                                x-show="filteredBooks().length === 0"
                                class="rounded-3xl border border-gray-100 bg-slate-50 px-4 py-10 text-center text-sm text-gray-500 md:col-span-2 lg:col-span-3"
                            >
                                Tidak ada buku tersedia.
                            </div>
                        </div>
                    </section>

                    <section
                        x-show="selectedBook"
                        x-cloak
                        class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm"
                    >
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                            <div>
                                <h4 class="font-extrabold text-gray-900">
                                    Pilih Copy Buku
                                </h4>

                                <p class="mt-1 text-sm text-gray-500">
                                    <span x-text="selectedBook?.title"></span>
                                </p>
                            </div>

                            <div class="flex flex-col gap-2 sm:flex-row">
                                <input
                                    type="number"
                                    x-model.number="targetQuantity"
                                    min="1"
                                    :max="maxClassLoanItems"
                                    class="w-full rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 sm:w-36"
                                    placeholder="Jumlah"
                                >

                                <button
                                    type="button"
                                    @click="autoSelect()"
                                    class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-800"
                                >
                                    <span class="material-symbols-outlined text-[18px]">done_all</span>
                                    Pilih Otomatis
                                </button>

                                <button
                                    type="button"
                                    @click="clearSelectedCopies()"
                                    class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-600 transition hover:bg-gray-50"
                                >
                                    Kosongkan
                                </button>
                            </div>
                        </div>

                        <div class="mt-5 grid gap-3 md:grid-cols-2 lg:grid-cols-3">
                            <template x-for="item in selectedBookItems()" :key="item.id">
                                <label
                                    class="flex cursor-pointer items-start gap-3 rounded-3xl border p-4 transition"
                                    :class="selectedIds.includes(String(item.id))
                                        ? 'border-emerald-300 bg-emerald-50'
                                        : 'border-gray-100 bg-white hover:border-emerald-200 hover:bg-emerald-50/40'"
                                >
                                    <input
                                        type="checkbox"
                                        :value="String(item.id)"
                                        x-model="selectedIds"
                                        class="mt-1 rounded border-gray-300 text-emerald-700 focus:ring-emerald-500"
                                    >

                                    <div>
                                        <p class="font-mono text-sm font-black text-gray-900" x-text="item.item_code"></p>

                                        <p class="mt-1 text-xs text-gray-500">
                                            Copy:
                                            <span class="font-bold" x-text="item.copy_number"></span>
                                            —
                                            Kondisi:
                                            <span class="font-bold" x-text="formatText(item.condition)"></span>
                                        </p>

                                        <p class="mt-1 text-xs text-gray-400">
                                            Lokasi:
                                            <span x-text="item.location || '-'"></span>
                                        </p>
                                    </div>
                                </label>
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
                            placeholder="Opsional. Contoh: Dipinjam untuk mata pelajaran Bahasa Indonesia jam pertama."
                        >{{ old('notes') }}</textarea>
                    </section>

                    <div class="flex flex-col-reverse gap-3 border-t border-gray-100 pt-6 sm:flex-row sm:justify-end">
                        <a href="{{ route('loans.index') }}"
                           class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-600 transition hover:bg-gray-50">
                            Batal
                        </a>

                        <button
                            type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-amber-500 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-amber-500/20 transition hover:bg-amber-600"
                        >
                            <span class="material-symbols-outlined text-[18px]">save</span>
                            Simpan Peminjaman Kelas
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>

    <script>
        function classBulkLoanForm(members, books, classes, config) {
            return {
                membersList: members || [],
                booksList: books || [],
                classesList: classes || [],
                config: config || {},

                maxClassLoanItems: parseInt(config?.maxClassLoanItems || 40),

                memberSearch: '',
                memberDropdownOpen: false,
                selectedMember: null,

                selectedClassId: config?.oldClassId || '',
                bookSearch: '',
                selectedBook: null,
                selectedIds: [],
                targetQuantity: 30,

                formError: '',

                init() {
                    if (this.config.oldMemberId) {
                        const member = this.membersList.find(item => String(item.id) === String(this.config.oldMemberId));

                        if (member) {
                            this.selectMember(member);
                        }
                    }

                    if (this.config.oldBookId) {
                        const book = this.booksList.find(item => String(item.id) === String(this.config.oldBookId));

                        if (book) {
                            this.selectBook(book);
                        }
                    }

                    if (Array.isArray(this.config.oldBookItemIds)) {
                        this.selectedIds = this.config.oldBookItemIds.map(id => String(id));
                    }
                },

                filteredMembers() {
                    const keyword = (this.memberSearch || '').toLowerCase().trim();

                    if (!keyword) {
                        return this.membersList.slice(0, 30);
                    }

                    return this.membersList
                        .filter(member => (member.search || '').includes(keyword))
                        .slice(0, 30);
                },

                selectMember(member) {
                    this.selectedMember = member;
                    this.memberSearch = `${member.name} - ${member.nis_nip || '-'}`;
                    this.memberDropdownOpen = false;
                    this.formError = '';
                },

                filteredBooks() {
                    const keyword = (this.bookSearch || '').toLowerCase().trim();

                    if (!keyword) {
                        return this.booksList.slice(0, 30);
                    }

                    return this.booksList
                        .filter(book => (book.search || '').includes(keyword))
                        .slice(0, 30);
                },

                selectBook(book) {
                    this.selectedBook = book;
                    this.selectedIds = [];
                    this.formError = '';
                },

                selectedBookItems() {
                    if (!this.selectedBook) {
                        return [];
                    }

                    return this.selectedBook.items || [];
                },

                autoSelect() {
                    if (!this.selectedBook) {
                        this.formError = 'Pilih jenis buku terlebih dahulu.';
                        return;
                    }

                    const qty = parseInt(this.targetQuantity || 0);

                    if (qty < 1) {
                        this.formError = 'Jumlah copy minimal 1.';
                        return;
                    }

                    if (qty > this.maxClassLoanItems) {
                        this.formError = `Maksimal ${this.maxClassLoanItems} copy untuk peminjaman kelas.`;
                        return;
                    }

                    const items = this.selectedBookItems();

                    if (qty > items.length) {
                        this.formError = `Stok tersedia hanya ${items.length} copy.`;
                        return;
                    }

                    this.selectedIds = items.slice(0, qty).map(item => String(item.id));
                    this.formError = '';
                },

                clearSelectedCopies() {
                    this.selectedIds = [];
                    this.formError = '';
                },

                validateForm(event) {
                    this.formError = '';

                    if (!this.selectedMember) {
                        event.preventDefault();
                        this.formError = 'Pilih perwakilan peminjam terlebih dahulu.';
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        return;
                    }

                    if (!this.selectedBook) {
                        event.preventDefault();
                        this.formError = 'Pilih jenis buku terlebih dahulu.';
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        return;
                    }

                    if (this.selectedIds.length < 1) {
                        event.preventDefault();
                        this.formError = 'Pilih minimal satu copy buku.';
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        return;
                    }

                    if (this.selectedIds.length > this.maxClassLoanItems) {
                        event.preventDefault();
                        this.formError = `Maksimal ${this.maxClassLoanItems} copy untuk peminjaman kelas.`;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                },

                formatText(value) {
                    if (!value) {
                        return '-';
                    }

                    return String(value)
                        .replace(/_/g, ' ')
                        .replace(/\b\w/g, char => char.toUpperCase());
                },
            };
        }
    </script>
</x-app-layout>