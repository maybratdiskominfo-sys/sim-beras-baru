<?php

namespace App\Observers;

use App\Models\ReportSetting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class ReportSettingObserver
{
    /**
     * Menangani event "updated" (saat data diubah).
     */
    public function updated(ReportSetting $reportSetting): void
    {
        // 1. Bersihkan Cache agar perubahan langsung terlihat di PDF
        Cache::forget('report_settings_data');

        // 2. Cek dan hapus file lama jika ada penggantian file (Logo/TTD)
        $fields = ['logo', 'ttd_kiri', 'ttd_kanan'];

        foreach ($fields as $field) {
            if ($reportSetting->isDirty($field)) {
                $oldFile = $reportSetting->getOriginal($field);
                
                if ($oldFile && Storage::disk('public')->exists($oldFile)) {
                    Storage::disk('public')->delete($oldFile);
                }
            }
        }
    }

    /**
     * Menangani event "deleted" (jika record dihapus).
     */
    public function deleted(ReportSetting $reportSetting): void
    {
        Cache::forget('report_settings_data');

        // Hapus semua file terkait jika settingan dihapus
        foreach (['logo', 'ttd_kiri', 'ttd_kanan'] as $field) {
            if ($reportSetting->$field) {
                Storage::disk('public')->delete($reportSetting->$field);
            }
        }
    }
}