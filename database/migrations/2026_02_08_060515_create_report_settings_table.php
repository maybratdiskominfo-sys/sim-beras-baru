<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_settings', function (Blueprint $table) {
            $table->id();

            // Konfigurasi Header
            $table->string('logo')->nullable();
            $table->string('nama_pemda')->default('PEMERINTAH KABUPATEN MAYBRAT');
            $table->string('judul_laporan')->default('REKAPITULASI KONSOLIDASI DISTRIBUSI BERAS');
            $table->string('sub_judul')->default('Seluruh Dinas / SKPD / Unit Kerja Lingkup Kabupaten Maybrat');
            $table->text('alamat')->nullable();

            // Pejabat Kiri (Contoh: Sekda)
            $table->string('jabatan_kiri')->default('Sekretaris Daerah');
            $table->string('nama_kiri')->nullable();
            $table->string('nip_kiri', 30)->nullable();
            $table->string('ttd_kiri')->nullable();

            // Pejabat Kanan (Contoh: Admin Logistik)
            $table->string('jabatan_kanan')->default('Admin Logistik Kabupaten');
            $table->string('nama_kanan')->nullable();
            $table->string('nip_kanan', 30)->nullable();
            $table->string('ttd_kanan')->nullable();

            // Switch Global
            $table->boolean('aktifkan_ttd_digital')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_settings');
    }
};
