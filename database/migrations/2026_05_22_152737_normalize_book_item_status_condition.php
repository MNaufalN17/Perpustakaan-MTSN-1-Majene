<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('book_items')
            ->where('status', 'rusak')
            ->where('condition', 'baik')
            ->update([
                'status' => 'tersedia',
            ]);

        DB::table('book_items')
            ->where('status', 'rusak')
            ->whereIn('condition', ['rusak ringan', 'rusak berat'])
            ->update([
                'status' => 'nonaktif',
            ]);

        DB::table('book_items')
            ->where('condition', 'hilang')
            ->update([
                'status' => 'hilang',
            ]);

        DB::table('book_items')
            ->where('status', 'hilang')
            ->where('condition', '!=', 'hilang')
            ->update([
                'condition' => 'hilang',
            ]);
    }

    public function down(): void
    {
        //
    }
};