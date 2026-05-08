<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\StudentClass;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    /**
     * Menampilkan daftar semua anggota perpustakaan.
     */
    public function index()
    {
        // Mengambil semua data anggota beserta relasi kelasnya, diurutkan dari yang terbaru
        $members = Member::with('studentClass')->latest()->get();
        
        // Kita asumsikan nama file view-nya nanti adalah 'pustakawan.members.index'
        return view('pustakawan.members.index', compact('members'));
    }

    /**
     * Menampilkan form untuk menambah anggota baru.
     */
    public function create()
    {
        // Mengambil data kelas untuk ditampilkan di pilihan dropdown (jika yang mendaftar adalah siswa)
        $classes = StudentClass::all();
        
        return view('pustakawan.members.create', compact('classes'));
    }

    /**
     * Menyimpan data anggota baru ke dalam database.
     */
    public function store(Request $request)
    {
        // 1. Validasi data yang dikirim dari form
        $validatedData = $request->validate([
            'member_code' => 'required|unique:members,member_code',
            'nis_nip' => 'required|unique:members,nis_nip',
            'name' => 'required|string|max:255',
            'member_type' => 'required|in:siswa,guru',
            'gender' => 'required|in:laki-laki,perempuan',
            'student_class_id' => 'nullable|exists:classes,id', // Kosong jika guru
            'phone' => 'nullable|string|max:15',
        ]);

        // 2. Simpan data ke database
        Member::create($validatedData);

        // 3. Arahkan kembali ke halaman daftar anggota dengan pesan sukses
        return redirect()->route('members.index')->with('success', 'Data anggota berhasil ditambahkan!');
    }

    /**
     * Menampilkan detail satu anggota (opsional, untuk melihat riwayat dll).
     */
    public function show(Member $member)
    {
        return view('pustakawan.members.show', compact('member'));
    }

    /**
     * Menampilkan form untuk mengedit data anggota.
     */
    public function edit(Member $member)
    {
        $classes = StudentClass::all();
        return view('pustakawan.members.edit', compact('member', 'classes'));
    }

    /**
     * Memperbarui data anggota di database.
     */
    public function update(Request $request, Member $member)
    {
        // 1. Validasi data (Pengecualian unique untuk ID member yang sedang diedit agar tidak error)
        $validatedData = $request->validate([
            'member_code' => 'required|unique:members,member_code,' . $member->id,
            'nis_nip' => 'required|unique:members,nis_nip,' . $member->id,
            'name' => 'required|string|max:255',
            'member_type' => 'required|in:siswa,guru',
            'gender' => 'required|in:laki-laki,perempuan',
            'student_class_id' => 'nullable|exists:classes,id',
            'phone' => 'nullable|string|max:15',
            'status' => 'required|in:aktif,nonaktif', // Status bisa diubah saat edit
        ]);

        // 2. Update data
        $member->update($validatedData);

        // 3. Arahkan kembali dengan pesan sukses
        return redirect()->route('members.index')->with('success', 'Data anggota berhasil diperbarui!');
    }

    /**
     * Menghapus (atau menonaktifkan) data anggota.
     */
    public function destroy(Member $member)
    {
        // Sebagai praktik terbaik perpustakaan, kita mungkin hanya mengubah statusnya, bukan menghapus datanya (opsional). 
        // Tapi untuk tahap ini, kita gunakan fitur hapus permanen agar sederhana.
        $member->delete();

        return redirect()->route('members.index')->with('success', 'Data anggota berhasil dihapus!');
    }
}