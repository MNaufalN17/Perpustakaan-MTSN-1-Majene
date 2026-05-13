<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-900">Manajemen Anggota</h2>
                <p class="mt-1 text-sm text-gray-500">Kelola data anggota perpustakaan secara cepat dan rapi.</p>
            </div>
            <a href="{{ route('members.create') }}" class="inline-flex items-center justify-center rounded-full bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
                Tambah Anggota Baru
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            <div class="overflow-hidden rounded-3xl bg-white shadow-sm border border-emerald-100">
                <div class="bg-gradient-to-r from-emerald-500 to-teal-500 p-6">
                    <h3 class="text-white text-lg font-semibold">Daftar Anggota</h3>
                    <p class="text-emerald-100 mt-1 text-sm">Lihat semua data anggota dan lakukan tindakan seperti lihat, edit, atau hapus.</p>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto rounded-3xl border border-emerald-100 bg-emerald-50 p-4">
                        <table class="min-w-full divide-y divide-emerald-200 text-left text-sm">
                            <thead class="rounded-3xl bg-white text-xs uppercase tracking-wider text-emerald-700">
                                <tr>
                                    <th class="px-6 py-3">Kode Anggota</th>
                                    <th class="px-6 py-3">Nama</th>
                                    <th class="px-6 py-3">Tipe</th>
                                    <th class="px-6 py-3">Kelas</th>
                                    <th class="px-6 py-3">Status</th>
                                    <th class="px-6 py-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-emerald-100 bg-white">
                                @forelse($members as $member)
                                    <tr class="hover:bg-emerald-50">
                                        <td class="px-6 py-4 text-gray-900">{{ $member->member_code }}</td>
                                        <td class="px-6 py-4 text-gray-700">{{ $member->name }}</td>
                                        <td class="px-6 py-4 text-gray-700 capitalize">{{ $member->member_type }}</td>
                                        <td class="px-6 py-4 text-gray-700">{{ $member->studentClass->class_name ?? '-' }}</td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $member->status === 'aktif' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600' }}">
                                                {{ strtoupper($member->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm font-medium text-emerald-700 space-x-2">
                                            <a href="{{ route('members.show', $member) }}" class="inline-flex items-center gap-2 rounded-full px-3 py-1 hover:bg-emerald-100">Lihat</a>
                                            <a href="{{ route('members.edit', $member) }}" class="inline-flex items-center gap-2 rounded-full px-3 py-1 hover:bg-emerald-100">Edit</a>
                                            <form method="POST" action="{{ route('members.destroy', $member) }}" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-red-600 hover:bg-red-100" onclick="return confirm('Apakah Anda yakin ingin menghapus anggota ini?')">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">Belum ada anggota. Gunakan tombol Tambah Anggota Baru untuk memulai.</td>
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
