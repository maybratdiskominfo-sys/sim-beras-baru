<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class RiceDistribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'employee_id',
        'amount_kg',
        'tahap',
        'month',
        'year',
        'taken_at',
        'receiver_name',
        'user_id'
    ];

    protected $casts = [
        'taken_at' => 'datetime',
        'amount_kg' => 'float',
        'month' => 'integer',
        'year' => 'integer',
    ];

    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            $employee = Employee::find($model->employee_id);

            if (!$employee) {
                throw new \Exception("Gagal: Data pegawai tidak ditemukan.");
            }

            // Automasi Data: Ambil dari Auth dan Relasi Employee
            $model->user_id = Auth::id();
            $model->department_id = $employee->department_id;

            // Default periode jika tidak diisi di form
            $model->month = $model->month ?? now()->month;
            $model->year = $model->year ?? now()->year;
            $model->taken_at = $model->taken_at ?? now();

            // LOGIKA VALIDASI JATAH
            // Hitung akumulasi pengambilan dalam periode & tahap yang sama
            $sudahDiambil = self::where('employee_id', $model->employee_id)
                ->where('tahap', $model->tahap)
                ->where('month', $model->month)
                ->where('year', $model->year)
                ->sum('amount_kg');

            $jatahMaksimal = (float) $employee->jatah_kg;
            $sisaJatah = $jatahMaksimal - (float) $sudahDiambil;

            // Jika input melebihi sisa jatah
            if ((float) $model->amount_kg > $sisaJatah) {
                // Memberikan respon error yang cantik di UI Filament
                throw ValidationException::withMessages([
                    'amount_kg' => "Jatah {$model->tahap} bulan {$model->month} sisa {$sisaJatah} Kg. (Total jatah: {$jatahMaksimal} Kg)",
                ]);
            }
        });
    }
}