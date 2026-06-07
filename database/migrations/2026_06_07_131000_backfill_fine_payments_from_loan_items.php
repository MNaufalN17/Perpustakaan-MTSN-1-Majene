<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('loan_items') || ! Schema::hasTable('fine_payments')) {
            return;
        }

        $loanItems = DB::table('loan_items')
            ->leftJoin('fine_payments', 'loan_items.id', '=', 'fine_payments.loan_item_id')
            ->whereNull('fine_payments.id')
            ->where('loan_items.fine_amount', '>', 0)
            ->select('loan_items.id', 'loan_items.fine_amount')
            ->get();

        foreach ($loanItems as $loanItem) {
            DB::table('fine_payments')->insert([
                'loan_item_id' => $loanItem->id,
                'amount' => $loanItem->fine_amount,
                'payment_date' => null,
                'payment_status' => 'belum dibayar',
                'received_by' => null,
                'notes' => '[Backfill] Tagihan denda dibuat dari riwayat pengembalian.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('fine_payments')) {
            return;
        }

        DB::table('fine_payments')
            ->where('notes', '[Backfill] Tagihan denda dibuat dari riwayat pengembalian.')
            ->delete();
    }
};
