<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ReportSetting extends Model
{
    protected $table = 'report_settings';

    protected $fillable = [
        'logo',
        'nama_pemda',
        'judul_laporan',
        'sub_judul',
        'alamat',
        'jabatan_kiri',
        'nama_kiri',
        'nip_kiri',
        'ttd_kiri',
        'jabatan_kanan',
        'nama_kanan',
        'nip_kanan',
        'ttd_kanan',
        'aktifkan_ttd_digital'
    ];

    /**
     * Casting otomatis agar logika di Blade dan Controller lebih cepat tanpa konversi manual.
     */
    protected $casts = [
        'aktifkan_ttd_digital' => 'boolean',
    ];

    /**
     * Helper untuk mengambil data setting tunggal (Singleton pattern)
     * Ditambahkan caching sederhana untuk mempercepat pemanggilan berulang.
     */
    public static function getValues()
    {
        // Simpan di cache selama 24 jam, otomatis reset jika ada update
        return \Illuminate\Support\Facades\Cache::remember('report_settings_data', 86400, function () {
            return self::first() ?: new self();
        });
    }

    // Tambahkan fungsi boot untuk hapus cache saat data diupdate
    protected static function boot()
    {
        parent::boot();
        static::saved(function () {
            \Illuminate\Support\Facades\Cache::forget('report_settings_data');
        });
    }



    /**
     * Mengambil path absolut untuk keperluan DomPDF.
     * Menggunakan method ini di Blade jauh lebih aman dan cepat daripada merakit string manual.
     */
    public function getLogoPathAttribute()
    {
        if ($this->logo && Storage::disk('public')->exists($this->logo)) {
            return public_path('storage/' . $this->logo);
        }
        return null;
    }

    /**
     * Accessor untuk mempermudah pengecekan file TTD di Blade secara otomatis.
     */
    public function getTtdKiriPathAttribute()
    {
        if ($this->ttd_kiri && Storage::disk('public')->exists($this->ttd_kiri)) {
            return public_path('storage/' . $this->ttd_kiri);
        }
        return null;
    }

    public function getTtdKananPathAttribute()
    {
        if ($this->ttd_kanan && Storage::disk('public')->exists($this->ttd_kanan)) {
            return public_path('storage/' . $this->ttd_kanan);
        }
        return null;
    }
}