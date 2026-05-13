<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-900">Katalog Buku Induk</h2>
                <p class="mt-1 text-sm text-gray-500">Kelola daftar buku utama dengan cepat dan tampilan yang menarik.</p>
            </div>
            <a href="{{ route('books.create') }}" class="inline-flex items-center justify-center rounded-full bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
                Tambah Buku Baru
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-3xl bg-white shadow-sm border border-green-100">
                <div class="bg-gradient-to-r from-emerald-500 to-teal-500 p-6">
                    <h3 class="text-white text-lg font-semibold">Daftar Buku</h3>
                    <p class="text-emerald-100 mt-1 text-sm">Lihat semua buku induk dan kelola data dengan mudah.</p>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto rounded-3xl border border-green-100 bg-green-50 p-4">
                        <table class="min-w-full divide-y divide-green-200 text-left text-sm">
                            <thead class="rounded-3xl bg-white text-xs uppercase tracking-wider text-green-700">
                                <tr>
                                    <th class="px-6 py-3">Judul</th>
                                    <th class="px-6 py-3">Penulis</th>
                                    <th class="px-6 py-3">Penerbit</th>
                                    <th class="px-6 py-3">Kategori</th>
                                    <th class="px-6 py-3">DDC</th>
                                    <th class="px-6 py-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-green-100 bg-white">
                                @forelse($books as $book)
                                    <tr class="hover:bg-green-50">
                                        <td class="px-6 py-4 text-gray-900">{{ $book->title }}</td>
                                        <td class="px-6 py-4 text-gray-700">{{ $book->author }}</td>
                                        <td class="px-6 py-4 text-gray-700">{{ $book->publisher }}</td>
                                        <td class="px-6 py-4 text-gray-700">{{ $book->category->name }}</td>
                                        <td class="px-6 py-4 text-gray-700">{{ $book->ddcClass->code }}</td>
                                        <td class="px-6 py-4 text-sm font-medium text-green-700">
                                            <a href="{{ route('books.show', $book) }}" class="inline-flex items-center gap-2 rounded-full px-3 py-1 hover:bg-green-100">Lihat</a>
                                            <a href="{{ route('books.edit', $book) }}" class="inline-flex items-center gap-2 rounded-full px-3 py-1 hover:bg-green-100">Edit</a>
                                            <form method="POST" action="{{ route('books.destroy', $book) }}" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-red-600 hover:bg-red-100" onclick="return confirm('Apakah Anda yakin ingin menghapus buku ini?')">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">Tidak ada buku dalam katalog. Silakan tambahkan buku baru.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>