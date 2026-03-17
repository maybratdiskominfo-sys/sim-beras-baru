<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rice_distributions', function (Blueprint $table) {
            $table->id();

            // Relasi Utama - Menggunakan index untuk performa query
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();

            // Data Distribusi
            $table->string('tahap'); // Contoh: "Tahap I", "Triwulan I"
            $table->decimal('amount_kg', 8, 2); // 8 digit total, 2 digit belakang koma
            
            // Menggunakan integer untuk bulan agar mudah di-filter (1-12)
            $table->unsignedTinyInteger('month')->index(); 
            $table->year('year')->index();
            
            $table->dateTime('taken_at');
            $table->string('receiver_name');

            // Log Audit Petugas - restrict agar data petugas tidak bisa dihapus jika ada history distribusi
            $table->foreignId('user_id')->constrained()->onDelete('restrict');

            $table->timestamps();
            
            // Composite Index: Berguna jika Anda sering mencari "Siapa saja yang sudah ambil di bulan X tahun Y"
            $table->index(['month', 'year', 'employee_id'], 'idx_distribution_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rice_distributions');
    }
};