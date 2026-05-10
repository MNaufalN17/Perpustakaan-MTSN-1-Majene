<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-primary leading-tight">
            {{ __('Proses Pengembalian Buku') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200">
                
                <!-- Header Status -->
                <div class="p-6 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-700">
                            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">assignment_return</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Nota: {{ $loan->loan_code }}</h3>
                            <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Peminjam: {{ $loan->member->name }}</p>
                        </div>
                    </div>
                    
                    <!-- Badge Jatuh Tempo -->
                    @php
                        $isOverdue = \Carbon\Carbon::now()->gt(\Carbon\Carbon::parse($loan->due_date)) && $loan->status == 'aktif';
                    @endphp
                    @if($isOverdue)
                        <span class="px-4 py-2 bg-red-100 text-red-700 rounded-full text-xs font-bold border border-red-200 flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">warning</span> TERLAMBAT
                        </span>
                    @endif
                </div>

                <div class="p-8">
                    <form method="POST" action="{{ route('loans.update', $loan->id) }}" class="space-y-8">
                        @csrf
                        @method('PUT')

                        <!-- Daftar Buku yang Dipinjam -->
                        <div class="space-y-4">
                            <label class="flex items-center gap-2 font-bold text-gray-700">
                                <span class="material-symbols-outlined text-primary">format_list_bulleted</span>
                                Daftar Item Buku & Kondisi Kembali
                            </label>

                            <div class="space-y-3">
                                @foreach($loan->loanItems as $index => $item)
                                    <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg flex flex-col md:flex-row md:items-center justify-between gap-4">
                                        <div class="flex items-center gap-3">
                                            <span class="text-sm font-bold text-gray-400">#{{ $index + 1 }}</span>
                                            <div>
                                                <p class="text-sm font-bold text-gray-900">{{ $item->bookItem->book->title }}</p>
                                                <p class="text-xs text-gray-500">Barcode: {{ $item->bookItem->item_code }}</p>
                                            </div>
                                        </div>

                                        <!-- Pilihan Kondisi Kembali -->
                                        <div class="flex gap-2">
                                            <select name="items[{{ $item->id }}][return_condition]" class="text-xs rounded-lg border-gray-300 focus:ring-primary focus:border-primary">
                                                <option value="baik" {{ $item->bookItem->condition == 'baik' ? 'selected' : '' }}>Kembali Baik</option>
                                                <option value="rusak">Kembali Rusak</option>
                                                <option value="hilang">Buku Hilang</option>
                                            </select>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Info Denda (Jika Ada) -->
                        @if($isOverdue)
                            <div class="p-4 bg-orange-50 border border-orange-200 rounded-lg">
                                <div class="flex items-center gap-2 text-orange-800 font-bold mb-1">
                                    <span class="material-symbols-outlined">payments</span>
                                    Informasi Denda
                                </div>
                                <p class="text-sm text-orange-700">
                                    Transaksi ini melewati batas waktu kembali ({{ \Carbon\Carbon::parse($loan->due_date)->format('d M Y') }}). 
                                    Pastikan siswa telah melunasi denda sebelum memproses pengembalian.
                                </p>
                            </div>
                        @endif

                        <div class="pt-6 border-t border-gray-100">
                            <div class="flex gap-4">
                                <a href="{{ route('loans.index') }}" class="w-1/3 text-center bg-gray-100 text-gray-600 font-bold py-4 px-6 rounded-xl hover:bg-gray-200 transition-all">
                                    Batal
                                </a>
                                <button type="submit" class="w-2/3 bg-primary text-white font-bold py-4 px-6 rounded-xl hover:bg-[#107c41] transition-all shadow-lg flex items-center justify-center gap-2">
                                    <span>Selesaikan Pengembalian</span>
                                    <span class="material-symbols-outlined">task_alt</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>