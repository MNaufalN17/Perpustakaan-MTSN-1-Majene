<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
                    Sirkulasi Perpustakaan
                </p>
                <h2 class="mt-1 font-semibold text-xl text-gray-900">
                    Manajemen Sirkulasi
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Kelola data peminjaman, pengembalian, dan pantau status transaksi perpustakaan.
                </p>
            </div>

            <a href="{{ route('loans.create') }}"
               class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800">
                <span class="material-symbols-outlined text-[18px]">add_circle</span>
                Peminjaman Baru
            </a>
        </div>
    </x-slot>

    <div class="py-10 bg-gradient-to-br from-slate-50 via-emerald-50/40 to-sky-50/40 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800 flex items-center gap-2 shadow-sm">
                    <span class="material-symbols-outlined text-[20px]">check_circle</span>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 shadow-sm">
                    <div class="font-bold mb-2">Terjadi kesalahan:</div>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="overflow-hidden rounded-3xl bg-white/75 backdrop-blur-xl shadow-sm border border-white/70">
                <div class="bg-gradient-to-r from-emerald-600 to-teal-500 p-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-white text-lg font-semibold">
                                Riwayat Transaksi
                            </h3>
                            <p class="text-emerald-50 mt-1 text-sm">
                                Pantau daftar buku yang sedang dipinjam, terlambat, selesai, atau dibatalkan.
                            </p>
                        </div>

                        <div class="hidden sm:flex h-11 w-11 items-center justify-center rounded-2xl bg-white/20 text-white">
                            <span class="material-symbols-outlined">receipt_long</span>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <div class="overflow-x-auto rounded-3xl border border-emerald-100 bg-emerald-50/70 p-4">
                        <table class="min-w-full divide-y divide-emerald-100 text-left text-sm">
                            <thead class="bg-white text-xs uppercase tracking-wider text-emerald-700">
                                <tr>
                                    <th class="px-6 py-3 font-bold">Kode Transaksi</th>
                                    <th class="px-6 py-3 font-bold">Nama Peminjam</th>
                                    <th class="px-6 py-3 font-bold">Tgl Pinjam</th>
                                    <th class="px-6 py-3 font-bold">Jatuh Tempo</th>
                                    <th class="px-6 py-3 font-bold">Status</th>
                                    <th class="px-6 py-3 font-bold">Aksi</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-emerald-50 bg-white">
                                @forelse($loans as $loan)
                                    @php
                                        $isOverdue = \Carbon\Carbon::now()->startOfDay()->gt(\Carbon\Carbon::parse($loan->due_date)->startOfDay())
                                            && in_array($loan->status, ['aktif', 'terlambat']);

                                        $canProcess = in_array($loan->status, ['aktif', 'terlambat']) || $isOverdue;
                                    @endphp

                                    <tr class="transition-colors hover:bg-emerald-50/70">
                                        <td class="px-6 py-4">
                                            <p class="font-semibold text-gray-900">
                                                {{ $loan->loan_code }}
                                            </p>
                                        </td>

                                        <td class="px-6 py-4">
                                            <p class="font-semibold text-gray-900">
                                                {{ $loan->member->name ?? '-' }}
                                            </p>
                                            <p class="mt-1 text-xs text-gray-500">
                                                {{ $loan->member->nis_nip ?? '-' }}
                                            </p>
                                        </td>

                                        <td class="px-6 py-4 text-gray-700">
                                            {{ \Carbon\Carbon::parse($loan->loan_date)->format('d M Y') }}
                                        </td>

                                        <td class="px-6 py-4">
                                            <span class="{{ $isOverdue || $loan->status === 'terlambat' ? 'inline-flex items-center gap-1 font-bold text-red-600' : 'text-gray-700' }}">
                                                @if($isOverdue || $loan->status === 'terlambat')
                                                    <span class="material-symbols-outlined text-[16px]">warning</span>
                                                @endif

                                                {{ \Carbon\Carbon::parse($loan->due_date)->format('d M Y') }}
                                            </span>
                                        </td>

                                        <td class="px-6 py-4">
                                            @if($loan->status === 'terlambat' || $isOverdue)
                                                <span class="inline-flex rounded-full border border-red-200 bg-red-50 px-3 py-1 text-xs font-bold text-red-700">
                                                    TERLAMBAT
                                                </span>
                                            @elseif($loan->status === 'aktif')
                                                <span class="inline-flex rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">
                                                    DIPINJAM
                                                </span>
                                            @elseif($loan->status === 'selesai')
                                                <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                                                    SELESAI
                                                </span>
                                            @elseif($loan->status === 'batal')
                                                <span class="inline-flex rounded-full border border-gray-200 bg-gray-50 px-3 py-1 text-xs font-bold text-gray-600">
                                                    BATAL
                                                </span>
                                            @else
                                                <span class="inline-flex rounded-full border border-gray-200 bg-gray-50 px-3 py-1 text-xs font-bold text-gray-600">
                                                    {{ strtoupper($loan->status) }}
                                                </span>
                                            @endif
                                        </td>

                                        <td class="px-6 py-4">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <a href="{{ route('loans.show', $loan->id) }}"
                                                   class="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700 transition hover:bg-emerald-100">
                                                    <span class="material-symbols-outlined text-[15px]">visibility</span>
                                                    Lihat
                                                </a>

                                                @if($canProcess)
                                                    <a href="{{ route('loans.edit', $loan->id) }}"
                                                       class="inline-flex items-center gap-1 rounded-full border border-teal-200 bg-teal-50 px-3 py-1.5 text-xs font-bold text-teal-700 transition hover:bg-teal-100">
                                                        <span class="material-symbols-outlined text-[15px]">assignment_return</span>
                                                        Proses
                                                    </a>

                                                    <form action="{{ route('loans.destroy', $loan->id) }}"
                                                          method="POST"
                                                          class="inline"
                                                          onsubmit="return confirm('Batalkan transaksi ini? Riwayat tetap disimpan dan status buku akan diperbarui.');">
                                                        @csrf
                                                        @method('DELETE')

                                                        <button type="submit"
                                                                class="inline-flex items-center gap-1 rounded-full border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-bold text-red-700 transition hover:bg-red-100">
                                                            <span class="material-symbols-outlined text-[15px]">cancel</span>
                                                            Batalkan
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-14 text-center">
                                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                                                <span class="material-symbols-outlined">receipt_long</span>
                                            </div>
                                            <p class="mt-4 text-sm font-semibold text-gray-700">
                                                Belum ada data transaksi peminjaman.
                                            </p>
                                            <p class="mt-1 text-xs text-gray-500">
                                                Klik tombol Peminjaman Baru untuk memulai transaksi.
                                            </p>
                                        </td>
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