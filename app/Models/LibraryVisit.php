<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LibraryVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'visitor_name',
        'identity_number',
        'visitor_type',
        'student_class_id',
        'visit_purpose',
        'visit_date',
        'check_in_time',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'visit_date' => 'date',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function studentClass()
    {
        return $this->belongsTo(StudentClass::class, 'student_class_id');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
