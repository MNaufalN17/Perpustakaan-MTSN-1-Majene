<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('label');
            $table->string('type')->default('text');
            $table->string('group')->default('general');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        DB::table('system_settings')->insert([
            [
                'key' => 'school_name',
                'value' => 'MTs Negeri 1 Majene',
                'label' => 'Nama Sekolah',
                'type' => 'text',
                'group' => 'identity',
                'description' => 'Nama sekolah atau madrasah yang digunakan pada sistem.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'library_name',
                'value' => 'Perpustakaan MTs Negeri 1 Majene',
                'label' => 'Nama Perpustakaan',
                'type' => 'text',
                'group' => 'identity',
                'description' => 'Nama perpustakaan yang tampil pada struk dan laporan.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'school_address',
                'value' => '',
                'label' => 'Alamat Sekolah',
                'type' => 'textarea',
                'group' => 'identity',
                'description' => 'Alamat sekolah atau madrasah.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'head_librarian',
                'value' => '',
                'label' => 'Kepala Perpustakaan',
                'type' => 'text',
                'group' => 'identity',
                'description' => 'Nama kepala perpustakaan.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'loan_days',
                'value' => '3',
                'label' => 'Lama Peminjaman',
                'type' => 'number',
                'group' => 'circulation',
                'description' => 'Jumlah hari peminjaman default.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'max_books_per_loan',
                'value' => '3',
                'label' => 'Maksimal Buku Dipinjam',
                'type' => 'number',
                'group' => 'circulation',
                'description' => 'Jumlah maksimal buku dalam satu transaksi peminjaman.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'fine_per_day',
                'value' => '500',
                'label' => 'Denda Per Hari',
                'type' => 'number',
                'group' => 'circulation',
                'description' => 'Nominal denda keterlambatan per buku per hari.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};