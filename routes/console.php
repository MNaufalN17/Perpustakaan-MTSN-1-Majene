<?php

use App\Models\Loan;
use App\Models\LoanItem;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('loans:sync-overdue', function () {
    $today = today()->format('Y-m-d');

    $loanCount = Loan::where('status', 'aktif')
        ->whereDate('due_date', '<', $today)
        ->update([
            'status' => 'terlambat',
        ]);

    $itemCount = LoanItem::where('status', 'dipinjam')
        ->whereHas('loan', function ($query) {
            $query->where('status', 'terlambat');
        })
        ->update([
            'status' => 'terlambat',
        ]);

    $this->info($loanCount . ' transaksi dan ' . $itemCount . ' item disinkronkan menjadi terlambat.');
})->purpose('Sinkronkan status peminjaman yang melewati batas kembali.');

Schedule::command('loans:sync-overdue')->dailyAt('00:05');
