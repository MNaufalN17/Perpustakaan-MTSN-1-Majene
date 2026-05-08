<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    protected $fillable = [
        'title', 'author', 'publisher', 'publication_year',
        'category_id', 'ddc_class_id', 'price', 'is_borrowable', 'description'
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function ddcClass(): BelongsTo
    {
        return $this->belongsTo(DdcClass::class);
    }

    public function bookItems(): HasMany
    {
        return $this->hasMany(BookItem::class);
    }
}