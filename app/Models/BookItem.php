<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookItem extends Model
{
    protected $fillable = [
        'book_id', 'item_code', 'classification_code', 'author_code',
        'title_initial', 'copy_number', 'status', 'condition',
        'location', 'acquisition_date'
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}