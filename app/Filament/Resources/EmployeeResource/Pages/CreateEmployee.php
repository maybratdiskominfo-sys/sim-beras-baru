<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    /**
     * Langkah 1: Persiapan data sebelum simpan ke database.
     * Mengatasi error "department_id doesn't have a default value".
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 1. Jika Admin Dinas (Ada Tenant aktif), pakai ID Tenant
        if (Filament::getTenant()) {
            $data['department_id'] = Filament::getTenant()->id;
        } 
        // 2. Jika Super Admin dan dia memilih departemen di form, biarkan data apa adanya
        // 3. Jika Super Admin tapi lupa memilih (dan field disembunyikan), beri fallback atau validasi
        elseif ($user->hasRole('super_admin') && !isset($data['department_id'])) {
            // Opsi: Ambil departemen pertama atau lempar error validasi
            $data['department_id'] = \App\Models\Department::first()?->id;
        }

        return $data;
    }

    /**
     * Langkah 2: Setelah Employee tersimpan, buatkan User-nya secara otomatis.
     */
    protected function afterCreate(): void
    {
        $employee = $this->record;

        DB::transaction(function () use ($employee) {
            // 1. Buat akun User
            $user = User::create([
                'name'          => $employee->nama_lengkap,
                'email'         => $employee->email,
                'department_id' => $employee->department_id,
                'password'      => Hash::make('password123'), // Disarankan infokan user untuk ganti password
                'is_active'     => $employee->is_active,
            ]);

            // 2. Hubungkan balik Employee ke User yang baru dibuat (Pastikan kolom user_id ada di tabel employees)
            $employee->update([
                'user_id' => $user->id,
            ]);

            // 3. Berikan Role default (Spatie Permission)
            // Menggunakan role 'panel_user' atau sesuaikan dengan role di database Anda
            $defaultRole = 'user'; 
            if (Role::where('name', $defaultRole)->exists()) {
                $user->assignRole($defaultRole);
            }

            // 4. Hubungkan ke Tenant (Tabel Pivot department_user)
            if ($employee->department_id) {
                $user->departments()->syncWithoutDetaching([$employee->department_id]);
            }
        });
    }

    //Langkah 3: Redirect kembali ke daftar pegawai setelah sukses.
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}