<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Department extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'code',
        'alamat_kantor',
        'latitude',
        'longitude',
        'radius_meter',
        'logo_kiri',
        'nama_kadin',
        'nip_kadin',
        'nama_petugas',
        'nip_petugas',
    ];

    protected $casts = [
        'latitude' => 'double',
        'longitude' => 'double',
        'radius_meter' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($department) {
            if (empty($department->slug)) {
                $department->slug = static::generateUniqueSlug($department->name);
            }
        });

        static::updating(function ($department) {
            if ($department->isDirty('name')) {
                $department->slug = static::generateUniqueSlug($department->name);
            }
        });
    }

    /**
     * Helper untuk membuat slug unik
     */
    private static function generateUniqueSlug(string $name): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        return $slug;
    }

    // --- RELASI UNTUK FILAMENT TENANCY (WAJIB ADA) ---

    /**
     * Relasi ke Postingan Berita
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'department_id');
    }

    /**
     * Relasi ke Jam Kerja (WorkSchedules)
     */
    public function workSchedules(): HasMany
    {
        return $this->hasMany(WorkSchedule::class, 'tenant_id');
    }

    /**
     * Relasi ke Hari Libur (Holidays)
     */
    public function holidays(): HasMany
    {
        return $this->hasMany(Holiday::class, 'tenant_id');
    }

    // --- RELASI FUNGSIONAL LAINNYA ---

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'department_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'department_user')
                    ->withTimestamps();
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'department_id');
    }

    public function riceStocks(): HasMany
    {
        return $this->hasMany(RiceStock::class, 'department_id');
    }

    public function riceDistributions(): HasMany
    {
        return $this->hasMany(RiceDistribution::class, 'department_id');
    }
}