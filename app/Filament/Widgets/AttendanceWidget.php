<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use App\Models\Employee;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Filament\Facades\Filament;


class AttendanceWidget extends BaseWidget
{
    // Refresh widget secara berkala agar waktu server dan status tombol tetap up-to-date
    protected static ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Ambil data employee dengan relasi department
        $employee = Employee::where('user_id', $user->id)->first();

        // 1. Validasi Keberadaan Profil Pegawai
        if (!$employee) {
            return [
                Stat::make('Sistem Presensi', 'Profil Belum Lengkap')
                    ->description('Hubungi Admin untuk sinkronisasi data pegawai.')
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('danger'),
            ];
        }

        // Ambil Tenant secara dinamis (Filament Shield / Multi-tenancy)
        $tenant = Filament::getTenant();
        $tenantId = $tenant?->id;

        // Cari data absen hari ini
        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', Carbon::today())
            ->first();

        // Generate URL dinamis ke halaman AttendanceSession
        // Pastikan nama route sesuai (biasanya filament.admin.pages.attendance-session)
        $sessionUrl = $tenant 
            ? route('filament.admin.pages.attendance-session', ['tenant' => $tenant])
            : '#';

        $stats = [];

        /**
         * LOGIKA UTAMA: INTERAKSI PRESENSI
         */
        if (!$attendance) {
            // KONDISI 1: BELUM ABSEN
            $stats[] = Stat::make('Status: Belum Absen', 'Sesi Masuk')
                ->description('Klik untuk ambil titik GPS Masuk')
                ->descriptionIcon('heroicon-m-map-pin')
                ->color('warning')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-yellow-50 transition duration-200 border-b-4 border-warning-500',
                    'onclick' => "window.location.href='{$sessionUrl}'",
                ]);
        } 
        elseif ($attendance && !$attendance->check_out && in_array($attendance->status, ['Hadir', 'Terlambat'])) {
            // KONDISI 2: SUDAH MASUK, TAMPILKAN TOMBOL PULANG
            $checkInTime = Carbon::parse($attendance->check_in)->format('H:i');
            
            $stats[] = Stat::make('Masuk: ' . $checkInTime, 'Sesi Pulang')
                ->description('Klik untuk absen pulang sekarang')
                ->descriptionIcon('heroicon-m-arrow-right-on-rectangle')
                ->color('danger')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-green-50 animate-pulse transition duration-200 border-b-4 border-success-500',
                    'onclick' => "window.location.href='{$sessionUrl}'",
                ]);
        } 
        elseif ($attendance && !in_array($attendance->status, ['Hadir', 'Terlambat'])) {
            // KONDISI 3: STATUS KHUSUS (IZIN/SAKIT/DINAS LUAR)
            $stats[] = Stat::make('Status: ' . $attendance->status, 'Tercatat')
                ->description($attendance->notes ?: 'Data telah diverifikasi sistem')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('info')
                ->extraAttributes([
                    'class' => 'border-b-4 border-info-500',
                ]);
        }
        else {
            // KONDISI 4: SELESAI (SUDAH CHECK-OUT)
            $checkOutTime = Carbon::parse($attendance->check_out)->format('H:i');
            $stats[] = Stat::make('Presensi Selesai', $checkOutTime)
                ->description('Sampai jumpa di hari kerja berikutnya!')
                ->descriptionIcon('heroicon-m-moon')
                ->color('gray')
                ->extraAttributes([
                    'class' => 'opacity-75 border-b-4 border-gray-400',
                ]);
        }

        /**
         * STAT PENDUKUNG (PROFESIONALISME DATA)
         */
        
        // Slot 2: Waktu Server (Sangat Penting untuk Presensi)
        $stats[] = Stat::make('Waktu Server', now()->format('H:i:s'))
            ->description(now()->isoFormat('dddd, D MMMM Y'))
            ->descriptionIcon('heroicon-m-clock')
            ->color('primary');

        // Slot 3: Informasi Tunjangan / Jatah (Personalisasi)
        $stats[] = Stat::make('Jatah Beras', ($employee->jatah_kg ?? 0) . ' Kg')
            ->description($employee->department->nama_departemen ?? 'Instansi Aktif')
            ->descriptionIcon('heroicon-m-shopping-bag')
            ->color('info');

        return $stats;
    }

    public static function canView(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        // Widget hanya tampil untuk Pegawai di dalam Tenant (Dinas), bukan Super Admin global
        return $user && !$user->hasRole('super_admin') && Filament::getTenant() !== null;
    }
}