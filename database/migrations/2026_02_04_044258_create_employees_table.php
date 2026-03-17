<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable(); // Jangan pakai constrained dulu karena tabel users belum ada
            $table->string('nip', 20)->unique();
            $table->string('nama_lengkap');
            $table->string('email')->unique()->nullable();
            $table->string('nomor_hp', 15)->nullable();
            $table->string('position')->nullable();
            $table->string('golongan')->nullable();
            $table->string('status_pegawai')->default('PNS');
            $table->decimal('jatah_kg', 8, 2)->default(10.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
