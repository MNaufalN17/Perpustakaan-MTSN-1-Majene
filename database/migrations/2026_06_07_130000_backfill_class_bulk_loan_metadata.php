<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('loans', 'loan_type')) {
            return;
        }

        $loanIds = DB::table('loans')
            ->join('loan_items', 'loans.id', '=', 'loan_items.loan_id')
            ->join('book_items', 'loan_items.book_item_id', '=', 'book_items.id')
            ->where(function ($query) {
                $query->whereNull('loans.loan_type')
                    ->orWhere('loans.loan_type', 'regular');
            })
            ->select('loans.id')
            ->groupBy('loans.id')
            ->havingRaw('COUNT(loan_items.id) > 1')
            ->havingRaw('COUNT(DISTINCT book_items.book_id) = 1')
            ->pluck('id');

        if ($loanIds->isEmpty()) {
            return;
        }

        $loans = DB::table('loans')
            ->leftJoin('members', 'loans.member_id', '=', 'members.id')
            ->whereIn('loans.id', $loanIds)
            ->select('loans.id', 'loans.notes', 'loans.student_class_id', 'members.student_class_id as member_class_id')
            ->get();

        foreach ($loans as $loan) {
            $payload = [
                'loan_type' => 'class_bulk',
                'updated_at' => now(),
            ];

            if (Schema::hasColumn('loans', 'student_class_id') && empty($loan->student_class_id) && ! empty($loan->member_class_id)) {
                $payload['student_class_id'] = $loan->member_class_id;
            }

            if (Schema::hasColumn('loans', 'notes') && empty($loan->notes)) {
                $payload['notes'] = '[Backfill] Ditandai otomatis sebagai peminjaman kelas lama berdasarkan pola eksemplar.';
            }

            DB::table('loans')
                ->where('id', $loan->id)
                ->update($payload);
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('loans', 'loan_type')) {
            return;
        }

        $payload = [
            'loan_type' => 'regular',
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('loans', 'student_class_id')) {
            $payload['student_class_id'] = null;
        }

        if (Schema::hasColumn('loans', 'notes')) {
            $payload['notes'] = null;
        }

        DB::table('loans')
            ->where('loan_type', 'class_bulk')
            ->where('notes', '[Backfill] Ditandai otomatis sebagai peminjaman kelas lama berdasarkan pola eksemplar.')
            ->update($payload);
    }
};
