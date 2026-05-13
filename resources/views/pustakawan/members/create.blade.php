<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-900">Tambah Anggota Baru</h2>
                <p class="mt-1 text-sm text-gray-500">Masukkan data anggota untuk menambah koleksi pengguna perpustakaan.</p>
            </div>
            <a href="{{ route('members.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-emerald-200 text-emerald-700 rounded-lg shadow-sm hover:bg-emerald-50">
                <span>« Kembali</span>
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-3xl border border-emerald-100 overflow-hidden">
                <div class="bg-gradient-to-r from-emerald-500 to-teal-500 p-6">
                    <h3 class="text-white text-lg font-semibold">Form Tambah Anggota</h3>
                    <p class="text-emerald-100 text-sm mt-1">Pastikan semua data terisi sesuai dengan dokumen identitas anggota.</p>
                </div>
                <div class="p-6 space-y-6">
                    <form method="POST" action="{{ route('members.store') }}" class="space-y-6">
                        @csrf

                        <div class="grid gap-6 md:grid-cols-2">
                            <div>
                                <label for="member_code" class="block text-sm font-medium text-gray-700">Kode Anggota</label>
                                <input id="member_code" name="member_code" type="text" value="{{ old('member_code') }}" required class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200" />
                                @error('member_code')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="nis_nip" class="block text-sm font-medium text-gray-700">NIS / NIP</label>
                                <input id="nis_nip" name="nis_nip" type="text" value="{{ old('nis_nip') }}" required class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200" />
                                @error('nis_nip')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="grid gap-6 md:grid-cols-2">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                                <input id="name" name="name" type="text" value="{{ old('name') }}" required class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200" />
                                @error('name')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700">No. HP</label>
                                <input id="phone" name="phone" type="text" value="{{ old('phone') }}" class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200" />
                                @error('phone')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="grid gap-6 md:grid-cols-2">
                            <div>
                                <label for="member_type" class="block text-sm font-medium text-gray-700">Tipe Anggota</label>
                                <div class="relative mt-2">
                                    <select id="member_type" name="member_type" required class="appearance-none block w-full rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 pr-10 text-gray-900 shadow-sm focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                                        <option value="">Pilih Tipe</option>
                                        <option value="siswa" {{ old('member_type') == 'siswa' ? 'selected' : '' }}>Siswa</option>
                                        <option value="guru" {{ old('member_type') == 'guru' ? 'selected' : '' }}>Guru</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-emerald-500">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </div>
                                </div>
                                @error('member_type')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="gender" class="block text-sm font-medium text-gray-700">Jenis Kelamin</label>
                                <div class="relative mt-2">
                                    <select id="gender" name="gender" required class="appearance-none block w-full rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 pr-10 text-gray-900 shadow-sm focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                                        <option value="">Pilih Jenis Kelamin</option>
                                        <option value="laki-laki" {{ old('gender') == 'laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                        <option value="perempuan" {{ old('gender') == 'perempuan' ? 'selected' : '' }}>Perempuan</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-emerald-500">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </div>
                                </div>
                                @error('gender')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="grid gap-6 md:grid-cols-2">
                            <div>
                                <label for="student_class_id" class="block text-sm font-medium text-gray-700">Kelas</label>
                                <div class="relative mt-2">
                                    <select id="student_class_id" name="student_class_id" class="appearance-none block w-full rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 pr-10 text-gray-900 shadow-sm focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                                        <option value="">Pilih Kelas</option>
                                        @forelse($classes as $class)
                                            <option value="{{ $class->id }}" {{ old('student_class_id') == $class->id ? 'selected' : '' }}>{{ $class->class_name }}</option>
                                        @empty
                                            <option value="" disabled>Tidak ada kelas tersedia</option>
                                        @endforelse
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-emerald-500">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </div>
                                </div>
                                @error('student_class_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                <div class="relative mt-2">
                                    <select id="status" name="status" required class="appearance-none block w-full rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 pr-10 text-gray-900 shadow-sm focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                                        <option value="aktif" {{ old('status', 'aktif') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                        <option value="nonaktif" {{ old('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-emerald-500">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </div>
                                </div>
                                @error('status')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div>
                            <label for="card_image" class="block text-sm font-medium text-gray-700">Kartu Anggota</label>
                            <input id="card_image" name="card_image" type="text" value="{{ old('card_image') }}" placeholder="Path atau nama file kartu" class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200" />
                            @error('card_image')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
                            <a href="{{ route('members.index') }}" class="inline-flex justify-center rounded-full border border-emerald-200 bg-white px-5 py-3 text-sm font-semibold text-emerald-700 hover:bg-emerald-50">Batal</a>
                            <button type="submit" class="inline-flex justify-center rounded-full bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-300">Simpan Anggota</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
