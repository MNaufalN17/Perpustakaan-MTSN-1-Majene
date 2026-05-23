<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BookItem extends Model
{
    protected $fillable = [
        'book_id',
        'item_code',
        'classification_code',
        'author_code',
        'title_code',
        'title_initial',
        'copy_number',
        'status',
        'condition',
        'location',
        'acquisition_date',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function loanItems(): HasMany
    {
        return $this->hasMany(LoanItem::class);
    }

    public function activeLoanItems(): HasMany
    {
        return $this->hasMany(LoanItem::class)
            ->whereIn('status', ['dipinjam', 'terlambat'])
            ->whereHas('loan', function ($query) {
                $query->whereIn('status', ['aktif', 'terlambat']);
            });
    }

    public function activeLoanItem(): HasOne
    {
        return $this->hasOne(LoanItem::class)
            ->whereIn('status', ['dipinjam', 'terlambat'])
            ->whereHas('loan', function ($query) {
                $query->whereIn('status', ['aktif', 'terlambat']);
            });
    }

    public function hasActiveLoan(): bool
    {
        return $this->activeLoanItems()->exists();
    }
}