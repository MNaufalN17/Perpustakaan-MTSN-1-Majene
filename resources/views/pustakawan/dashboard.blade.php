<!-- <x-app-layout> adalah komponen bawaan Breeze untuk memanggil kerangka navigasi dan desain dasar -->
<x-app-layout>
    
    <!-- Bagian ini untuk mengisi judul di bagian atas halaman (Header) -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Pustakawan') }}
        </h2>
    </x-slot>

    <!-- Bagian ini adalah isi/konten utama dari halaman dashboard -->
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-bold">Selamat datang, {{ Auth::user()->name }}!</h3>
                    <p class="mt-2 text-gray-600">
                        Anda saat ini login sebagai Pustakawan. Melalui panel ini, Anda dapat mengelola data buku, anggota, dan transaksi sirkulasi perpustakaan.
                    </p>
                    
                    <!-- Kotak kosong ini nantinya akan kita isi dengan grafik atau ringkasan data (Total Buku, Peminjaman, dll) -->
                    <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-md">
                        <p class="text-sm text-blue-800">
                            Modul ringkasan statistik dan fitur pengelolaan akan segera ditambahkan di sini.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>