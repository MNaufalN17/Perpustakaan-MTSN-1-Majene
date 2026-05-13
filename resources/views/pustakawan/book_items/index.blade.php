<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-900">Eksemplar Buku</h2>
                <p class="mt-1 text-sm text-gray-500">Kelola daftar copy fisik buku dengan cepat dan mudah.</p>
            </div>
            <a href="{{ route('book_items.create') }}" class="inline-flex items-center justify-center rounded-full bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
                Tambah Eksemplar Baru
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-3xl bg-white shadow-sm border border-green-100">
                <div class="bg-gradient-to-r from-emerald-500 to-teal-500 p-6">
                    <h3 class="text-white text-lg font-semibold">Daftar Eksemplar Buku</h3>
                    <p class="text-emerald-100 mt-1 text-sm">Lihat semua copy fisik buku dan kelola data dengan mudah.</p>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto rounded-3xl border border-green-100 bg-green-50 p-4">
                        <table class="min-w-full divide-y divide-green-200 text-left text-sm">
                            <thead class="rounded-3xl bg-white text-xs uppercase tracking-wider text-green-700">
                                <tr>
                                    <th class="px-6 py-3">Item Code</th>
                                    <th class="px-6 py-3">Judul Buku</th>
                                    <th class="px-6 py-3">Status</th>
                                    <th class="px-6 py-3">Kondisi</th>
                                    <th class="px-6 py-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-green-100 bg-white">
                                @forelse($bookItems as $item)
                                    <tr class="hover:bg-green-50">
                                        <td class="px-6 py-4 font-mono text-gray-900">{{ $item->item_code }}</td>
                                        <td class="px-6 py-4 text-gray-700">{{ $item->book->title }}</td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold
                                                {{ $item->status === 'tersedia' ? 'bg-green-100 text-green-700' : '' }}
                                                {{ $item->status === 'dipinjam' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                                {{ $item->status === 'rusak' ? 'bg-red-100 text-red-700' : '' }}
                                                {{ $item->status === 'hilang' ? 'bg-gray-100 text-gray-700' : '' }}
                                            ">
                                                {{ ucfirst($item->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold
                                                {{ $item->condition === 'baik' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                                {{ $item->condition === 'cukup' ? 'bg-amber-100 text-amber-700' : '' }}
                                                {{ $item->condition === 'rusak' ? 'bg-orange-100 text-orange-700' : '' }}
                                            ">
                                                {{ ucfirst($item->condition) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm font-medium text-green-700">
                                            <a href="{{ route('book_items.show', $item) }}" class="inline-flex items-center gap-2 rounded-full px-3 py-1 hover:bg-green-100">Lihat</a>
                                            <a href="{{ route('book_items.edit', $item) }}" class="inline-flex items-center gap-2 rounded-full px-3 py-1 hover:bg-green-100">Edit</a>
                                            <form method="POST" action="{{ route('book_items.destroy', $item) }}" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-red-600 hover:bg-red-100" onclick="return confirm('Apakah Anda yakin ingin menghapus eksemplar ini?')">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">Tidak ada eksemplar buku. Silakan tambahkan eksemplar baru.</td>
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
