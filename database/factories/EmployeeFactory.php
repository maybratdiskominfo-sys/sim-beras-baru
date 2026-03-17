<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'nama_lengkap' => $this->faker->name(),
            'nip' => $this->faker->unique()->numerify('##################'),
            'department_id' => Department::inRandomOrder()->first()?->id,
            'is_active' => true,
            'user_id' => null, 
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Employee $employee) {
            // Buat User terkait
            $user = User::create([
                'name' => $employee->nama_lengkap,
                'email' => $this->faker->unique()->safeEmail(),
                'password' => Hash::make('password'),
                'department_id' => $employee->department_id,
            ]);

            // Berikan role 'user' (Pastikan role ini sudah ada di database)
            $user->assignRole('user');

            // Update user_id ke employee tanpa memicu Observer
            $employee->updateQuietly(['user_id' => $user->id]);
        });
    }
}