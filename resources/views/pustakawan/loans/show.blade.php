<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-xl text-primary leading-tight">
                {{ __('Detail Struk Transaksi') }}
            </h2>
            <!-- Tombol Kembali -->
            <a href="{{ route('loans.index') }}" class="text-sm font-bold text-gray-500 hover:text-primary transition-colors flex items-center gap-1">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali ke Tabel
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <!-- Kotak Struk -->
            <div class="bg-white overflow-hidden shadow-md sm:rounded-xl border border-gray-200 p-8" id="print-area">
                
                <!-- Kop Surat / Header Struk -->
                <div class="flex items-center justify-between border-b border-gray-200 pb-6 mb-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center text-green-700">
                            <span class="material-symbols-outlined text-3xl" style="font-variation-settings: 'FILL' 1;">local_library</span>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">SIM Perpustakaan</h3>
                            <p class="text-sm text-gray-500">MTsN 1 Majene</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <h4 class="font-bold text-lg text-primary">{{ $loan->loan_code }}</h4>
                        <p class="text-xs text-gray-500 font-medium">Nota Peminjaman</p>
                    </div>
                </div>

                <!-- Informasi Transaksi -->
                <div class="grid grid-cols-2 gap-6 mb-8 bg-gray-50 p-6 rounded-lg border border-gray-100">
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Data Peminjam</p>
                        <p class="font-bold text-gray-900">{{ $loan->member->name }}</p>
                        <p class="text-sm text-gray-600">ID: {{ $loan->member->member_code }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Status Transaksi</p>
                        @if($loan->status == 'aktif')
                            <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-bold border border-yellow-200 inline-block">AKTIF / DIPINJAM</span>
                        @else
                            <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-bold border border-gray-200 inline-block">SELESAI / KEMBALI</span>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Tanggal Pinjam</p>
                        <p class="font-bold text-gray-900">{{ \Carbon\Carbon::parse($loan->loan_date)->format('d M Y') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Batas Kembali</p>
                        <p class="font-bold text-primary">{{ \Carbon\Carbon::parse($loan->due_date)->format('d M Y') }}</p>
                    </div>
                </div>

                <!-- Tabel Item Buku -->
                <div class="mb-8">
                    <p class="text-sm text-gray-500 uppercase font-bold tracking-wider mb-3">Item Buku yang Dipinjam</p>
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b-2 border-gray-200 text-sm text-gray-900">
                                <th class="pb-2 font-bold w-12">No</th>
                                <th class="pb-2 font-bold">Judul Buku</th>
                                <th class="pb-2 font-bold">Barcode FIsik</th>
                                <th class="pb-2 font-bold text-right">Kondisi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm text-gray-700">
                            @foreach($loan->loanItems as $index => $item)
                                <tr class="border-b border-gray-100">
                                    <td class="py-3 text-gray-500">{{ $index + 1 }}</td>
                                    <td class="py-3 font-semibold text-gray-900">{{ $item->bookItem->book->title }}</td>
                                    <td class="py-3">{{ $item->bookItem->item_code }}</td>
                                    <td class="py-3 text-right capitalize">{{ $item->return_condition ?? $item->bookItem->condition }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Footer Struk -->
                <div class="text-center text-xs text-gray-400 mt-12">
                    <p>Harap kembalikan buku tepat waktu untuk menghindari denda.</p>
                    <p>Dicetak pada: {{ date('d M Y H:i') }}</p>
                </div>
            </div>

            <!-- Tombol Aksi di Luar Area Cetak -->
            <div class="mt-6 flex justify-end gap-4">
                <button onclick="window.print()" class="bg-gray-800 text-white font-bold py-3 px-6 rounded-xl hover:bg-gray-900 transition-all flex items-center gap-2 shadow-sm">
                    <span class="material-symbols-outlined">print</span> Cetak Struk
                </button>
            </div>

        </div>
    </div>

    <!-- Sedikit CSS Tambahan untuk Print -->
    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            #print-area, #print-area * {
                visibility: visible;
            }
            #print-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                border: none;
                box-shadow: none;
            }
        }
    </style>
</x-app-layout>