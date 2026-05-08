<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Kepala Sekolah') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-bold">Selamat datang, {{ Auth::user()->name }}!</h3>
                    <p class="mt-1 text-gray-600">
                        Berikut adalah ringkasan statistik Sistem Informasi Perpustakaan saat ini.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                
                <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-blue-500">
                    <div class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total Anggota Aktif</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">{{ $totalMembers }}</div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-green-500">
                    <div class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total Eksemplar Buku</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">{{ $totalBookItems }}</div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-yellow-500">
                    <div class="text-sm font-medium text-gray-500 uppercase tracking-wider">Buku Sedang Dipinjam</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">{{ $borrowedBooks }}</div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-indigo-500">
                    <div class="text-sm font-medium text-gray-500 uppercase tracking-wider">Transaksi Aktif</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">{{ $activeLoans }}</div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-red-500">
                    <div class="text-sm font-medium text-gray-500 uppercase tracking-wider">Buku Rusak / Hilang</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">{{ $problematicBooks }}</div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-orange-500">
                    <div class="text-sm font-medium text-gray-500 uppercase tracking-wider">Terlambat Dikembalikan</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">{{ $overdueItems }}</div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>