<?php

namespace Database\Seeders;

use App\Models\User;
use BezhanSalleh\FilamentShield\FilamentShieldServiceProvider;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Jalankan Seeder Role (Spatie/Shield) - WAJIB PERTAMA
        $this->call(FilamentShieldServiceProvider::class);

        // 2. Buat Super Admin Utama untuk login pertama kali
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('super_admin');

        // 3. Jalankan Seeder Department (Jika Anda punya DepartmentSeeder)
        // $this->call(DepartmentSeeder::class);

        // 4. Jalankan EmployeeSeeder
        $this->call(EmployeeSeeder::class);
    }
}