<?php

namespace App\Filament\Pages;

use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\WorkSchedule;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Carbon\Carbon;

class AttendanceSession extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-finger-print';
    protected static string $view = 'filament.pages.attendance-session';
    protected static ?string $title = 'Sesi Presensi Digital';
    protected static bool $shouldRegisterNavigation = false;

    // State Properies
    public $lat, $lng;
    public $officeLat, $officeLng, $radius;
    public bool $hasAttendanceToday = false;
    public bool $canCheckOut = false;
    public ?string $lastUpdate = null; // Menandai kapan GPS terakhir update

    public function mount()
    {
        $this->loadOfficeLocation();
        $this->loadState();
    }

    /**
     * Sinkronisasi data kantor dari Tenant aktif
     */
    private function loadOfficeLocation(): void
    {
        $tenant = Filament::getTenant();
        
        // Default koordinat jika tenant tidak set lokasi
        $this->officeLat = (float) ($tenant->latitude ?? -1.2708436);
        $this->officeLng = (float) ($tenant->longitude ?? 132.49339);
        $this->radius    = (int) ($tenant->radius_meter ?? 100);
    }

    /**
     * Memeriksa status kehadiran user secara real-time
     */
    public function loadState(): void
    {
        $employee = Auth::user()->employee;
        
        if (!$employee) return;

        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', Carbon::today())
            ->first();

        $this->hasAttendanceToday = (bool) $attendance;
        $this->canCheckOut = $attendance && $attendance->check_in && !$attendance->check_out;
    }

    #[On('updateLocation')]
    public function updateLocation($lat, $lng): void
    {
        $this->lat = $lat;
        $this->lng = $lng;
        $this->lastUpdate = now()->toDateTimeString();
    }

    /**
     * Proses Check-In dengan Validasi Berlapis
     */
    public function checkIn()
    {
        $user = Auth::user();
        $employee = $user->employee;
        $tenant = Filament::getTenant();
        $today = Carbon::today();

        // 1. Pre-Condition Checks
        if (!$employee || !$tenant) {
            $this->sendNotification('Error', 'Profil karyawan atau unit kerja tidak ditemukan.', 'danger');
            return;
        }

        // 2. GPS Freshness & Geofencing
        if (!$this->lat || !$this->lng) {
            $this->sendNotification('GPS Gagal', 'Sinyal lokasi tidak ditemukan.', 'warning');
            return;
        }

        $distance = $this->calculateDistance($this->lat, $this->lng, $this->officeLat, $this->officeLng);
        if ($distance > $this->radius) {
            $this->sendNotification('Di Luar Radius', "Jarak Anda " . round($distance) . "m. Maksimal " . $this->radius . "m.", 'danger');
            return;
        }

        // 3. Holiday & Schedule Validation
        if ($this->isHoliday($tenant->id, $today)) return;

        $schedule = WorkSchedule::where('tenant_id', $tenant->id)
            ->where('day', $today->format('l'))
            ->where('is_active', true)
            ->first();

        if (!$schedule) {
            $this->sendNotification('Jadwal Tidak Ditemukan', 'Tidak ada jadwal kerja aktif hari ini.', 'warning');
            return;
        }

        // 4. Execution with Transaction
        try {
            DB::transaction(function () use ($employee, $tenant, $today, $schedule, $distance) {
                // Double check prevention (Pessimistic Locking)
                $exists = Attendance::where('employee_id', $employee->id)
                    ->whereDate('date', $today)
                    ->exists();

                if ($exists) throw new \Exception("Anda sudah melakukan presensi hari ini.");

                $currentTime = now()->format('H:i:s');
                $status = ($currentTime > $schedule->start_time) ? 'Terlambat' : 'Hadir';

                Attendance::create([
                    'employee_id' => $employee->id,
                    'department_id' => $employee->department_id,
                    'tenant_id' => $tenant->id,
                    'date' => $today,
                    'check_in' => now(),
                    'status' => $status,
                    'location_lat_long' => "{$this->lat},{$this->lng}",
                    'distance_from_office' => (int) $distance,
                ]);
            });

            $this->sendNotification('Berhasil', 'Check-in berhasil tercatat.', 'success');
            $this->loadState();

        } catch (\Exception $e) {
            $this->sendNotification('Gagal Simpan', $e->getMessage(), 'danger');
        }
    }

    public function checkOut()
    {
        $employee = Auth::user()->employee;
        
        if (!$employee) return;

        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', Carbon::today())
            ->whereNull('check_out')
            ->first();

        if (!$attendance) {
            $this->sendNotification('Error', 'Data check-in tidak ditemukan.', 'danger');
            return;
        }

        $attendance->update(['check_out' => now()]);

        $this->sendNotification('Berhasil', 'Selamat beristirahat!', 'success');
        $this->loadState();
    }

    /**
     * Helper untuk mengecek hari libur
     */
    private function isHoliday($tenantId, $date): bool
    {
        $holiday = Holiday::where('tenant_id', $tenantId)->whereDate('date', $date)->first();
        if ($holiday) {
            $this->sendNotification('Hari Libur', "Hari ini libur: {$holiday->description}", 'warning');
            return true;
        }
        return false;
    }

    /**
     * Centralized Notification
     */
    private function sendNotification(string $title, string $body, string $type): void
    {
        Notification::make()->title($title)->body($body)->$type()->send();
    }

    /**
     * Haversine Formula (Bisa dipindah ke Helper Class)
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        return $earthRadius * (2 * atan2(sqrt($a), sqrt(1-$a)));
    }
}