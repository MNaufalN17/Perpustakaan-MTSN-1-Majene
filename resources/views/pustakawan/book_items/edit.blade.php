@php
    $book = $bookItem->book;
    $ddcCode = $book->ddcClass->code ?? '000';
    $authorCode = $book->author_code ?? $bookItem->author_code ?? 'Pen';
    $titleCode = $book->title_code ?? $bookItem->title_code ?? $bookItem->title_initial ?? 'b';
    $hasActiveLoan = $bookItem->activeLoanItem !== null;
    $initialStatus = $hasActiveLoan ? 'dipinjam' : old('status', $bookItem->status === 'rusak' ? 'nonaktif' : $bookItem->status);
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
                    Manajemen Koleksi
                </p>
                <h2 class="mt-1 text-xl font-bold text-gray-900">
                    Edit Eksemplar Buku
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Identitas buku mengikuti Buku Induk. Status dipinjam dikunci jika transaksi peminjaman masih aktif.
                </p>
            </div>

            <a href="{{ route('book_items.index') }}"
               class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-white px-5 py-3 text-sm font-semibold text-emerald-700 shadow-sm hover:bg-emerald-50">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Kembali
            </a>
        </div>
    </x-slot>

    <div
        class="min-h-screen bg-gradient-to-br from-slate-50 via-emerald-50/40 to-sky-50/40 py-10"
        x-data="{
            ddcCode: @js($ddcCode),
            authorCode: @js($authorCode),
            titleCode: @js($titleCode),
            copyNumber: @js(old('copy_number', $bookItem->copy_number)),
            status: @js($initialStatus),
            condition: @js(old('condition', $bookItem->condition)),
            hasActiveLoan: @js($hasActiveLoan),

            padNumber(number) {
                return String(parseInt(number || 1)).padStart(3, '0');
            },

            automaticCode() {
                return `${this.ddcCode}-${this.authorCode}-${this.titleCode}-${this.padNumber(this.copyNumber)}`;
            },

            normalizeByStatus() {
                if (this.hasActiveLoan) {
                    this.status = 'dipinjam';
                    return;
                }

                if (this.status === 'hilang') {
                    this.condition = 'hilang';
                }
            },

            normalizeByCondition() {
                if (this.condition === 'hilang') {
                    this.status = 'hilang';
                    return;
                }

                if (this.condition !== 'hilang' && this.status === 'hilang') {
                    this.status = this.hasActiveLoan ? 'dipinjam' : 'tersedia';
                }

                if (this.hasActiveLoan) {
                    this.status = 'dipinjam';
                }
            }
        }"
    >
        <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">

            @if ($errors->any())
                <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-red-700 shadow-sm">
                    <p class="text-sm font-bold">Data eksemplar belum bisa disimpan</p>

                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('error_title') || session('error_message') || session('error'))
                <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-red-800 shadow-sm">
                    <p class="text-sm font-bold">
                        {{ session('error_title', 'Data belum bisa disimpan') }}
                    </p>
                    <p class="mt-1 text-sm">
                        {{ session('error_message', session('error')) }}
                    </p>
                    @if(session('error_detail'))
                        <p class="mt-1 text-xs text-red-700">
                            {{ session('error_detail') }}
                        </p>
                    @endif
                </div>
            @endif

            @if($hasActiveLoan)
                <div class="mb-5 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-amber-800 shadow-sm">
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                            <span class="material-symbols-outlined text-[20px]">warning</span>
                        </div>

                        <div>
                            <p class="text-sm font-bold">
                                Eksemplar ini sedang dipinjam
                            </p>

                            <p class="mt-1 text-sm leading-6">
                                Status eksemplar dikunci sebagai dipinjam. Pengembalian harus diproses melalui halaman Peminjaman agar transaksi dan stok tetap sesuai.
                            </p>

                            <p class="mt-1 text-xs leading-5 text-amber-700">
                                Peminjam:
                                <span class="font-bold">
                                    {{ $bookItem->activeLoanItem->loan->member->name ?? '-' }}
                                </span>
                                |
                                Kode transaksi:
                                <span class="font-mono font-bold">
                                    {{ $bookItem->activeLoanItem->loan->loan_code ?? '-' }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/80 shadow-[0_20px_60px_rgba(15,23,42,0.08)] backdrop-blur-xl">
                <div class="bg-gradient-to-r from-emerald-700 to-teal-500 p-6 text-white">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-lg font-bold">
                                Form Edit Eksemplar
                            </h3>
                            <p class="mt-1 text-sm text-emerald-50">
                                Status menunjukkan posisi buku, kondisi menunjukkan keadaan fisik buku.
                            </p>
                        </div>

                        <div class="w-fit rounded-2xl border border-white/20 bg-white/15 px-4 py-3">
                            <p class="text-xs text-emerald-50">Kode Otomatis</p>
                            <p class="mt-1 font-mono text-sm font-bold text-white" x-text="automaticCode()"></p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('book_items.update', $bookItem) }}" class="space-y-8 p-6 md:p-8">
                    @csrf
                    @method('PUT')

                    <section class="rounded-3xl border border-emerald-100 bg-emerald-50/50 p-5 md:p-6">
                        <h4 class="font-bold text-gray-900">Buku Induk</h4>
                        <p class="mt-1 text-sm text-gray-500">
                            Data ini tidak diedit dari halaman eksemplar.
                        </p>

                        <div class="mt-5 grid gap-4 md:grid-cols-4">
                            <div class="rounded-2xl bg-white p-4 shadow-sm">
                                <p class="text-xs font-bold uppercase tracking-[0.12em] text-gray-500">Judul Buku</p>
                                <p class="mt-2 text-sm font-bold text-gray-900">{{ $book->title }}</p>
                            </div>

                            <div class="rounded-2xl bg-white p-4 shadow-sm">
                                <p class="text-xs font-bold uppercase tracking-[0.12em] text-gray-500">DDC</p>
                                <p class="mt-2 font-mono text-sm font-bold text-emerald-700">{{ $ddcCode }}</p>
                            </div>

                            <div class="rounded-2xl bg-white p-4 shadow-sm">
                                <p class="text-xs font-bold uppercase tracking-[0.12em] text-gray-500">Kode Penulis</p>
                                <p class="mt-2 font-mono text-sm font-bold text-gray-900">{{ $authorCode }}</p>
                            </div>

                            <div class="rounded-2xl bg-white p-4 shadow-sm">
                                <p class="text-xs font-bold uppercase tracking-[0.12em] text-gray-500">Kode Judul</p>
                                <p class="mt-2 font-mono text-sm font-bold text-gray-900">{{ $titleCode }}</p>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm md:p-6">
                        <h4 class="font-bold text-gray-900">Data Copy Fisik</h4>
                        <p class="mt-1 text-sm text-gray-500">
                            Nomor copy tidak dapat diubah saat eksemplar masih dipinjam.
                        </p>

                        <div class="mt-5 grid gap-5 md:grid-cols-3">
                            <div>
                                <label for="copy_number" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                    Nomor Copy <span class="text-red-500">*</span>
                                </label>

                                <input
                                    id="copy_number"
                                    name="copy_number"
                                    type="number"
                                    min="1"
                                    x-model.number="copyNumber"
                                    required
                                    @if($hasActiveLoan) readonly @endif
                                    class="mt-2 block w-full rounded-2xl border border-emerald-200 px-4 py-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 {{ $hasActiveLoan ? 'bg-slate-100 cursor-not-allowed' : 'bg-white' }}"
                                >

                                @error('copy_number')
                                    <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="status" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                    Status Eksemplar <span class="text-red-500">*</span>
                                </label>

                                <select
                                    id="status"
                                    name="status"
                                    x-model="status"
                                    @change="normalizeByStatus()"
                                    required
                                    class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                                >
                                    @if($hasActiveLoan)
                                        <option value="dipinjam">Dipinjam</option>
                                    @else
                                        <option value="tersedia">Tersedia</option>
                                        <option value="dipinjam">Dipinjam</option>
                                        <option value="hilang">Hilang</option>
                                        <option value="nonaktif">Nonaktif</option>
                                    @endif
                                </select>

                                @error('status')
                                    <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="condition" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                    Kondisi Fisik <span class="text-red-500">*</span>
                                </label>

                                <select
                                    id="condition"
                                    name="condition"
                                    x-model="condition"
                                    @change="normalizeByCondition()"
                                    required
                                    class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                                >
                                    <option value="baik">Baik</option>
                                    <option value="rusak ringan">Rusak Ringan</option>
                                    <option value="rusak berat">Rusak Berat</option>
                                    <option value="hilang">Hilang</option>
                                </select>

                                @error('condition')
                                    <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-5 rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3">
                            <p class="text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                Kode Eksemplar Otomatis
                            </p>
                            <p class="mt-2 font-mono text-sm font-bold text-gray-900" x-text="automaticCode()"></p>
                        </div>
                    </section>

                    <div class="flex flex-col-reverse gap-3 border-t border-gray-100 pt-6 sm:flex-row sm:justify-end">
                        <a href="{{ route('book_items.index') }}"
                           class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-6 py-3 text-sm font-bold text-gray-600 hover:bg-gray-50">
                            Batal
                        </a>

                        <button
                            type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-6 py-3 text-sm font-bold text-white hover:bg-emerald-800"
                        >
                            <span>Simpan Perubahan</span>
                            <span class="material-symbols-outlined text-[18px]">save</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>