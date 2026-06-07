<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    public const FINE_PER_DAY = 500;

    public const TYPE_REGULAR = 'regular';
    public const TYPE_CLASS_BULK = 'class_bulk';

    protected $fillable = [
        'loan_code',
        'member_id',
        'student_class_id',
        'loan_date',
        'due_date',
        'return_date',
        'status',
        'loan_type',
        'handled_by',
        'notes',
        'return_notes',
    ];

    protected $casts = [
        'loan_date' => 'date',
        'due_date' => 'date',
        'return_date' => 'date',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function studentClass(): BelongsTo
    {
        return $this->belongsTo(StudentClass::class, 'student_class_id');
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

    public function getIsClassLoanAttribute(): bool
    {
        return ($this->loan_type ?? self::TYPE_REGULAR) === self::TYPE_CLASS_BULK;
    }

    public function getLoanTypeLabelAttribute(): string
    {
        return $this->is_class_loan
            ? 'Perwakilan Kelas'
            : 'Peminjaman Biasa';
    }

    public function getCurrentLateDaysAttribute(): int
    {
        if (! in_array($this->status, ['aktif', 'terlambat'], true)) {
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
        if (! in_array($this->status, ['aktif', 'terlambat'], true)) {
            return 0;
        }

        return $this->current_late_days * $this->finePerDay() * $this->active_items_count;
    }

    public function getTotalFineAmountAttribute(): int
    {
        return $this->stored_fine_amount + $this->running_fine_amount;
    }

    private function finePerDay(): int
    {
        try {
            if (class_exists(SystemSetting::class) && method_exists(SystemSetting::class, 'intValue')) {
                return max(0, (int) SystemSetting::intValue('fine_per_day', self::FINE_PER_DAY));
            }

            if (class_exists(SystemSetting::class) && method_exists(SystemSetting::class, 'getValue')) {
                return max(0, (int) SystemSetting::getValue('fine_per_day', self::FINE_PER_DAY));
            }
        } catch (\Throwable $e) {
            // Fallback agar accessor denda tetap aman walaupun setting bermasalah.
        }

        return self::FINE_PER_DAY;
    }
}   