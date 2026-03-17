<?php

namespace App\Filament\Resources\AttendanceResource\Pages;

use App\Filament\Resources\AttendanceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class EditAttendance extends EditRecord
{
    protected static string $resource = AttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * Skenario: Menangani Logika Absen Pulang dan Koreksi Admin
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $existingRecord = $this->getRecord();

        // 1. Logika Check-out Otomatis
        // Jika check_out di database masih kosong DAN di form tidak diisi manual
        if (empty($existingRecord->check_out) && empty($data['check_out'])) {
            
            // Jika yang mengedit adalah user biasa, set jam sekarang
            if (!$user->hasAnyRole(['super_admin', 'admin_opd'])) {
                $data['check_out'] = now()->format('H:i:s');
            } 
            // Jika Admin yang mengedit tapi membiarkan kosong, kita biarkan kosong 
            // atau beri default agar tidak null jika memang tujuannya untuk check-out
        }

        // 2. Pastikan department_id tetap konsisten jika ada perubahan employee (oleh Admin)
        if (isset($data['employee_id']) && $data['employee_id'] !== $existingRecord->employee_id) {
            $employee = \App\Models\Employee::find($data['employee_id']);
            if ($employee) {
                $data['department_id'] = $employee->department_id;
            }
        }

        return $data;
    }

    /**
     * Notifikasi sukses yang dinamis
     */
    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Berhasil')
            ->body('Data presensi telah berhasil diperbarui.');
    }

    /**
     * Redirect kembali ke daftar setelah simpan
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}