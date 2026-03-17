<?php

namespace App\Filament\Resources\AttendanceResource\Pages;

use App\Filament\Resources\AttendanceResource;
use App\Models\Employee;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;

class CreateAttendance extends CreateRecord
{
    protected static string $resource = AttendanceResource::class;

    /**
     * LOGIKA VALIDASI SEBELUM DATA DISIMPAN
     */
    protected function beforeCreate(): void
    {
        $data = $this->data;
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 1. Lewati validasi geofencing jika status bukan 'Hadir' (Izin/Sakit/Dinas Luar)
        // atau jika penginput adalah Admin (Bypass untuk input manual)
        if ($user->hasAnyRole(['super_admin', 'admin_opd']) || ($data['status'] ?? 'Hadir') !== 'Hadir') {
            return;
        }

        // 2. Identifikasi Pegawai (User biasa yang absen mandiri)
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            Notification::make()
                ->danger()
                ->title('Akses Ditolak')
                ->body('Akun Anda tidak terhubung dengan data Pegawai.')
                ->send();
            throw new Halt();
        }

        $dept = $employee->department;

        // 3. Jika lokasi kantor belum di-setting, izinkan absen tapi berikan peringatan di log
        if (!$dept || !$dept->latitude || !$dept->longitude) {
            return; 
        }

        // 4. Validasi keberadaan koordinat dari device
        if (empty($data['location_lat_long'])) {
            Notification::make()
                ->warning()
                ->title('Lokasi Tidak Terdeteksi')
                ->body('Gagal mendapatkan koordinat GPS. Pastikan izin lokasi/GPS pada browser Anda aktif.')
                ->send();
            throw new Halt();
        }

        // 5. Hitung Jarak
        [$latPegawai, $lngPegawai] = explode(',', $data['location_lat_long']);
        
        $distanceInMeters = $this->calculateDistanceInMeters(
            (float) $latPegawai, (float) $lngPegawai, 
            (float) $dept->latitude, (float) $dept->longitude
        );

        // 6. Validasi Radius (Default 100m jika tidak diisi di setting department)
        $allowedRadius = $dept->radius_meter ?? 100;

        if ($distanceInMeters > $allowedRadius) {
            Notification::make()
                ->danger()
                ->title('Di Luar Jangkauan Kantor')
                ->body("Jarak Anda: " . round($distanceInMeters) . "m. Batas maksimal yang diizinkan adalah {$allowedRadius}m.")
                ->persistent()
                ->send();

            throw new Halt();
        }
    }

    /**
     * MODIFIKASI DATA SEBELUM DISIMPAN KE DATABASE
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Ambil employee_id dari form (jika diisi admin) atau dari user login (jika user biasa)
        $employeeId = $data['employee_id'] ?? Employee::where('user_id', $user->id)->first()?->id;
        $employee = Employee::find($employeeId);

        if ($employee) {
            $data['employee_id'] = $employee->id;
            $data['department_id'] = $employee->department_id;
            
            // Set default waktu jika kosong
            $data['date'] = $data['date'] ?? now()->format('Y-m-d');
            $data['check_in'] = $data['check_in'] ?? now()->format('H:i:s');
            
            // Logic status otomatis jika penginput adalah user biasa (Status default 'Hadir')
            if (!$user->hasAnyRole(['super_admin', 'admin_opd']) && $data['status'] === 'Hadir') {
                // Contoh batas jam 08:00 (Sesuaikan dengan kebijakan OPD Anda)
                if (now()->format('H:i') > '08:00') {
                    $data['status'] = 'Hadir'; // Tetap hadir, tapi di logic lain/accessor dihitung terlambat
                    // Atau ubah status jadi 'Terlambat' jika ingin status eksplisit:
                    // $data['status'] = 'Terlambat';
                }
            }
        }

        return $data;
    }

    /**
     * Fungsi Helper Kalkulasi Jarak (Haversine Formula)
     */
    private function calculateDistanceInMeters($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371000; // Radius bumi dalam meter
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Presensi Berhasil')
            ->body('Data kehadiran telah berhasil disimpan dalam sistem.');
    }

        //Langkah 3: Redirect kembali ke daftar pegawai setelah sukses.
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}