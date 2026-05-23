<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    public const FINE_PER_DAY = 500;

    protected $fillable = [
        'loan_code',
        'member_id',
        'loan_date',
        'due_date',
        'status',
        'handled_by',
        'notes',
    ];

    protected $casts = [
        'loan_date' => 'date',
        'due_date' => 'date',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function loanItems(): HasMany
    {
        return $this->hasMany(LoanItem::class);
    }

    public function activeLoanItems(): HasMany
    {
        return $this->hasMany(LoanItem::class)
            ->whereIn('status', ['dipinjam', 'terlambat']);
    }

    public function getCurrentLateDaysAttribute(): int
    {
        if (!in_array($this->status, ['aktif', 'terlambat'])) {
            return 0;
        }

        $today = Carbon::now()->startOfDay();
        $dueDate = Carbon::parse($this->due_date)->startOfDay();

        return $today->gt($dueDate)
            ? (int) $dueDate->diffInDays($today)
            : 0;
    }

    public function getActiveItemsCountAttribute(): int
    {
        if ($this->relationLoaded('loanItems')) {
            return $this->loanItems
                ->whereIn('status', ['dipinjam', 'terlambat'])
                ->count();
        }

        return $this->loanItems()
            ->whereIn('status', ['dipinjam', 'terlambat'])
            ->count();
    }

    public function getStoredFineAmountAttribute(): int
    {
        if ($this->relationLoaded('loanItems')) {
            return (int) $this->loanItems->sum('fine_amount');
        }

        return (int) $this->loanItems()->sum('fine_amount');
    }

    public function getRunningFineAmountAttribute(): int
    {
        if (!in_array($this->status, ['aktif', 'terlambat'])) {
            return 0;
        }

        return $this->current_late_days * self::FINE_PER_DAY * $this->active_items_count;
    }

    public function getTotalFineAmountAttribute(): int
    {
        return $this->stored_fine_amount + $this->running_fine_amount;
    }
}