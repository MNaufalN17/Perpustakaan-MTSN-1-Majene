<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-900">Detail Anggota</h2>
                <p class="mt-1 text-sm text-gray-500">Lihat informasi lengkap anggota perpustakaan.</p>
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
                    <h3 class="text-white text-lg font-semibold">Informasi Anggota</h3>
                    <p class="text-emerald-100 text-sm mt-1">Detail data ini digunakan sebagai referensi dalam seluruh proses peminjaman.</p>
                </div>
                <div class="p-6 space-y-6">
                    <div class="grid gap-6 sm:grid-cols-2">
                        <div class="rounded-3xl border border-emerald-100 bg-emerald-50 p-5">
                            <h4 class="text-sm font-semibold text-emerald-900">Kode Anggota</h4>
                            <p class="mt-2 text-gray-800">{{ $member->member_code }}</p>
                        </div>
                        <div class="rounded-3xl border border-emerald-100 bg-emerald-50 p-5">
                            <h4 class="text-sm font-semibold text-emerald-900">NIS / NIP</h4>
                            <p class="mt-2 text-gray-800">{{ $member->nis_nip }}</p>
                        </div>
                        <div class="rounded-3xl border border-emerald-100 bg-emerald-50 p-5">
                            <h4 class="text-sm font-semibold text-emerald-900">Nama</h4>
                            <p class="mt-2 text-gray-800">{{ $member->name }}</p>
                        </div>
                        <div class="rounded-3xl border border-emerald-100 bg-emerald-50 p-5">
                            <h4 class="text-sm font-semibold text-emerald-900">Tipe Anggota</h4>
                            <p class="mt-2 text-gray-800 capitalize">{{ $member->member_type }}</p>
                        </div>
                        <div class="rounded-3xl border border-emerald-100 bg-emerald-50 p-5">
                            <h4 class="text-sm font-semibold text-emerald-900">Jenis Kelamin</h4>
                            <p class="mt-2 text-gray-800 capitalize">{{ $member->gender }}</p>
                        </div>
                        <div class="rounded-3xl border border-emerald-100 bg-emerald-50 p-5">
                            <h4 class="text-sm font-semibold text-emerald-900">Kelas</h4>
                            <p class="mt-2 text-gray-800">{{ $member->studentClass->class_name ?? '-' }}</p>
                        </div>
                        <div class="rounded-3xl border border-emerald-100 bg-emerald-50 p-5">
                            <h4 class="text-sm font-semibold text-emerald-900">No. HP</h4>
                            <p class="mt-2 text-gray-800">{{ $member->phone ?? '-' }}</p>
                        </div>
                        <div class="rounded-3xl border border-emerald-100 bg-emerald-50 p-5">
                            <h4 class="text-sm font-semibold text-emerald-900">Status</h4>
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $member->status === 'aktif' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600' }}">{{ strtoupper($member->status) }}</span>
                        </div>
                        <div class="sm:col-span-2 rounded-3xl border border-emerald-100 bg-emerald-50 p-5">
                            <h4 class="text-sm font-semibold text-emerald-900">Kartu Anggota</h4>
                            <p class="mt-2 text-gray-800">{{ $member->card_image ?? 'Belum terisi' }}</p>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                        <a href="{{ route('members.edit', $member) }}" class="inline-flex justify-center rounded-full bg-emerald-600 px-5 py-3 text-sm font-semibold text-white hover:bg-emerald-700">Edit Anggota</a>
                        <form method="POST" action="{{ route('members.destroy', $member) }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Apakah Anda yakin ingin menghapus anggota ini?')" class="inline-flex justify-center rounded-full border border-red-200 bg-white px-5 py-3 text-sm font-semibold text-red-600 hover:bg-red-50">Hapus Anggota</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
