<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
                    Manajemen Koleksi
                </p>
                <h2 class="mt-1 text-xl font-bold text-gray-900">
                    Stok Fisik Buku
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Kelola daftar eksemplar fisik buku, status ketersediaan, dan kondisi buku.
                </p>
            </div>

            <a href="{{ route('book_items.create') }}"
               class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800">
                <span class="material-symbols-outlined text-[18px]">add_circle</span>
                Tambah Eksemplar
            </a>
        </div>
    </x-slot>

    @php
        $itemCount = method_exists($bookItems, 'total') ? $bookItems->total() : $bookItems->count();

        $availableCount = $bookItems->where('status', 'tersedia')->count();
        $borrowedCount = $bookItems->where('status', 'dipinjam')->count();
        $damagedCount = $bookItems->whereIn('condition', ['rusak ringan', 'rusak berat'])->count();

        $bookOptions = $bookItems
            ->map(fn ($item) => $item->book->title ?? null)
            ->filter()
            ->unique()
            ->sort()
            ->values();
    @endphp

    <div
        class="min-h-screen bg-gradient-to-br from-slate-50 via-emerald-50/30 to-sky-50/30 py-8"
        x-data="{
            search: '',
            filterStatus: '',
            filterCondition: '',
            filterBook: '',

            matches(text, status, condition, bookTitle) {
                const keyword = this.search.toLowerCase().trim();

                const matchSearch = keyword === '' || text.toLowerCase().includes(keyword);
                const matchStatus = this.filterStatus === '' || status === this.filterStatus;
                const matchCondition = this.filterCondition === '' || condition === this.filterCondition;
                const matchBook = this.filterBook === '' || bookTitle === this.filterBook;

                return matchSearch && matchStatus && matchCondition && matchBook;
            },

            resetFilter() {
                this.search = '';
                this.filterStatus = '';
                this.filterCondition = '';
                this.filterBook = '';
            }
        }"
    >
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

            @if (session('success') || session('success_title') || session('success_message'))
                <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800 shadow-sm">
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                            <span class="material-symbols-outlined text-[20px]">check_circle</span>
                        </div>

                        <div>
                            <p class="text-sm font-bold">
                                {{ session('success_title', 'Berhasil') }}
                            </p>

                            <p class="mt-1 text-sm leading-6">
                                {{ session('success_message', session('success')) }}
                            </p>

                            @if(session('success_detail'))
                                <p class="mt-1 text-xs leading-5 text-emerald-700">
                                    {{ session('success_detail') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            @if (session('error') || session('error_title') || session('error_message'))
                <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-red-800 shadow-sm">
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-red-100 text-red-700">
                            <span class="material-symbols-outlined text-[20px]">error</span>
                        </div>

                        <div>
                            <p class="text-sm font-bold">
                                {{ session('error_title', 'Gagal') }}
                            </p>

                            <p class="mt-1 text-sm leading-6">
                                {{ session('error_message', session('error')) }}
                            </p>

                            @if(session('error_detail'))
                                <p class="mt-1 text-xs leading-5 text-red-700">
                                    {{ session('error_detail') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <div class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/80 shadow-[0_18px_50px_rgba(15,23,42,0.06)] backdrop-blur-xl">
                <div class="border-b border-gray-100 bg-white px-6 py-6">
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                                <span class="material-symbols-outlined">inventory_2</span>
                            </div>

                            <div>
                                <h3 class="text-lg font-bold text-gray-900">
                                    Database Eksemplar
                                </h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Total {{ number_format($itemCount, 0, ',', '.') }} eksemplar fisik tersimpan.
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-2 sm:flex sm:flex-wrap sm:justify-end">
                            <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3">
                                <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-emerald-700">Tersedia</p>
                                <p class="mt-1 text-lg font-extrabold text-emerald-800">{{ $availableCount }}</p>
                            </div>

                            <div class="rounded-2xl border border-sky-100 bg-sky-50 px-4 py-3">
                                <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-sky-700">Dipinjam</p>
                                <p class="mt-1 text-lg font-extrabold text-sky-800">{{ $borrowedCount }}</p>
                            </div>

                            <div class="rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3">
                                <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-amber-700">Rusak</p>
                                <p class="mt-1 text-lg font-extrabold text-amber-800">{{ $damagedCount }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-3 lg:grid-cols-[1.4fr_1fr_1fr_1fr_auto]">
                        <div class="relative">
                            <span class="material-symbols-outlined pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[20px] text-gray-400">
                                search
                            </span>

                            <input
                                type="text"
                                x-model="search"
                                placeholder="Cari kode eksemplar, judul, penulis, atau DDC..."
                                class="w-full rounded-2xl border border-gray-200 bg-slate-50 px-12 py-3 text-sm text-gray-800 outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100"
                            >
                        </div>

                        <div class="relative">
                            <select
                                x-model="filterBook"
                                class="w-full appearance-none rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 pr-10 text-sm text-gray-700 outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100"
                            >
                                <option value="">Semua Buku</option>
                                @foreach($bookOptions as $bookTitle)
                                    <option value="{{ $bookTitle }}">{{ $bookTitle }}</option>
                                @endforeach
                            </select>

                            <span class="material-symbols-outlined pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-[20px] text-gray-400">
                                expand_more
                            </span>
                        </div>

                        <div class="relative">
                            <select
                                x-model="filterStatus"
                                class="w-full appearance-none rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 pr-10 text-sm text-gray-700 outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100"
                            >
                                <option value="">Semua Status</option>
                                <option value="tersedia">Tersedia</option>
                                <option value="dipinjam">Dipinjam</option>
                                <option value="rusak">Rusak</option>
                                <option value="hilang">Hilang</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>

                            <span class="material-symbols-outlined pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-[20px] text-gray-400">
                                expand_more
                            </span>
                        </div>

                        <div class="relative">
                            <select
                                x-model="filterCondition"
                                class="w-full appearance-none rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 pr-10 text-sm text-gray-700 outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100"
                            >
                                <option value="">Semua Kondisi</option>
                                <option value="baik">Baik</option>
                                <option value="rusak ringan">Rusak Ringan</option>
                                <option value="rusak berat">Rusak Berat</option>
                                <option value="hilang">Hilang</option>
                            </select>

                            <span class="material-symbols-outlined pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-[20px] text-gray-400">
                                expand_more
                            </span>
                        </div>

                        <button
                            type="button"
                            @click="resetFilter()"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-bold text-gray-600 transition hover:bg-gray-50"
                        >
                            <span class="material-symbols-outlined text-[18px]">restart_alt</span>
                            Reset
                        </button>
                    </div>
                </div>

                <div class="p-6">
                    <div class="overflow-x-auto rounded-3xl border border-gray-100 bg-white">
                        <table class="min-w-[1240px] w-full divide-y divide-gray-100 text-left text-sm">
                            <thead class="bg-slate-50 text-xs uppercase tracking-wider text-gray-500">
                                <tr>
                                    <th class="w-[190px] px-5 py-4 font-bold">Kode Eksemplar</th>
                                    <th class="w-[280px] px-5 py-4 font-bold">Buku</th>
                                    <th class="w-[130px] px-5 py-4 text-center font-bold">DDC</th>
                                    <th class="w-[130px] px-5 py-4 text-center font-bold">Copy</th>
                                    <th class="w-[150px] px-5 py-4 text-center font-bold">Status</th>
                                    <th class="w-[170px] px-5 py-4 text-center font-bold">Kondisi</th>
                                    <th class="w-[240px] px-5 py-4 text-center font-bold">Aksi</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse($bookItems as $bookItem)
                                    @php
                                        $bookTitle = $bookItem->book->title ?? '-';
                                        $author = $bookItem->book->author ?? '-';
                                        $ddcCode = $bookItem->classification_code ?? $bookItem->book->ddcClass->code ?? '-';

                                        $searchText = strtolower(
                                            ($bookItem->item_code ?? '') . ' ' .
                                            ($bookItem->classification_code ?? '') . ' ' .
                                            ($bookItem->author_code ?? '') . ' ' .
                                            ($bookItem->title_code ?? '') . ' ' .
                                            ($bookTitle ?? '') . ' ' .
                                            ($author ?? '') . ' ' .
                                            ($ddcCode ?? '') . ' ' .
                                            ($bookItem->status ?? '') . ' ' .
                                            ($bookItem->condition ?? '')
                                        );
                                    @endphp

                                    <tr
                                        class="transition hover:bg-emerald-50/40"
                                        x-show="matches(
                                            @js($searchText),
                                            @js($bookItem->status),
                                            @js($bookItem->condition),
                                            @js($bookTitle)
                                        )"
                                    >
                                        <td class="px-5 py-4 align-middle">
                                            <div>
                                                <span class="font-mono text-xs font-bold text-gray-900">
                                                    {{ $bookItem->item_code }}
                                                </span>
                                                <p class="mt-1 text-xs text-gray-500">
                                                    {{ $bookItem->classification_code ?? '-' }} -
                                                    {{ $bookItem->author_code ?? '-' }} -
                                                    {{ $bookItem->title_code ?? $bookItem->title_initial ?? '-' }}
                                                </p>
                                            </div>
                                        </td>

                                        <td class="px-5 py-4 align-middle">
                                            <p class="font-bold text-gray-900">
                                                {{ $bookTitle }}
                                            </p>
                                            <p class="mt-1 text-xs text-gray-500">
                                                Penulis: {{ $author }}
                                            </p>
                                        </td>

                                        <td class="px-5 py-4 text-center align-middle">
                                            <span class="inline-flex items-center justify-center rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1.5 font-mono text-xs font-bold text-emerald-700">
                                                {{ $ddcCode }}
                                            </span>
                                        </td>

                                        <td class="px-5 py-4 text-center align-middle">
                                            <span class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-700">
                                                {{ $bookItem->copy_number ?? '-' }}
                                            </span>
                                        </td>

                                        <td class="px-5 py-4 text-center align-middle">
                                            @if($bookItem->status === 'tersedia')
                                                <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                    Tersedia
                                                </span>
                                            @elseif($bookItem->status === 'dipinjam')
                                                <span class="inline-flex items-center gap-1.5 rounded-full border border-sky-100 bg-sky-50 px-3 py-1.5 text-xs font-bold text-sky-700">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-sky-500"></span>
                                                    Dipinjam
                                                </span>
                                            @elseif($bookItem->status === 'rusak')
                                                <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-100 bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                                    Rusak
                                                </span>
                                            @elseif($bookItem->status === 'hilang')
                                                <span class="inline-flex items-center gap-1.5 rounded-full border border-red-100 bg-red-50 px-3 py-1.5 text-xs font-bold text-red-700">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                                    Hilang
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs font-bold text-gray-600">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>
                                                    Nonaktif
                                                </span>
                                            @endif
                                        </td>

                                        <td class="px-5 py-4 text-center align-middle">
                                            @if($bookItem->condition === 'baik')
                                                <span class="inline-flex items-center rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">
                                                    Baik
                                                </span>
                                            @elseif($bookItem->condition === 'rusak ringan')
                                                <span class="inline-flex items-center rounded-full border border-amber-100 bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700">
                                                    Rusak Ringan
                                                </span>
                                            @elseif($bookItem->condition === 'rusak berat')
                                                <span class="inline-flex items-center rounded-full border border-red-100 bg-red-50 px-3 py-1.5 text-xs font-bold text-red-700">
                                                    Rusak Berat
                                                </span>
                                            @else
                                                <span class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs font-bold text-gray-600">
                                                    {{ ucfirst($bookItem->condition ?? '-') }}
                                                </span>
                                            @endif
                                        </td>

                                        <td class="px-5 py-4 align-middle">
                                            <div class="mx-auto w-[210px] rounded-2xl border border-gray-100 bg-slate-50 p-2 shadow-sm">
                                                <div class="grid grid-cols-2 gap-2">
                                                    <a href="{{ route('book_items.show', $bookItem) }}"
                                                       class="inline-flex h-9 items-center justify-center gap-1.5 rounded-xl border border-emerald-200 bg-white px-3 text-xs font-bold text-emerald-700 transition hover:bg-emerald-50">
                                                        <span class="material-symbols-outlined text-[15px]">visibility</span>
                                                        Lihat
                                                    </a>

                                                    <a href="{{ route('book_items.edit', $bookItem) }}"
                                                       class="inline-flex h-9 items-center justify-center gap-1.5 rounded-xl border border-teal-200 bg-white px-3 text-xs font-bold text-teal-700 transition hover:bg-teal-50">
                                                        <span class="material-symbols-outlined text-[15px]">edit</span>
                                                        Edit
                                                    </a>

                                                    <form
                                                        action="{{ route('book_items.destroy', $bookItem) }}"
                                                        method="POST"
                                                        class="col-span-2"
                                                        onsubmit="return confirm('Hapus eksemplar ini? Data eksemplar yang dihapus tidak akan tampil lagi pada stok fisik buku.')"
                                                    >
                                                        @csrf
                                                        @method('DELETE')

                                                        <button
                                                            type="submit"
                                                            class="inline-flex h-9 w-full items-center justify-center gap-1.5 rounded-xl border border-red-200 bg-red-50 px-3 text-xs font-bold text-red-700 transition hover:bg-red-100"
                                                        >
                                                            <span class="material-symbols-outlined text-[15px]">delete</span>
                                                            Hapus Eksemplar
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-14 text-center">
                                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                                                <span class="material-symbols-outlined">inventory_2</span>
                                            </div>
                                            <p class="mt-4 text-sm font-semibold text-gray-700">
                                                Belum ada data eksemplar.
                                            </p>
                                            <p class="mt-1 text-xs text-gray-500">
                                                Klik tombol Tambah Eksemplar untuk menambahkan stok fisik buku.
                                            </p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if(method_exists($bookItems, 'links'))
                        <div class="mt-6">
                            {{ $bookItems->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>