<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class EmployeeSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Cek apakah sudah ada departemen, jika belum buat 1 agar factory tidak error
        if (Department::count() === 0) {
            Department::create([
                'name' => 'Dinas Umum',
                'code' => 'UMUM'
            ]);
        }

        // Jalankan factory 50 data
        Employee::factory()->count(50)->create();

        $this->command->info('50 Data Pegawai dan User berhasil ditambahkan!');
    }
}