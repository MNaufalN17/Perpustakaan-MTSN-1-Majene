<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function roleLabel(): string
    {
        return match ((int) $this->role_id) {
            1 => 'Pustakawan',
            2 => 'Kepala Perpustakaan',
            3 => 'Staff IT Admin',
            default => 'Pengguna Sistem',
        };
    }

    public function isPustakawan(): bool
    {
        return (int) $this->role_id === 1;
    }

    public function isKepalaPerpustakaan(): bool
    {
        return (int) $this->role_id === 2;
    }

    public function isAdmin(): bool
    {
        return (int) $this->role_id === 3;
    }
}
