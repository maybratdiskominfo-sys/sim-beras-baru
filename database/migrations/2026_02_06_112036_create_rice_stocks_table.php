<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rice_stocks', function (Blueprint $table) {
            $table->id();

            // Relasi ke Dinas (Wajib untuk Multi-Tenancy)
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();

            // Info Stok
            $table->string('tahap')->nullable();
            $table->integer('jumlah_karung');
            $table->decimal('berat_per_karung', 8, 2)->default(50.00);
            $table->decimal('total_kg', 12, 2); // Kapasitas hingga jutaan kg

            $table->date('tanggal_masuk');
            $table->string('keterangan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rice_stocks');
    }
};
