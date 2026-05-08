<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\BookItem;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon; // Pustaka bawaan Laravel untuk memanipulasi tanggal

class LoanController extends Controller
{
    /**
     * Menampilkan riwayat dan daftar transaksi peminjaman.
     */
    public function index()
    {
        // Mengambil transaksi beserta nama peminjam dan pustakawan yang melayani
        $loans = Loan::with(['member', 'handler'])->latest()->get();
        return view('pustakawan.loans.index', compact('loans'));
    }

    /**
     * Menampilkan form untuk transaksi peminjaman baru.
     */
    public function create()
    {
        // Hanya tampilkan anggota yang aktif
        $members = Member::where('status', 'aktif')->get();
        
        // Hanya tampilkan fisik buku yang statusnya 'tersedia'
        $bookItems = BookItem::where('status', 'tersedia')->with('book')->get();

        return view('pustakawan.loans.create', compact('members', 'bookItems'));
    }

    /**
     * Menyimpan transaksi peminjaman baru.
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'member_id' => 'required|exists:members,id',
            'book_item_ids' => 'required|array|min:1|max:3', // Aturan: Maksimal 3 buku
            'book_item_ids.*' => 'exists:book_items,id'
        ]);

        // 2. Validasi Aturan: Anggota tidak boleh punya pinjaman yang belum selesai
        $hasActiveLoan = Loan::where('member_id', $request->member_id)
                             ->where('status', 'aktif')
                             ->exists();
        
        if ($hasActiveLoan) {
            return back()->withErrors(['member_id' => 'Anggota ini masih memiliki pinjaman aktif yang belum dikembalikan.']);
        }

        // 3. Buat Nota Peminjaman Utama
        $loan = Loan::create([
            'loan_code' => 'TRX-' . date('YmdHis'), // Membuat kode otomatis berdasarkan tanggal & jam
            'member_id' => $request->member_id,
            'loan_date' => Carbon::now()->toDateString(),
            'due_date' => Carbon::now()->addDays(3)->toDateString(), // Jatuh tempo otomatis +3 hari
            'status' => 'aktif',
            'handled_by' => Auth::id(), // Mencatat Pustakawan yang sedang login
        ]);

        // 4. Catat Detail Buku yang Dipinjam
        foreach ($request->book_item_ids as $bookItemId) {
            LoanItem::create([
                'loan_id' => $loan->id,
                'book_item_id' => $bookItemId,
                'status' => 'dipinjam'
            ]);

            // 5. Ubah status fisik buku menjadi 'dipinjam' agar tidak bisa dipinjam orang lain
            BookItem::where('id', $bookItemId)->update(['status' => 'dipinjam']);
        }

        return redirect()->route('loans.index')->with('success', 'Transaksi peminjaman berhasil disimpan!');
    }

    /**
     * Menampilkan detail satu transaksi peminjaman.
     */
    public function show(Loan $loan)
    {
        $loan->load(['member', 'handler', 'loanItems.bookItem.book']);
        return view('pustakawan.loans.show', compact('loan'));
    }

    /**
     * Menampilkan form untuk proses PENGEMBALIAN buku.
     */
    public function edit(Loan $loan)
    {
        $loan->load(['member', 'loanItems.bookItem.book']);
        return view('pustakawan.loans.edit', compact('loan'));
    }

    /**
     * Memproses Pengembalian Buku dan Denda.
     */
    public function update(Request $request, Loan $loan)
    {
        // Validasi kondisi buku saat dikembalikan (dikirim sebagai array karena bisa lebih dari 1 buku)
        $request->validate([
            'return_conditions' => 'required|array',
            'return_conditions.*' => 'required|in:baik,rusak ringan,rusak berat,hilang'
        ]);

        $today = Carbon::now();
        $dueDate = Carbon::parse($loan->due_date);
        
        // Menghitung selisih hari jika hari ini lebih besar dari tanggal jatuh tempo
        $lateDays = $today->greaterThan($dueDate) ? $today->diffInDays($dueDate) : 0;
        $finePerDay = 500; // Denda Rp500 per hari

        // Proses setiap buku yang ada di nota peminjaman
        foreach ($loan->loanItems as $item) {
            // Ambil input kondisi dari form frontend
            $condition = $request->return_conditions[$item->id] ?? 'baik';
            
            // Tentukan status akhir fisik buku
            $statusBuku = ($condition == 'baik') ? 'tersedia' : (($condition == 'hilang') ? 'hilang' : 'rusak');

            // Update catatan di detail pinjaman
            $item->update([
                'return_date' => $today->toDateString(),
                'late_days' => $lateDays,
                'fine_amount' => $lateDays * $finePerDay,
                'return_condition' => $condition,
                'status' => 'dikembalikan'
            ]);

            // Update status fisik buku di rak
            $item->bookItem->update([
                'status' => $statusBuku, 
                'condition' => $condition
            ]);
        }

        // Tutup nota peminjaman menjadi selesai
        $loan->update(['status' => 'selesai']);

        return redirect()->route('loans.index')->with('success', 'Buku berhasil dikembalikan dan data telah diperbarui!');
    }

    /**
     * Menghapus riwayat transaksi.
     */
    public function destroy(Loan $loan)
    {
        $loan->delete();
        return redirect()->route('loans.index')->with('success', 'Riwayat peminjaman dihapus!');
    }
}