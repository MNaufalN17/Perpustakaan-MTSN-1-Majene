# AGENTS.md

## Deskripsi Project
Ini adalah sistem perpustakaan sekolah untuk MTSN 1 Majene berbasis Laravel.
Website memiliki beberapa role, seperti admin/pustakawan, guru, siswa, dan anggota perpustakaan.

## Aturan Umum
- Jangan mengubah fitur yang sudah berjalan tanpa alasan jelas.
- Jangan menghapus file, route, migration, controller, atau view tanpa izin.
- Utamakan perbaikan bug kecil dan aman.
- Setiap perubahan harus tetap cocok dengan struktur Laravel yang sudah ada.
- Gunakan bahasa Indonesia untuk label, pesan, dan tampilan website.
- Jangan membuat desain terlalu ramai.
- Navigasi harus sederhana, rapi, dan mudah digunakan.

## Aturan Coding
- Gunakan Laravel Blade untuk tampilan.
- Gunakan Tailwind CSS jika project sudah menggunakannya.
- Jangan mengganti framework frontend tanpa izin.
- Jika memperbaiki file Blade, pastikan directive Blade seperti @csrf, @method, @error, @js tetap valid.
- Jika menggunakan Alpine.js, pastikan script tidak merusak sintaks Blade.
- Jangan membuat nama route baru jika route lama sudah tersedia.
- Cek controller, model, migration, dan route sebelum mengubah view.

## Modul Website
- Dashboard
- Data anggota/member
- Data buku/book items
- Peminjaman/loans
- Pengembalian
- Login dan autentikasi
- Validasi form

## Prioritas Saat Memperbaiki Bug
1. Cari penyebab error.
2. Perbaiki bagian yang bermasalah saja.
3. Jangan ubah fitur lain.
4. Jelaskan file apa saja yang diubah.
5. Berikan cara mengetes hasilnya.

## Perintah Testing
Setelah perubahan, sarankan menjalankan:
```bash
php artisan view:clear
php artisan route:clear
php artisan cache:clear
php artisan serve