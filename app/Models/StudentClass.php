<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\Member;

class StudentClass extends Model
{
    // Memberitahu Laravel bahwa model ini menggunakan tabel 'classes'
    protected $table = 'classes'; 

    protected $fillable = [
        'class_name',
        'level',
        'academic_year',
    ];

    // Satu kelas memiliki banyak anggota (siswa)
    public function members(): HasMany
    {
        return $this->hasMany(Member::class, 'student_class_id');
    }
}