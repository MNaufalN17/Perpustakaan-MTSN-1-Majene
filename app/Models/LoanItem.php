<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LoanItem extends Model
{
    protected $fillable = [
        'loan_id', 'book_item_id', 'return_date', 'renewal_count', 
        'last_renewed_at', 'late_days', 'fine_amount', 
        'return_condition', 'status', 'notes'
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function bookItem(): BelongsTo
    {
        return $this->belongsTo(BookItem::class);
    }

    public function finePayment(): HasOne
    {
        return $this->hasOne(FinePayment::class);
    }
}