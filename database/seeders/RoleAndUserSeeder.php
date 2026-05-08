<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleAndUserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Membuat Role Pustakawan
        $rolePustakawan = Role::create([
            'name' => 'pustakawan'
        ]);

        $roleKepsek = Role::create([
            'name' => 'kepala_sekolah'
        ]);

        User::create([
            'name' => 'Admin Pustakawan',
            'email' => 'admin@perpus.com',
            'password' => Hash::make('12345678'), // Jangan lupa passwordnya ya!
            'role_id' => $rolePustakawan->id,
            'status' => 'aktif',
        ]);

        User::create([
            'name' => 'Kepala Sekolah',
            'email' => 'kepalasekolahmts1majene@perpus.com',
            'password' => Hash::make('12345678'),
            'role_id' => $roleKepsek->id,
            'status' => 'aktif',
        ]);
    }
}