<?php

namespace App\Observers;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class EmployeeObserver
{
    /**
     * Handle the Employee "created" event.
     * Otomatis membuat User baru dan mengisi tabel pivot department_user.
     */
    public function created(Employee $employee): void
    {
        // 1. Buat akun User secara otomatis
        $user = User::create([
            'name'          => $employee->nama_lengkap,
            'email'         => $employee->email,
            'password'      => Hash::make($employee->nip), // Password default adalah NIP
            'department_id' => $employee->department_id,   // Set department_id utama
        ]);

        // 2. Isi tabel pivot department_user agar muncul di database Laragon Anda
        // Pastikan di Model User sudah ada relasi: public function departments()
        if (method_exists($user, 'departments')) {
            $user->departments()->syncWithoutDetaching([$employee->department_id]);
        }

        // 3. Hubungkan User ID ke data Employee tanpa memicu event 'updated' kembali
        $employee->updateQuietly([
            'user_id' => $user->id,
        ]);

        // 4. Jika status awal sudah aktif (langsung dicentang), berikan Role
        if ($employee->is_active) {
            $user->assignRole('user');
            $this->clearPermissionCache();
        }
    }

    /**
     * Handle the Employee "updated" event.
     * Sinkronisasi Role dan data profil User.
     */
    public function updated(Employee $employee): void
    {
        $user = $employee->user;

        if ($user) {
            // A. Sinkronisasi Role berdasarkan status aktif/nonaktif
            if ($employee->isDirty('is_active')) {
                if ($employee->is_active) {
                    $user->assignRole('user');
                } else {
                    $user->removeRole('user');
                }
                $this->clearPermissionCache();
            }

            // B. Jika Nama, Email, atau Department berubah, update juga di User
            if ($employee->isDirty(['nama_lengkap', 'email', 'department_id'])) {
                $user->update([
                    'name'          => $employee->nama_lengkap,
                    'email'         => $employee->email,
                    'department_id' => $employee->department_id,
                ]);

                // Update juga di tabel pivot jika department berubah
                if ($employee->isDirty('department_id')) {
                    $user->departments()->sync([$employee->department_id]);
                }
            }
        }
    }

    /**
     * Handle the Employee "deleted" event.
     */
    public function deleted(Employee $employee): void
    {
        // Hapus user dan lepaskan relasi di pivot table
        if ($user = $employee->user) {
            $user->departments()->detach();
            $user->delete();
        }
    }

    /**
     * Handle the Employee "forceDeleted" event.
     */
    public function forceDeleted(Employee $employee): void
    {
        if ($user = $employee->user) {
            $user->departments()->detach();
            $user->forceDelete();
        }
    }

    /**
     * Bersihkan cache permission Spatie.
     */
    protected function clearPermissionCache(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}