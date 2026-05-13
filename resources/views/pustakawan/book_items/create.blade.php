<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-900">Tambah Eksemplar Baru</h2>
                <p class="mt-1 text-sm text-gray-500">Masukkan data barcode dan kondisi fisik buku yang baru masuk.</p>
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
                    <h3 class="text-white text-lg font-semibold">Form Tambah Eksemplar Buku</h3>
                    <p class="text-emerald-100 text-sm mt-1">Lengkapi informasi copy fisik buku dengan benar sesuai kondisi saat diterima.</p>
                </div>
                <div class="p-6 space-y-6">
                    <form method="POST" action="{{ route('book_items.store') }}" class="space-y-6">
                        @csrf

                        <div class="grid gap-6 md:grid-cols-2">
                            <div>
                                <label for="book_id" class="block text-sm font-medium text-gray-700">Judul Buku Induk <span class="text-red-500">*</span></label>
                                <div class="relative mt-2">
                                    <select id="book_id" name="book_id" required style="appearance: none; -webkit-appearance: none; -moz-appearance: none;" class="appearance-none block w-full rounded-2xl border border-green-200 bg-green-50 px-4 py-3 pr-10 text-gray-900 shadow-sm focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                                        <option value="">Pilih Judul Buku</option>
                                        @forelse($books as $book)
                                            <option value="{{ $book->id }}" {{ old('book_id') == $book->id ? 'selected' : '' }}>{{ $book->title }}</option>
                                        @empty
                                            <option value="" disabled>Tidak ada buku tersedia</option>
                                        @endforelse
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-green-500">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </div>
                                </div>
                                @if($books->isEmpty())
                                    <p class="mt-2 text-sm text-yellow-700">Belum ada buku induk. Tambahkan buku terlebih dahulu di menu Katalog Buku.</p>
                                @endif
                                @error('book_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="item_code" class="block text-sm font-medium text-gray-700">Item Code (Barcode) <span class="text-red-500">*</span></label>
                                <input id="item_code" name="item_code" type="text" value="{{ old('item_code') }}" placeholder="Contoh: BK001" required class="mt-2 block w-full rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200" />
                                @error('item_code')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="grid gap-6 md:grid-cols-2">
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Status <span class="text-red-500">*</span></label>
                                <div class="relative mt-2">
                                    <select id="status" name="status" required style="appearance: none; -webkit-appearance: none; -moz-appearance: none;" class="appearance-none block w-full rounded-2xl border border-green-200 bg-green-50 px-4 py-3 pr-10 text-gray-900 shadow-sm focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                                        <option value="">Pilih Status</option>
                                        <option value="tersedia" {{ old('status') == 'tersedia' ? 'selected' : '' }}>Tersedia</option>
                                        <option value="dipinjam" {{ old('status') == 'dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                                        <option value="rusak" {{ old('status') == 'rusak' ? 'selected' : '' }}>Rusak</option>
                                        <option value="hilang" {{ old('status') == 'hilang' ? 'selected' : '' }}>Hilang</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-green-500">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </div>
                                </div>
                                @error('status')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="condition" class="block text-sm font-medium text-gray-700">Kondisi Fisik <span class="text-red-500">*</span></label>
                                <div class="relative mt-2">
                                    <select id="condition" name="condition" required style="appearance: none; -webkit-appearance: none; -moz-appearance: none;" class="appearance-none block w-full rounded-2xl border border-green-200 bg-green-50 px-4 py-3 pr-10 text-gray-900 shadow-sm focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                                        <option value="">Pilih Kondisi</option>
                                        <option value="baik" {{ old('condition') == 'baik' ? 'selected' : '' }}>Baik</option>
                                        <option value="cukup" {{ old('condition') == 'cukup' ? 'selected' : '' }}>Cukup</option>
                                        <option value="rusak" {{ old('condition') == 'rusak' ? 'selected' : '' }}>Rusak</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-green-500">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </div>
                                </div>
                                @error('condition')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
                            <a href="{{ route('book_items.index') }}" class="inline-flex justify-center rounded-full border border-green-200 bg-white px-5 py-3 text-sm font-semibold text-green-700 hover:bg-green-50">Batal</a>
                            <button type="submit" class="inline-flex justify-center rounded-full bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-300">Simpan Eksemplar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
