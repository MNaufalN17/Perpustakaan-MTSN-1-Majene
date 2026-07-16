# Panduan Instalasi dan Penggunaan Sistem Perpustakaan MTSN 1 Majene

Dokumen ini berisi panduan untuk mitra pengembang atau pihak hosting dalam melakukan instalasi, konfigurasi, dan pengujian sistem perpustakaan berbasis Laravel ini.

## 1. Persiapan Awal
Pastikan server atau lokal komputer Anda sudah terinstal:
- PHP >= 8.1
- Composer
- Node.js & NPM
- Database MySQL atau MariaDB

## 2. Langkah Instalasi

1. **Ekstrak atau Clone Project**
   Ekstrak file ZIP project ke dalam folder web server Anda (misal: `htdocs` untuk XAMPP atau `www` untuk Laragon).

2. **Install Dependensi PHP**
   Buka terminal/command prompt di dalam folder project, lalu jalankan:
   ```bash
   composer install
   ```

3. **Install Dependensi Frontend**
   Jalankan perintah berikut untuk menginstal package NPM:
   ```bash
   npm install
   ```

4. **Konfigurasi Environment**
   - Copy file `.env.example` dan ubah namanya menjadi `.env`
   - Buka file `.env` dan atur koneksi database Anda:
     ```env
     DB_CONNECTION=mysql
     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_DATABASE=nama_database_anda
     DB_USERNAME=root
     DB_PASSWORD=password_database_anda
     ```

5. **Generate Application Key**
   ```bash
   php artisan key:generate
   ```

## 3. Migrasi Database dan Seeding (PENTING)

Agar Anda bisa langsung login menggunakan akun yang sudah disediakan (Admin IT, Kepala Sekolah, Pustakawan), Anda **WAJIB** menjalankan migrasi dan seeding database.

Jalankan perintah berikut di terminal:
```bash
php artisan migrate:fresh --seed
```
*(Gunakan `migrate:fresh --seed` jika ingin menghapus semua tabel dan membuat ulang beserta isinya, atau `migrate --seed` untuk instalasi pertama kali).*

Perintah di atas akan membuat semua tabel yang dibutuhkan dan mengisi data awal (seeder), termasuk Role dan Akun Pengguna yang ada di `RoleAndUserSeeder`.

## 4. Build Aset Frontend
Untuk mengkompilasi file CSS (Tailwind) dan JavaScript agar tampilan berjalan dengan baik, jalankan:
```bash
npm run build
```
*(Gunakan `npm run dev` jika Anda masih dalam tahap pengembangan di lokal komputer)*

## 5. Menjalankan Aplikasi (Lokal)
Jika Anda menggunakan server bawaan Laravel, jalankan:
```bash
php artisan serve
```
Akses aplikasi melalui browser di alamat: `http://localhost:8000`

---

## 6. Akses Akun Default (Hasil Seeder)

Setelah Anda menjalankan perintah seeding di atas, Anda bisa **LANGSUNG LOGIN** menggunakan akun-akun berikut ini yang sudah tersimpan otomatis di database:

| Role (Peran) | Nama Akun | Email Login | Password |
| :--- | :--- | :--- | :--- |
| **Admin IT** | Admin IT Sekolah | `it@mtsn1majene.com` | `12345678` |
| **Kepala Sekolah** | Kepala Sekolah | `kepsek@mtsn1majene.com` | `12345678` |
| **Pustakawan** | Staf Pustakawan Utama | `pustakawan@mtsn1majene.com` | `12345678` |

## 7. Catatan untuk Hosting (Production)
Jika Anda mengunggah (hosting) project ini ke cPanel, VPS, atau server production:
1. Pastikan folder root dari domain/subdomain Anda mengarah ke folder `public`.
2. Pada file `.env`, pastikan Anda mengubah pengaturan berikut:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   ```
3. Jalankan perintah optimasi berikut di terminal server Anda untuk mempercepat loading:
   ```bash
   php artisan optimize:clear
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
