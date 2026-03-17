<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiceStock extends Model
{
    use HasFactory;

    /**
     * Atribut yang dapat diisi massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'department_id',
        'tahap',
        'tanggal_masuk',
        'jumlah_karung',
        'berat_per_karung',
        'total_kg',
        'keterangan',
    ];

    /**
     * Konversi tipe data otomatis.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal_masuk'    => 'date',
        'jumlah_karung'    => 'float',
        'berat_per_karung' => 'float',
        'total_kg'         => 'float',
    ];

    /**
     * Relasi ke model Department (Dinas/Unit).
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Logika otomatisasi saat model berinteraksi dengan database.
     */
    protected static function booted(): void
    {
        static::saving(function (RiceStock $riceStock) {
            // Kalkulasi otomatis: Total = Karung x Berat per Karung
            // Kita pastikan nilai default 0 untuk menghindari error perkalian null
            $jumlah = (float) ($riceStock->jumlah_karung ?? 0);
            $berat  = (float) ($riceStock->berat_per_karung ?? 0);

            $riceStock->total_kg = $jumlah * $berat;
        });
    }
}