<x-app-layout>
    <x-slot name="header">
        <h2 class="font-h1-judul text-xl text-primary leading-tight font-bold">
            {{ __('Dashboard Utama') }}
        </h2>
        <p class="font-body-utama text-sm text-on-surface-variant mt-1">
            Ringkasan aktivitas dan metrik sistem perpustakaan hari ini.
        </p>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col gap-8">
            
            <div class="grid grid-cols-12 gap-6">
                <div class="col-span-12 md:col-span-6 lg:col-span-3 bg-white rounded-xl border border-gray-200 p-6 flex flex-col gap-4 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                    <div class="flex items-center justify-between z-10">
                        <p class="font-label-tombol text-xs font-bold text-gray-500 uppercase">Total Buku</p>
                        <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                            <span class="material-symbols-outlined text-[#006130]" style="font-variation-settings: 'FILL' 1;">library_books</span>
                        </div>
                    </div>
                    <div class="z-10">
                        <h3 class="font-h1-judul text-3xl font-bold text-gray-900">12,450</h3>
                        <p class="font-body-kecil text-xs text-green-600 mt-1 flex items-center gap-1">
                            <span class="material-symbols-outlined text-[16px]">trending_up</span> +124 bulan ini
                        </p>
                    </div>
                </div>

                <div class="col-span-12 md:col-span-6 lg:col-span-3 bg-white rounded-xl border border-gray-200 p-6 flex flex-col gap-4 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                    <div class="flex items-center justify-between z-10">
                        <p class="font-label-tombol text-xs font-bold text-gray-500 uppercase">Anggota Aktif</p>
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                            <span class="material-symbols-outlined text-blue-600" style="font-variation-settings: 'FILL' 1;">group</span>
                        </div>
                    </div>
                    <div class="z-10">
                        <h3 class="font-h1-judul text-3xl font-bold text-gray-900">842</h3>
                        <p class="font-body-kecil text-xs text-blue-600 mt-1 flex items-center gap-1">
                            <span class="material-symbols-outlined text-[16px]">trending_up</span> +12 minggu ini
                        </p>
                    </div>
                </div>

                <div class="col-span-12 md:col-span-6 lg:col-span-3 bg-white rounded-xl border border-gray-200 p-6 flex flex-col gap-4 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                    <div class="flex items-center justify-between z-10">
                        <p class="font-label-tombol text-xs font-bold text-gray-500 uppercase">Peminjaman Hari Ini</p>
                        <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center">
                            <span class="material-symbols-outlined text-indigo-600" style="font-variation-settings: 'FILL' 1;">swap_horiz</span>
                        </div>
                    </div>
                    <div class="z-10">
                        <h3 class="font-h1-judul text-3xl font-bold text-gray-900">156</h3>
                        <p class="font-body-kecil text-xs text-gray-500 mt-1 flex items-center gap-1">
                            42 masih diproses
                        </p>
                    </div>
                </div>

                <div class="col-span-12 md:col-span-6 lg:col-span-3 bg-white rounded-xl border border-gray-200 p-6 flex flex-col gap-4 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                    <div class="flex items-center justify-between z-10">
                        <p class="font-label-tombol text-xs font-bold text-gray-500 uppercase">Denda Belum Dibayar</p>
                        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                            <span class="material-symbols-outlined text-[#ba1a1a]" style="font-variation-settings: 'FILL' 1;">payments</span>
                        </div>
                    </div>
                    <div class="z-10">
                        <h3 class="font-h2-subjudul text-2xl font-bold text-[#ba1a1a]">Rp 245.000</h3>
                        <p class="font-body-kecil text-xs text-gray-500 mt-1 flex items-center gap-1">
                            Dari 15 anggota
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-12 gap-6">
                <div class="col-span-12 lg:col-span-4 bg-white rounded-xl border border-gray-200 p-6 flex flex-col gap-4 shadow-sm">
                    <h3 class="font-bold text-lg text-gray-900 mb-2">Buku Sering Dipinjam</h3>
                    
                    <div class="flex items-center gap-4 p-3 rounded-lg bg-gray-50 border border-gray-100">
                        <div class="w-12 h-16 bg-gray-200 rounded flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-gray-500">menu_book</span>
                        </div>
                        <div>
                            <h4 class="font-bold text-sm text-gray-900 line-clamp-1">Laskar Pelangi</h4>
                            <p class="text-xs text-gray-500">Andrea Hirata</p>
                            <p class="text-[12px] text-[#006130] mt-1 font-bold">45 kali dipinjam</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 p-3 rounded-lg bg-gray-50 border border-gray-100">
                        <div class="w-12 h-16 bg-gray-200 rounded flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-gray-500">menu_book</span>
                        </div>
                        <div>
                            <h4 class="font-bold text-sm text-gray-900 line-clamp-1">Bumi Manusia</h4>
                            <p class="text-xs text-gray-500">Pramoedya A. Toer</p>
                            <p class="text-[12px] text-[#006130] mt-1 font-bold">38 kali dipinjam</p>
                        </div>
                    </div>

                    <button class="mt-auto w-full py-2 border border-[#006130] text-[#006130] text-sm font-bold rounded-lg hover:bg-green-50 transition-colors">
                        Lihat Laporan Lengkap
                    </button>
                </div>

                <div class="col-span-12 lg:col-span-8 bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden flex flex-col">
                    <div class="p-6 border-b border-gray-200 flex items-center justify-between bg-gray-50">
                        <h3 class="font-bold text-lg text-gray-900">Transaksi Terbaru</h3>
                        <a href="{{ route('loans.index') }}" class="flex items-center gap-2 text-[#006130] text-sm font-bold hover:bg-green-50 px-3 py-1.5 rounded-lg transition-colors">
                            Lihat Semua <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                        </a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="py-3 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider">ID Transaksi</th>
                                    <th class="py-3 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Peminjam</th>
                                    <th class="py-3 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="py-3 px-4 text-sm text-gray-900">#TRX-2023-089</td>
                                    <td class="py-3 px-4 text-sm text-gray-900 font-semibold">Budi Santoso</td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            Dikembalikan
                                        </span>
                                    </td>
                                </tr>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="py-3 px-4 text-sm text-gray-900">#TRX-2023-090</td>
                                    <td class="py-3 px-4 text-sm text-gray-900 font-semibold">Siti Aminah</td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Dipinjam
                                        </span>
                                    </td>
                                </tr>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="py-3 px-4 text-sm text-gray-900">#TRX-2023-091</td>
                                    <td class="py-3 px-4 text-sm text-gray-900 font-semibold">Reza Rahadian</td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Terlambat
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>