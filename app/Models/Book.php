<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'author_code',
        'title_code',
        'publisher',
        'publication_year',
        'price',
        'category_id',
        'ddc_class_id',
        'is_borrowable',
        'description',
    ];

    protected $casts = [
        'is_borrowable' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function ddcClass()
    {
        return $this->belongsTo(DdcClass::class, 'ddc_class_id');
    }

    public function bookItems()
    {
        return $this->hasMany(BookItem::class, 'book_id', 'id');
    }
}