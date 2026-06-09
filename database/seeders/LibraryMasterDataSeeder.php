<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LibraryMasterDataSeeder extends Seeder
{
    // public function run(): void
    // {
    //     // 1. Suntikkan Data Kategori Dasar
    //     $categories = [
    //         ['name' => 'Buku Teks / Pelajaran', 'description' => 'Buku wajib untuk kegiatan belajar mengajar.'],
    //         ['name' => 'Fiksi & Sastra', 'description' => 'Novel, cerpen, puisi, dan karya imajinatif lainnya.'],
    //         ['name' => 'Agama & Spritualitas', 'description' => 'Buku-buku keagamaan dan pembentukan karakter.'],
    //         ['name' => 'Referensi', 'description' => 'Kamus, ensiklopedia, atlas, dll. Tidak untuk dipinjam.'],
    //         ['name' => 'Sains & Teknologi', 'description' => 'Pengetahuan alam, komputer, dan teknologi.'],
    //     ];

    //     foreach ($categories as $category) {
    //         DB::table('categories')->updateOrInsert(
    //             ['name' => $category['name']],
    //             ['description' => $category['description'], 'created_at' => now(), 'updated_at' => now()]
    //         );
    //     }

    //     // 2. Suntikkan 10 Golongan Utama DDC dengan kolom 'name' dan 'description'
    //     $ddcClasses = [
    //         ['code' => '000', 'name' => 'Karya Umum', 'description' => 'Komputer, Ilmu Informasi & Referensi'],
    //         ['code' => '100', 'name' => 'Filsafat & Psikologi', 'description' => 'Ilmu Filsafat dan Perilaku'],
    //         ['code' => '200', 'name' => 'Agama', 'description' => 'Agama dan Teologi'],
    //         ['code' => '300', 'name' => 'Ilmu Sosial', 'description' => 'Sosiologi, Hukum, dan Pendidikan'],
    //         ['code' => '400', 'name' => 'Bahasa', 'description' => 'Linguistik dan Bahasa'],
    //         ['code' => '500', 'name' => 'Sains', 'description' => 'Sains Murni dan Matematika'],
    //         ['code' => '600', 'name' => 'Teknologi', 'description' => 'Ilmu Terapan (Kedokteran, Pertanian)'],
    //         ['code' => '700', 'name' => 'Kesenian & Olahraga', 'description' => 'Kesenian, Hiburan, dan Rekreasi'],
    //         ['code' => '800', 'name' => 'Sastra', 'description' => 'Kesusastraan, Puisi, Novel, dan Drama'],
    //         ['code' => '900', 'name' => 'Sejarah & Geografi', 'description' => 'Sejarah, Biografi, dan Geografi'],
    //     ];

    //     foreach ($ddcClasses as $ddc) {
    //         DB::table('ddc_classes')->updateOrInsert(
    //             ['code' => $ddc['code']],
    //             [
    //                 'name' => $ddc['name'], 
    //                 'description' => $ddc['description'], 
    //                 'created_at' => now(), 
    //                 'updated_at' => now()
    //             ]
    //         );
    //     }

    //     $this->command->info('Berhasil! Data Kategori dan Kelas DDC (dengan kolom name) berhasil ditambahkan.');
    // }
}