<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_code',
        'nis_nip',
        'name',
        'member_type',
        'gender',
        'student_class_id',
        'phone',
        'status',
        'card_image',
    ];

    public function studentClass()
    {
        return $this->belongsTo(StudentClass::class);
    }

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    public function visits()
    {
        return $this->hasMany(LibraryVisit::class);
    }
}
