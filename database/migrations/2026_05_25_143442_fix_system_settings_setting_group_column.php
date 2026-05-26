<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('system_settings')) {
            Schema::create('system_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->string('label');
                $table->string('type')->default('text');
                $table->string('setting_group')->default('general');
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        Schema::table('system_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('system_settings', 'setting_group')) {
                $table->string('setting_group')->default('general')->after('type');
            }
        });

        if (Schema::hasColumn('system_settings', 'group') && Schema::hasColumn('system_settings', 'setting_group')) {
            DB::statement("UPDATE system_settings SET setting_group = `group` WHERE `group` IS NOT NULL AND `group` != ''");
        }

        $settings = [
            [
                'key' => 'school_name',
                'value' => 'MTs Negeri 1 Majene',
                'label' => 'Nama Sekolah',
                'type' => 'text',
                'setting_group' => 'identity',
                'description' => 'Nama sekolah atau madrasah yang digunakan pada sistem.',
            ],
            [
                'key' => 'library_name',
                'value' => 'Perpustakaan MTs Negeri 1 Majene',
                'label' => 'Nama Perpustakaan',
                'type' => 'text',
                'setting_group' => 'identity',
                'description' => 'Nama perpustakaan yang tampil pada struk dan laporan.',
            ],
            [
                'key' => 'school_address',
                'value' => '',
                'label' => 'Alamat Sekolah',
                'type' => 'textarea',
                'setting_group' => 'identity',
                'description' => 'Alamat sekolah atau madrasah.',
            ],
            [
                'key' => 'head_librarian',
                'value' => '',
                'label' => 'Kepala Perpustakaan',
                'type' => 'text',
                'setting_group' => 'identity',
                'description' => 'Nama kepala perpustakaan.',
            ],
            [
                'key' => 'loan_days',
                'value' => '3',
                'label' => 'Lama Peminjaman',
                'type' => 'number',
                'setting_group' => 'circulation',
                'description' => 'Jumlah hari peminjaman default.',
            ],
            [
                'key' => 'max_books_per_loan',
                'value' => '3',
                'label' => 'Maksimal Buku Dipinjam',
                'type' => 'number',
                'setting_group' => 'circulation',
                'description' => 'Jumlah maksimal buku dalam satu transaksi peminjaman.',
            ],
            [
                'key' => 'fine_per_day',
                'value' => '500',
                'label' => 'Denda Per Hari',
                'type' => 'number',
                'setting_group' => 'circulation',
                'description' => 'Nominal denda keterlambatan per buku per hari.',
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('system_settings')->updateOrInsert(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'label' => $setting['label'],
                    'type' => $setting['type'],
                    'setting_group' => $setting['setting_group'],
                    'description' => $setting['description'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            if (Schema::hasColumn('system_settings', 'setting_group')) {
                $table->dropColumn('setting_group');
            }
        });
    }
};