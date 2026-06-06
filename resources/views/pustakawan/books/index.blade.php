<x-app-layout>
    @php
        $canManage = auth()->check() && (int) auth()->user()->role_id === 1;
    @endphp

    <style>
        [x-cloak] {
            display: none !important;
        }

        .soft-grid-bg {
            background-image:
                radial-gradient(circle at 20% 20%, rgba(16, 185, 129, 0.12), transparent 26%),
                radial-gradient(circle at 80% 15%, rgba(20, 184, 166, 0.14), transparent 25%),
                radial-gradient(circle at 70% 80%, rgba(14, 165, 233, 0.10), transparent 24%);
        }
    </style>

    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
                    Manajemen Koleksi
                </p>

                <h2 class="mt-1 text-xl font-bold text-gray-900">
                    Katalog Buku Induk
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Kelola data buku induk dan eksemplar fisiknya.
                </p>
            </div>

            @if($canManage)
                <a href="{{ route('books.create') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800">
                    <span class="material-symbols-outlined text-[18px]">add_circle</span>
                    Tambah Buku Baru
                </a>
            @endif
        </div>
    </x-slot>

    @php
        $bookCollection = method_exists($books, 'getCollection') ? $books->getCollection() : collect($books ?? []);
        $bookCount = method_exists($books, 'total') ? $books->total() : $bookCollection->count();
    @endphp

    <div
        x-data="bookDeleteModal()"
        @keydown.escape.window="closeDeleteModal()"
        class="min-h-screen bg-gradient-to-br from-slate-50 via-emerald-50/40 to-sky-50/40 py-10"
    >
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

            <div class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/90 shadow-[0_18px_50px_rgba(15,23,42,0.06)] backdrop-blur-xl">

                <div class="relative overflow-hidden bg-gradient-to-r from-emerald-700 to-teal-500 px-6 py-6">
                    <div class="absolute -right-16 -top-20 h-52 w-52 rounded-full bg-white/10 blur-2xl"></div>
                    <div class="absolute -left-20 bottom-0 h-48 w-48 rounded-full bg-emerald-200/20 blur-2xl"></div>

                    <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-white">
                                Daftar Buku Induk
                            </h3>

                            <p class="mt-1 text-sm text-emerald-50">
                                Ringkasan buku, kategori, klasifikasi, stok, dan aksi pengelolaan.
                            </p>
                        </div>

                        <div class="flex w-fit items-center gap-3 rounded-2xl border border-white/20 bg-white/15 px-4 py-3 text-white">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20">
                                <span class="material-symbols-outlined">library_books</span>
                            </div>

                            <div>
                                <p class="text-xs text-emerald-50">
                                    Total Buku
                                </p>

                                <p class="text-lg font-bold">
                                    {{ number_format($bookCount, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <div class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm">
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[1180px] table-fixed divide-y divide-gray-100 text-sm">
                                <thead class="bg-slate-50">
                                    <tr class="text-xs uppercase tracking-[0.14em] text-gray-500">
                                        <th class="w-[35%] px-5 py-4 text-left font-extrabold">
                                            Buku
                                        </th>

                                        <th class="w-[18%] px-5 py-4 text-left font-extrabold">
                                            Kategori
                                        </th>

                                        <th class="w-[10%] px-5 py-4 text-center font-extrabold">
                                            DDC
                                        </th>

                                        <th class="w-[12%] px-5 py-4 text-center font-extrabold">
                                            Eksemplar
                                        </th>

                                        <th class="w-[12%] px-5 py-4 text-center font-extrabold">
                                            Status
                                        </th>

                                        <th class="w-[13%] px-5 py-4 text-center font-extrabold">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @forelse($books as $book)
                                        @php
                                            $bookItemsQuery = method_exists($book, 'bookItems') ? $book->bookItems() : null;

                                            $totalCopyCount = $bookItemsQuery
                                                ? (clone $bookItemsQuery)->count()
                                                : ($book->book_items_count ?? 0);

                                            $activeStockCount = $bookItemsQuery
                                                ? (clone $bookItemsQuery)
                                                    ->where(function ($query) {
                                                        $query->whereNull('status')
                                                            ->orWhere('status', '!=', 'nonaktif');
                                                    })
                                                    ->count()
                                                : ($book->book_items_count ?? $book->stock ?? 0);

                                            $isBorrowable = (bool) ($book->is_borrowable ?? true);

                                            $bookInitial = strtoupper(substr(trim($book->title ?? 'B'), 0, 1));

                                            $deletePayload = [
                                                'action' => route('books.destroy', $book),
                                                'title' => $book->title ?? 'Buku',
                                                'copy_count' => $totalCopyCount,
                                                'can_delete' => $totalCopyCount < 1,
                                                'book_items_url' => route('book_items.index', ['keyword' => $book->title ?? '']),
                                            ];

                                            $deletePayloadEncoded = base64_encode(json_encode($deletePayload));
                                        @endphp

                                        <tr class="align-middle transition hover:bg-emerald-50/40">
                                            <td class="px-5 py-5">
                                                <div class="flex items-start gap-4">
                                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-600 to-teal-500 text-lg font-black text-white shadow-sm">
                                                        {{ $bookInitial }}
                                                    </div>

                                                    <div class="min-w-0">
                                                        <p class="break-words text-base font-extrabold leading-6 text-gray-950">
                                                            {{ $book->title ?? '-' }}
                                                        </p>

                                                        <p class="mt-1 break-words text-sm text-gray-500">
                                                            {{ $book->author ?? '-' }}
                                                        </p>

                                                        <div class="mt-2 flex flex-wrap gap-2">
                                                            <span class="inline-flex max-w-[180px] items-center rounded-full border border-gray-200 bg-slate-50 px-3 py-1 text-[11px] font-bold text-gray-600">
                                                                {{ $book->publisher ?? '-' }}
                                                            </span>

                                                            <span class="inline-flex items-center rounded-full border border-gray-200 bg-slate-50 px-3 py-1 text-[11px] font-bold text-gray-600">
                                                                {{ $book->publication_year ?? '-' }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <td class="px-5 py-5">
                                                <div class="flex items-center gap-3">
                                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700">
                                                        <span class="material-symbols-outlined text-[18px]">category</span>
                                                    </div>

                                                    <div class="min-w-0">
                                                        <p class="break-words text-sm font-extrabold leading-5 text-gray-800">
                                                            {{ $book->category->name ?? 'Belum ada kategori' }}
                                                        </p>

                                                        <p class="mt-0.5 text-xs text-gray-400">
                                                            Kategori buku
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>

                                            <td class="px-5 py-5 text-center">
                                                @if($book->ddcClass?->code)
                                                    <div class="inline-flex min-w-[72px] flex-col items-center justify-center rounded-2xl border border-sky-100 bg-sky-50 px-3 py-2">
                                                        <span class="text-sm font-black text-sky-800">
                                                            {{ $book->ddcClass->code }}
                                                        </span>

                                                        <span class="mt-0.5 text-[10px] font-extrabold uppercase tracking-wider text-sky-600">
                                                            DDC
                                                        </span>
                                                    </div>
                                                @else
                                                    <div class="inline-flex min-w-[72px] flex-col items-center justify-center rounded-2xl border border-gray-200 bg-gray-50 px-3 py-2">
                                                        <span class="text-sm font-black text-gray-400">
                                                            -
                                                        </span>

                                                        <span class="mt-0.5 text-[10px] font-extrabold uppercase tracking-wider text-gray-400">
                                                            DDC
                                                        </span>
                                                    </div>
                                                @endif
                                            </td>

                                            <td class="px-5 py-5 text-center">
                                                <div class="inline-flex min-w-[78px] flex-col items-center justify-center rounded-2xl border {{ $activeStockCount > 0 ? 'border-emerald-100 bg-emerald-50' : 'border-gray-200 bg-gray-50' }} px-4 py-3">
                                                    <span class="text-2xl font-black leading-none {{ $activeStockCount > 0 ? 'text-emerald-800' : 'text-gray-400' }}">
                                                        {{ number_format($activeStockCount, 0, ',', '.') }}
                                                    </span>

                                                    <span class="mt-1 text-[10px] font-extrabold uppercase tracking-wider {{ $activeStockCount > 0 ? 'text-emerald-700' : 'text-gray-400' }}">
                                                        Copy
                                                    </span>
                                                </div>

                                                @if($totalCopyCount > $activeStockCount)
                                                    <p class="mt-2 text-[11px] font-semibold text-gray-400">
                                                        Total: {{ number_format($totalCopyCount, 0, ',', '.') }}
                                                    </p>
                                                @endif
                                            </td>

                                            <td class="px-5 py-5 text-center">
                                                @if($isBorrowable)
                                                    <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-extrabold text-emerald-700">
                                                        <span class="material-symbols-outlined text-[15px]">check_circle</span>
                                                        Bisa Dipinjam
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-extrabold text-amber-700">
                                                        <span class="material-symbols-outlined text-[15px]">visibility</span>
                                                        Baca di Tempat
                                                    </span>
                                                @endif
                                            </td>

                                            <td class="px-5 py-5">
                                                <div class="mx-auto grid w-[150px] grid-cols-2 gap-2">
                                                    <a href="{{ route('books.show', $book) }}"
                                                       class="inline-flex h-9 items-center justify-center gap-1 rounded-xl border border-emerald-200 bg-white px-2 text-xs font-extrabold text-emerald-700 transition hover:bg-emerald-50">
                                                        <span class="material-symbols-outlined text-[15px]">visibility</span>
                                                        Lihat
                                                    </a>

                                                    @if($canManage)
                                                        <a href="{{ route('books.edit', $book) }}"
                                                           class="inline-flex h-9 items-center justify-center gap-1 rounded-xl border border-teal-200 bg-white px-2 text-xs font-extrabold text-teal-700 transition hover:bg-teal-50">
                                                            <span class="material-symbols-outlined text-[15px]">edit</span>
                                                            Edit
                                                        </a>

                                                        <button
                                                            type="button"
                                                            data-delete-payload="{{ $deletePayloadEncoded }}"
                                                            @click="openDeleteModalFromButton($event.currentTarget)"
                                                            class="col-span-2 inline-flex h-9 w-full items-center justify-center gap-1 rounded-xl border border-red-200 bg-red-50 px-2 text-xs font-extrabold text-red-700 transition hover:bg-red-100"
                                                        >
                                                            <span class="material-symbols-outlined text-[15px]">delete</span>
                                                            Hapus
                                                        </button>
                                                    @else
                                                        <span class="inline-flex h-9 items-center justify-center rounded-xl border border-gray-200 bg-gray-50 px-2 text-xs font-bold text-gray-400">
                                                            -
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-14 text-center">
                                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                                                    <span class="material-symbols-outlined">menu_book</span>
                                                </div>

                                                <p class="mt-4 text-sm font-semibold text-gray-700">
                                                    Belum ada buku dalam katalog.
                                                </p>

                                                <p class="mt-1 text-xs text-gray-500">
                                                    @if($canManage)
                                                        Klik tombol Tambah Buku Baru untuk mulai menambahkan koleksi.
                                                    @else
                                                        Belum ada data buku yang dapat ditampilkan.
                                                    @endif
                                                </p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if(method_exists($books, 'links'))
                        <div class="mt-6">
                            {{ $books->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div
            x-show="deleteModalOpen"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 px-4 py-8 backdrop-blur-md"
        >
            <div
                @click.outside="closeDeleteModal()"
                x-show="deleteModalOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                class="relative w-full max-w-lg overflow-hidden rounded-[2rem] bg-white shadow-[0_30px_90px_rgba(15,23,42,0.35)]"
            >
                <div class="soft-grid-bg relative overflow-hidden px-7 pb-6 pt-7">
                    <div class="absolute -right-16 -top-16 h-40 w-40 rounded-full bg-white/40 blur-2xl"></div>
                    <div class="absolute -left-10 bottom-0 h-32 w-32 rounded-full bg-emerald-200/50 blur-2xl"></div>

                    <button
                        type="button"
                        @click="closeDeleteModal()"
                        class="absolute right-5 top-5 flex h-10 w-10 items-center justify-center rounded-2xl bg-white/80 text-gray-500 shadow-sm transition hover:bg-white hover:text-gray-800"
                    >
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>

                    <div class="relative">
                        <div
                            class="flex h-16 w-16 items-center justify-center rounded-3xl shadow-lg"
                            :class="deleteBook.can_delete ? 'bg-red-600 text-white shadow-red-600/25' : 'bg-amber-500 text-white shadow-amber-500/25'"
                        >
                            <span
                                class="material-symbols-outlined text-[34px]"
                                x-text="deleteBook.can_delete ? 'delete_forever' : 'inventory_2'"
                            ></span>
                        </div>

                        <div class="mt-5">
                            <p
                                class="text-xs font-extrabold uppercase tracking-[0.22em]"
                                :class="deleteBook.can_delete ? 'text-red-600' : 'text-amber-600'"
                                x-text="deleteBook.can_delete ? 'Konfirmasi Hapus' : 'Eksemplar Masih Ada'"
                            ></p>

                            <h3
                                class="mt-2 text-2xl font-black leading-tight text-gray-950"
                                x-text="deleteBook.can_delete ? 'Hapus buku induk ini?' : 'Buku belum bisa dihapus'"
                            ></h3>

                            <p class="mt-3 max-w-md text-sm leading-6 text-gray-600">
                                <template x-if="deleteBook.can_delete">
                                    <span>
                                        Buku ini belum memiliki eksemplar. Data buku induk bisa dihapus dari katalog.
                                    </span>
                                </template>

                                <template x-if="!deleteBook.can_delete">
                                    <span>
                                        Hapus eksemplarnya terlebih dahulu, lalu kembali hapus buku induknya.
                                    </span>
                                </template>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="px-7 py-6">
                    <div class="rounded-3xl border border-gray-100 bg-slate-50 p-5">
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-400">
                            Buku
                        </p>

                        <p class="mt-2 text-base font-extrabold leading-6 text-gray-900" x-text="deleteBook.title"></p>

                        <template x-if="!deleteBook.can_delete">
                            <div class="mt-4 inline-flex items-center gap-2 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-bold text-amber-700">
                                <span class="material-symbols-outlined text-[18px]">stacks</span>
                                <span x-text="deleteBook.copy_count + ' eksemplar tercatat'"></span>
                            </div>
                        </template>

                        <template x-if="deleteBook.can_delete">
                            <div class="mt-4 inline-flex items-center gap-2 rounded-2xl border border-red-200 bg-red-50 px-4 py-2 text-sm font-bold text-red-700">
                                <span class="material-symbols-outlined text-[18px]">priority_high</span>
                                Aksi ini akan menghapus data buku.
                            </div>
                        </template>
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-gray-100 bg-white px-7 py-5 sm:flex-row sm:justify-end">
                    <button
                        type="button"
                        @click="closeDeleteModal()"
                        class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-600 transition hover:bg-gray-50"
                    >
                        Batal
                    </button>

                    <template x-if="!deleteBook.can_delete">
                        <a
                            :href="deleteBook.book_items_url"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-amber-500 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-amber-500/25 transition hover:bg-amber-600"
                        >
                            <span class="material-symbols-outlined text-[18px]">inventory_2</span>
                            Kelola Eksemplar
                        </a>
                    </template>

                    <template x-if="deleteBook.can_delete">
                        <form method="POST" :action="deleteBook.action">
                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-red-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-red-600/25 transition hover:bg-red-700"
                            >
                                <span class="material-symbols-outlined text-[18px]">delete</span>
                                Ya, Hapus
                            </button>
                        </form>
                    </template>
                </div>
            </div>
        </div>

        <script>
            function bookDeleteModal() {
                return {
                    deleteModalOpen: false,
                    deleteBook: {
                        action: '',
                        title: '',
                        copy_count: 0,
                        can_delete: false,
                        book_items_url: '',
                    },

                    openDeleteModalFromButton(button) {
                        try {
                            const encodedPayload = button.dataset.deletePayload || '';
                            const payload = JSON.parse(atob(encodedPayload));
                            this.openDeleteModal(payload);
                        } catch (error) {
                            console.error(error);

                            this.deleteBook = {
                                action: '',
                                title: 'Data buku gagal dibaca',
                                copy_count: 0,
                                can_delete: false,
                                book_items_url: '{{ route('book_items.index') }}',
                            };

                            this.deleteModalOpen = true;
                        }
                    },

                    openDeleteModal(payload) {
                        this.deleteBook = payload;
                        this.deleteModalOpen = true;
                    },

                    closeDeleteModal() {
                        this.deleteModalOpen = false;

                        this.deleteBook = {
                            action: '',
                            title: '',
                            copy_count: 0,
                            can_delete: false,
                            book_items_url: '',
                        };
                    },
                };
            }
        </script>
    </div>
</x-app-layout>