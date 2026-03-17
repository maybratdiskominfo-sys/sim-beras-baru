<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'employee_id',
        'department_id', // Penting untuk Tenancy
        'date',
        'check_in',
        'check_out',
        'status', 
        'location_lat_long',
        'distance_from_office',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'distance_from_office' => 'integer',
    ];

    /**
     * GLOBAL SCOPE: Agar Admin OPD hanya melihat presensi pegawai AKTIF di departemennya.
     * parent::getEloquentQuery() di Resource akan mengikuti ini.
     */
    protected static function booted()
    {
        // Logika Otomatisasi saat simpan data
        static::creating(function ($attendance) {
            // 1. Set Tanggal Default
            if (empty($attendance->date)) {
                $attendance->date = now()->format('Y-m-d');
            }
            
            // 2. Set Check-in Default
            if (empty($attendance->check_in)) {
                $attendance->check_in = now();
            }

            // 3. Ambil Jadwal Kerja berdasarkan tenant (department_id)
            $dayName = Carbon::parse($attendance->date)->format('l');
            $schedule = WorkSchedule::where('tenant_id', $attendance->department_id)
                ->where('day', $dayName)
                ->where('is_active', true)
                ->first();

            // 4. Tentukan status otomatis (Hadir/Terlambat) jika belum diset manual
            if (!$attendance->status) {
                if ($schedule) {
                    $checkInTime = Carbon::parse($attendance->check_in)->format('H:i:s');
                    $attendance->status = ($checkInTime > $schedule->start_time) ? 'Terlambat' : 'Hadir';
                } else {
                    $attendance->status = 'Hadir';
                }
            }
        });
    }

    /**
     * SCOPE: Hanya tampilkan presensi jika pegawainya berstatus AKTIF
     */
    public function scopeActiveEmployee(Builder $query): void
    {
        $query->whereHas('employee', function ($q) {
            $q->where('is_active', true);
        });
    }

    /**
     * ACCESSOR: Menghitung menit keterlambatan
     */
    public function getLateMinutesAttribute(): int
    {
        if (!$this->check_in || $this->status !== 'Terlambat') {
            return 0;
        }

        $dayName = $this->date->format('l');
        $schedule = WorkSchedule::where('tenant_id', $this->department_id)
            ->where('day', $dayName)
            ->first();

        if (!$schedule) return 0;

        $limit = Carbon::parse($this->date->format('Y-m-d') . ' ' . $schedule->start_time);
        $checkIn = Carbon::parse($this->check_in);

        return $checkIn->gt($limit) ? $checkIn->diffInMinutes($limit) : 0;
    }

    /**
     * ACCESSOR: Durasi Jam Kerja
     */
    public function getWorkDurationAttribute(): string
    {
        if ($this->check_in && $this->check_out) {
            $hours = $this->check_in->diffInHours($this->check_out);
            $minutes = $this->check_in->copy()->addHours($hours)->diffInMinutes($this->check_out);
            return "{$hours} Jam {$minutes} Menit";
        }
        return 'Belum Pulang';
    }

    /**
     * HELPER: Validasi Hari Libur & Jadwal
     */
    public static function canPresence($tenantId): array
    {
        $today = now();
        
        $holiday = Holiday::where('tenant_id', $tenantId)
            ->whereDate('date', $today)
            ->first();
            
        if ($holiday) {
            return ['status' => false, 'message' => "Hari ini Libur: {$holiday->description}"];
        }

        $schedule = WorkSchedule::where('tenant_id', $tenantId)
            ->where('day', $today->format('l'))
            ->where('is_active', true)
            ->first();

        if (!$schedule) {
            return ['status' => false, 'message' => "Bukan jadwal kerja aktif."];
        }

        return ['status' => true, 'schedule' => $schedule];
    }

    // --- RELASI ---

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function department(): BelongsTo
    {
        // Pastikan department_id ada untuk filter Multi-tenancy
        return $this->belongsTo(Department::class, 'department_id');
    }

    // --- SCOPES ---

    public function scopeToday($query)
    {
        return $query->whereDate('date', Carbon::today());
    }
}