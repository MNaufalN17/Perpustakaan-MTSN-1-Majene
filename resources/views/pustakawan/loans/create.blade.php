<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-primary leading-tight">
            {{ __('Form Peminjaman Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200">
                
                <div class="p-6 bg-gray-50 border-b border-gray-200 flex items-center gap-3">
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center text-green-700">
                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">add_shopping_cart</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Input Transaksi</h3>
                        <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Pastikan data anggota dan buku sudah benar</p>
                    </div>
                </div>

                <div class="p-8">
                    <form method="POST" action="{{ route('loans.store') }}" class="space-y-8">
                        @csrf

                        <div class="space-y-4">
                            <label class="flex items-center gap-2 font-bold text-gray-700">
                                <span class="material-symbols-outlined text-primary">person</span>
                                Pilih Anggota / Siswa
                            </label>
                            <select name="member_id" required class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-primary focus:border-primary">
                                <option value="">-- Cari Nama atau Kode Anggota --</option>
                                @foreach($members as $member)
                                    <option value="{{ $member->id }}">{{ $member->member_code }} - {{ $member->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <hr class="border-gray-100">

                        <div class="space-y-4">
                            <label class="flex items-center gap-2 font-bold text-gray-700">
                                <span class="material-symbols-outlined text-primary">menu_book</span>
                                Pilih Buku yang Dipinjam (Maksimal 3)
                            </label>
                            
                            <div class="grid grid-cols-1 gap-4">
                                <select name="book_item_ids[]" required class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-primary focus:border-primary">
                                    <option value="">-- Pilih Buku 1 (Wajib) --</option>
                                    @foreach($bookItems as $item)
                                        <option value="{{ $item->id }}">{{ $item->item_code }} - {{ $item->book->title }} ({{ $item->condition }})</option>
                                    @endforeach
                                </select>

                                <select name="book_item_ids[]" class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-primary focus:border-primary">
                                    <option value="">-- Pilih Buku 2 (Opsional) --</option>
                                    @foreach($bookItems as $item)
                                        <option value="{{ $item->id }}">{{ $item->item_code }} - {{ $item->book->title }}</option>
                                    @endforeach
                                </select>

                                <select name="book_item_ids[]" class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-primary focus:border-primary">
                                    <option value="">-- Pilih Buku 3 (Opsional) --</option>
                                    @foreach($bookItems as $item)
                                        <option value="{{ $item->id }}">{{ $item->item_code }} - {{ $item->book->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-gray-600">Tanggal Pinjam</label>
                                <input type="text" value="{{ date('d M Y') }}" disabled class="w-full bg-gray-50 border-gray-200 rounded-lg text-gray-500">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-gray-600 font-bold text-primary">Batas Kembali (7 Hari)</label>
                                <input type="text" value="{{ date('d M Y', strtotime('+7 days')) }}" disabled class="w-full bg-green-50 border-green-200 rounded-lg text-green-700 font-bold">
                            </div>
                        </div>

                        <div class="pt-6">
                            <button type="submit" class="w-full bg-primary text-white font-bold py-4 px-6 rounded-xl hover:bg-[#107c41] transition-all shadow-lg flex items-center justify-center gap-2">
                                <span>Proses Peminjaman</span>
                                <span class="material-symbols-outlined">send</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>