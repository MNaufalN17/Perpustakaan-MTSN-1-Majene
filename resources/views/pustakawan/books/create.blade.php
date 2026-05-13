<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-900">Tambah Buku Baru</h2>
                <p class="mt-1 text-sm text-gray-500">Masukkan data buku induk yang akan disimpan di katalog perpustakaan.</p>
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
                    <h3 class="text-white text-lg font-semibold">Form Tambah Buku</h3>
                    <p class="text-emerald-100 text-sm mt-1">Lengkapi informasi buku dengan tepat agar mudah dicari oleh pengguna.</p>
                </div>
                <div class="p-6 space-y-6">
                    <form method="POST" action="{{ route('books.store') }}" class="space-y-6">
                        @csrf

                        <div class="grid gap-6 md:grid-cols-2">
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700">Judul Buku</label>
                                <input id="title" name="title" type="text" value="{{ old('title') }}" required class="mt-2 block w-full rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200" />
                                @error('title')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="author" class="block text-sm font-medium text-gray-700">Penulis</label>
                                <input id="author" name="author" type="text" value="{{ old('author') }}" required class="mt-2 block w-full rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200" />
                                @error('author')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="grid gap-6 md:grid-cols-2">
                            <div>
                                <label for="publisher" class="block text-sm font-medium text-gray-700">Penerbit</label>
                                <input id="publisher" name="publisher" type="text" value="{{ old('publisher') }}" required class="mt-2 block w-full rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200" />
                                @error('publisher')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="category_id" class="block text-sm font-medium text-gray-700">Kategori</label>
                                <div class="relative mt-2">
                                    <select id="category_id" name="category_id" required style="appearance: none; -webkit-appearance: none; -moz-appearance: none;" class="appearance-none block w-full rounded-2xl border border-green-200 bg-green-50 px-4 py-3 pr-10 text-gray-900 shadow-sm focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                                        <option value="">Pilih Kategori</option>
                                        @forelse($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                        @empty
                                            <option value="" disabled>Tidak ada kategori tersedia</option>
                                        @endforelse
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-green-500">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </div>
                                </div>
                                @if($categories->isEmpty())
                                    <p class="mt-2 text-sm text-yellow-700">Belum ada data kategori. Tambahkan kategori terlebih dahulu di menu Kategori.</p>
                                @endif
                                </div>
                                @error('category_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="grid gap-6 md:grid-cols-2">
                            <div>
                                <label for="ddc_class_id" class="block text-sm font-medium text-gray-700">Kelas DDC</label>
                                <div class="relative mt-2">
                                    <select id="ddc_class_id" name="ddc_class_id" required style="appearance: none; -webkit-appearance: none; -moz-appearance: none;" class="appearance-none block w-full rounded-2xl border border-green-200 bg-green-50 px-4 py-3 pr-10 text-gray-900 shadow-sm focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                                        <option value="">Pilih DDC</option>
                                        @forelse($ddcClasses as $ddcClass)
                                            <option value="{{ $ddcClass->id }}" {{ old('ddc_class_id') == $ddcClass->id ? 'selected' : '' }}>{{ $ddcClass->code }}</option>
                                        @empty
                                            <option value="" disabled>Tidak ada kelas DDC tersedia</option>
                                        @endforelse
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-green-500">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </div>
                                </div>
                                @if($ddcClasses->isEmpty())
                                    <p class="mt-2 text-sm text-yellow-700">Belum ada data kelas DDC. Tambahkan DDC terlebih dahulu di menu Kelas DDC.</p>
                                @endif
                                </div>
                                @error('ddc_class_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
                            <a href="{{ route('books.index') }}" class="inline-flex justify-center rounded-full border border-green-200 bg-white px-5 py-3 text-sm font-semibold text-green-700 hover:bg-green-50">Batal</a>
                            <button type="submit" class="inline-flex justify-center rounded-full bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-300">Simpan Buku</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>