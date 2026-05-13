<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-900">Detail Buku</h2>
                <p class="mt-1 text-sm text-gray-500">Lihat informasi lengkap dari buku yang ada di katalog induk.</p>
            </div>
            <a href="{{ route('books.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-green-200 text-green-700 rounded-lg shadow-sm hover:bg-green-50">
                <span>« Kembali</span>
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-3xl border border-green-100 overflow-hidden">
                <div class="bg-gradient-to-r from-emerald-500 to-teal-500 p-6">
                    <h3 class="text-white text-lg font-semibold">Informasi Buku</h3>
                    <p class="text-emerald-100 text-sm mt-1">Detail data buku berikut digunakan untuk pengelolaan perpustakaan.</p>
                </div>
                <div class="p-6 space-y-6">
                    <div class="grid gap-6 sm:grid-cols-2">
                        <div class="rounded-3xl border border-green-100 bg-green-50 p-5">
                            <h4 class="text-sm font-semibold text-green-900">Judul</h4>
                            <p class="mt-2 text-gray-800">{{ $book->title }}</p>
                        </div>
                        <div class="rounded-3xl border border-green-100 bg-green-50 p-5">
                            <h4 class="text-sm font-semibold text-green-900">Penulis</h4>
                            <p class="mt-2 text-gray-800">{{ $book->author }}</p>
                        </div>
                        <div class="rounded-3xl border border-green-100 bg-green-50 p-5">
                            <h4 class="text-sm font-semibold text-green-900">Penerbit</h4>
                            <p class="mt-2 text-gray-800">{{ $book->publisher }}</p>
                        </div>
                        <div class="rounded-3xl border border-green-100 bg-green-50 p-5">
                            <h4 class="text-sm font-semibold text-green-900">Kategori</h4>
                            <p class="mt-2 text-gray-800">{{ $book->category->name }}</p>
                        </div>
                        <div class="rounded-3xl border border-green-100 bg-green-50 p-5 sm:col-span-2">
                            <h4 class="text-sm font-semibold text-green-900">Kelas DDC</h4>
                            <p class="mt-2 text-gray-800">{{ $book->ddcClass->code }}</p>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                        <a href="{{ route('books.edit', $book) }}" class="inline-flex justify-center rounded-full bg-emerald-600 px-5 py-3 text-sm font-semibold text-white hover:bg-emerald-700">Edit Buku</a>
                        <form method="POST" action="{{ route('books.destroy', $book) }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Apakah Anda yakin ingin menghapus buku ini?')" class="inline-flex justify-center rounded-full border border-red-200 bg-white px-5 py-3 text-sm font-semibold text-red-600 hover:bg-red-50">Hapus Buku</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>