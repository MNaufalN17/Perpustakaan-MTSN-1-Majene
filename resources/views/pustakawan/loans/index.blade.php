<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Data Transaksi Sirkulasi') }}
        </h2>
    </x-slot>

    <div class="py-12 font-sans">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-4 text-green-800 flex items-center gap-2">
                    <span class="material-symbols-outlined">check_circle</span>
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden flex flex-col">
                
                <div class="bg-gray-50 p-6 border-b border-gray-200 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center text-green-700">
                            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">sync_saved_locally</span>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">Riwayat Peminjaman</h3>
                    </div>
                    
                    <a href="{{ route('loans.create') }}" class="bg-[#006130] text-white px-4 py-2 rounded-lg hover:bg-[#107c41] transition-colors flex items-center gap-2 text-sm font-semibold shadow-sm">
                        <span class="material-symbols-outlined text-sm">add</span> Peminjaman Baru
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-600 text-sm border-b border-gray-200">
                                <th class="p-4 font-semibold">Kode Transaksi</th>
                                <th class="p-4 font-semibold">Nama Peminjam</th>
                                <th class="p-4 font-semibold">Tgl Pinjam</th>
                                <th class="p-4 font-semibold">Jatuh Tempo</th>
                                <th class="p-4 font-semibold">Status</th>
                                <th class="p-4 font-semibold text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm text-gray-800">
                            @forelse($loans as $loan)
                                <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                    <td class="p-4 font-medium">{{ $loan->loan_code }}</td>
                                    
                                    <td class="p-4">{{ $loan->member->name }}</td>
                                    
                                    <td class="p-4">{{ \Carbon\Carbon::parse($loan->loan_date)->format('d M Y') }}</td>
                                    
                                    <td class="p-4">
                                        @php
                                            // Cek apakah status masih aktif dan sudah lewat jatuh tempo
                                            $isOverdue = \Carbon\Carbon::now()->startOfDay()->gt(\Carbon\Carbon::parse($loan->due_date)->startOfDay()) && $loan->status == 'aktif';
                                        @endphp
                                        <span class="{{ $isOverdue ? 'text-[#ba1a1a] font-bold flex items-center gap-1' : '' }}">
                                            @if($isOverdue) <span class="material-symbols-outlined text-[16px]">warning</span> @endif
                                            {{ \Carbon\Carbon::parse($loan->due_date)->format('d M Y') }}
                                        </span>
                                    </td>
                                    
                                    <td class="p-4">
                                        @if($loan->status == 'aktif')
                                            <span class="px-3 py-1 bg-yellow-100 text-yellow-800 border border-yellow-200 rounded-full text-xs font-bold tracking-wide">
                                                AKTIF
                                            </span>
                                        @else
                                            <span class="px-3 py-1 bg-gray-100 text-gray-600 border border-gray-200 rounded-full text-xs font-bold tracking-wide">
                                                SELESAI
                                            </span>
                                        @endif
                                    </td>
                                    
                                    <td class="p-4 flex justify-center gap-3">
                                        
                                        <a href="{{ route('loans.show', $loan->id) }}" class="text-gray-500 hover:text-[#006130] transition-colors" title="Lihat Struk">
                                            <span class="material-symbols-outlined">visibility</span>
                                        </a>

                                        @if($loan->status == 'aktif')
                                            <a href="{{ route('loans.edit', $loan->id) }}" class="text-blue-500 hover:text-blue-700 transition-colors" title="Proses Pengembalian">
                                                <span class="material-symbols-outlined">assignment_return</span>
                                            </a>
                                        @endif

                                        <form action="{{ route('loans.destroy', $loan->id) }}" method="POST" onsubmit="return confirm('Perhatian: Yakin ingin menghapus riwayat transaksi ini?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-gray-400 hover:text-[#ba1a1a] transition-colors" title="Hapus Riwayat">
                                                <span class="material-symbols-outlined">delete</span>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-8 text-center flex flex-col items-center justify-center text-gray-500">
                                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                                            <span class="material-symbols-outlined text-3xl">inbox</span>
                                        </div>
                                        <p>Belum ada data transaksi peminjaman.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>