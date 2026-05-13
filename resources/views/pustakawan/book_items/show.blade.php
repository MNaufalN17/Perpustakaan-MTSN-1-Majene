<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-900">Detail Eksemplar Buku</h2>
                <p class="mt-1 text-sm text-gray-500">Lihat informasi lengkap copy fisik buku yang tersimpan.</p>
            </div>
            <a href="{{ route('book_items.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-green-200 text-green-700 rounded-lg shadow-sm hover:bg-green-50">
                <span>« Kembali</span>
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-3xl border border-green-100 overflow-hidden">
                <div class="bg-gradient-to-r from-emerald-500 to-teal-500 p-6">
                    <h3 class="text-white text-lg font-semibold">{{ $bookItem->book->title }}</h3>
                    <p class="text-emerald-100 text-sm mt-1">Item Code: <span class="font-mono">{{ $bookItem->item_code }}</span></p>
                </div>

                <div class="p-6 space-y-8">
                    <!-- Informasi Buku Induk -->
                    <div class="pb-6 border-b border-green-100">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Informasi Buku Induk</h4>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <p class="text-sm text-gray-600">Judul</p>
                                <p class="text-base font-medium text-gray-900">{{ $bookItem->book->title }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Penulis</p>
                                <p class="text-base font-medium text-gray-900">{{ $bookItem->book->author }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Penerbit</p>
                                <p class="text-base font-medium text-gray-900">{{ $bookItem->book->publisher }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Kategori</p>
                                <p class="text-base font-medium text-gray-900">{{ $bookItem->book->category->name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Kelas DDC</p>
                                <p class="text-base font-medium text-gray-900">{{ $bookItem->book->ddcClass->code }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Informasi Eksemplar Fisik -->
                    <div class="pb-6 border-b border-green-100">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Informasi Eksemplar Fisik</h4>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <p class="text-sm text-gray-600">Item Code (Barcode)</p>
                                <p class="text-base font-mono font-medium text-gray-900">{{ $bookItem->item_code }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Status</p>
                                <div class="mt-1">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold
                                        {{ $bookItem->status === 'tersedia' ? 'bg-green-100 text-green-700' : '' }}
                                        {{ $bookItem->status === 'dipinjam' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                        {{ $bookItem->status === 'rusak' ? 'bg-red-100 text-red-700' : '' }}
                                        {{ $bookItem->status === 'hilang' ? 'bg-gray-100 text-gray-700' : '' }}
                                    ">
                                        {{ ucfirst($bookItem->status) }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Kondisi Fisik</p>
                                <div class="mt-1">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold
                                        {{ $bookItem->condition === 'baik' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                        {{ $bookItem->condition === 'cukup' ? 'bg-amber-100 text-amber-700' : '' }}
                                        {{ $bookItem->condition === 'rusak' ? 'bg-orange-100 text-orange-700' : '' }}
                                    ">
                                        {{ ucfirst($bookItem->condition) }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Terakhir Diperbarui</p>
                                <p class="text-base font-medium text-gray-900">{{ $bookItem->updated_at->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Aksi -->
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
                        <a href="{{ route('book_items.edit', $bookItem) }}" class="inline-flex justify-center rounded-full bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                            Edit Eksemplar
                        </a>
                        <form method="POST" action="{{ route('book_items.destroy', $bookItem) }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex justify-center rounded-full bg-red-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-red-700" onclick="return confirm('Apakah Anda yakin ingin menghapus eksemplar ini?')">
                                Hapus Eksemplar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
