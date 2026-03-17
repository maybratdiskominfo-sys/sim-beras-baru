<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Employee extends Model
{
    use HasFactory, Notifiable;

    /**
     * Kolom yang dapat diisi melalui mass assignment.
     * Pastikan 'user_id' ada di sini agar Observer bisa mengisinya otomatis.
     */
    protected $fillable = [
        'department_id',
        'user_id',
        'nip',
        'nama_lengkap',
        'email',
        'nomor_hp',
        'position',
        'golongan',
        'jatah_kg',
        'status_pegawai',
        'is_active',
    ];

    /**
     * Casting atribut.
     * Menjamin 'is_active' selalu terbaca sebagai true/false di kode.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'jatah_kg' => 'decimal:1',
    ];

    /**
     * Relasi ke User (Akun Login).
     * Terhubung melalui foreign key 'user_id' di tabel employees.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke Department (OPD).
     * Digunakan untuk pembatasan data (tenancy) per kantor.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    /**
     * Relasi ke Riwayat Presensi.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Helper: Opsi Jabatan untuk Form Filament.
     */
    public static function getPositionOptions(): array
    {
        return [
            'Kepala Dinas' => 'Kepala Dinas',
            'Sekretaris' => 'Sekretaris',
            'Kepala Bidang' => 'Kepala Bidang',
            'Kepala Seksi' => 'Kepala Seksi',
            // 'Staf Ahli' => 'Staf Ahli',
            // 'Staf Administrasi' => 'Staf Administrasi',
            // 'Fungsional' => 'Fungsional',
            'Staf' => 'Staf',
            'Lainnya' => 'Lainnya',
        ];
    }

    /**
     * Helper: Opsi Golongan untuk Form Filament.
     */
    public static function getGolonganOptions(): array
    {
        return [
            'IV/e' => 'IV/e - Pembina Utama',
            'IV/d' => 'IV/d - Pembina Utama Madya',
            'IV/c' => 'IV/c - Pembina Utama Muda',
            'IV/b' => 'IV/b - Pembina Tingkat I',
            'IV/a' => 'IV/a - Pembina',
            'III/d' => 'III/d - Penata Tingkat I',
            'III/c' => 'III/c - Penata',
            'III/b' => 'III/b - Penata Muda Tingkat I',
            'III/a' => 'III/a - Penata Muda',
            'II/d' => 'II/d - Pengatur Tingkat I',
            'II/c' => 'II/c - Pengatur',
            'II/b' => 'II/b - Pengatur Muda Tingkat I',
            'II/a' => 'II/a - Pengatur Muda',
            'HONDA' => 'Honor Daerah',
        ];
    }
}