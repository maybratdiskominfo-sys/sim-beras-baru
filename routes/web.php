<?php

use App\Http\Controllers\ReportSettingController;
use App\Livewire\Front\Home;
use App\Livewire\Blog\Index as BlogIndex;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - SIM BERAS & PORTAL BERITA
|--------------------------------------------------------------------------
*/

/**
 * HALAMAN FRONTEND (PORTAL BERITA)
 * Livewire v3 akan otomatis menggunakan layout di:
 * resources/views/components/layouts/app.blade.php
 */

// Mengarahkan halaman utama (root) ke Komponen Home Livewire
Route::get('/', Home::class)->name('home');

// Halaman Daftar Berita
// Route::get('/blog', BlogIndex::class)->name('blog');


/**
 * HALAMAN BACKEND / LAPORAN
 */

// Route untuk cetak PDF Laporan Rekapitulasi Stok
Route::get('/laporan/rekapitulasi-stok', [ReportSettingController::class, 'cetakPdf'])
    ->name('pdf.rekap-stok')
    ->middleware(['auth']); 

/**
 * TIPS:
 * Jika tampilan masih belum berubah, jalankan perintah ini di terminal:
 * php artisan route:clear
 */