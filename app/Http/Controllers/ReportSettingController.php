<?php

namespace App\Http\Controllers;

use App\Models\ReportSetting;
use App\Models\Department;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportSettingController extends Controller
{
    public function cetakPdf(Request $request)
    {
        // 1. Ambil data setting secara efisien
        $config = ReportSetting::getValues();

        // 2. Mapping Filter dengan Type Casting yang ketat
        $tanggal = $request->filled('tanggal') ? $request->query('tanggal') : null;
        $bulan = $request->filled('bulan') ? (int)$request->query('bulan') : null;
        $tahun = (int)$request->query('tahun', date('Y'));

        // 3. Optimasi Query: Gunakan select() untuk mengurangi beban memori
        $departments = Department::select('id', 'name')
            ->withCount('employees') // Mengambil jumlah pegawai secara efisien
            ->withSum(['riceStocks as total_masuk' => function ($q) use ($tanggal, $bulan, $tahun) {
                if ($tanggal) $q->whereDate('created_at', $tanggal);
                elseif ($bulan) $q->whereMonth('created_at', $bulan)->whereYear('created_at', $tahun);
                else $q->whereYear('created_at', $tahun);
            }], 'total_kg')
            ->withSum(['riceDistributions as total_keluar' => function ($q) use ($tanggal, $bulan, $tahun) {
                if ($tanggal) $q->whereDate('created_at', $tanggal);
                elseif ($bulan) $q->whereMonth('created_at', $bulan)->whereYear('created_at', $tahun);
                else $q->whereYear('created_at', $tahun);
            }], 'amount_kg')
            ->get();

        // 4. Logika Label Periode
        $labelPeriode = "Tahun " . $tahun;
        if ($tanggal) {
            $labelPeriode = Carbon::parse($tanggal)->translatedFormat('d F Y');
        } elseif ($bulan) {
            $labelPeriode = Carbon::create()->month($bulan)->translatedFormat('F') . " " . $tahun;
        }

        $date = now()->translatedFormat('d F Y');

        // 5. Optimasi DomPDF: Tambahkan limit waktu & memori agar tidak timeout
        ini_set('memory_limit', '256M');
        set_time_limit(60);

        // $pdf = Pdf::loadView('filament.pdf.report-settings', compact(
        $pdf = Pdf::loadView('pdf.report-settings', compact(
            'config', 'departments', 'date', 'labelPeriode'
        ));

        // 6. Pengaturan Kertas & Opsi Performa
        return $pdf->setPaper([0, 0, 609.4488, 935.433], 'portrait')
            ->setOption([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => false,
                'isFontSubsettingEnabled' => true, // Mempercepat render font
                'pdfBackend' => 'CPDF',            // Backend lebih cepat untuk layout sederhana
            ])
            ->stream("Laporan_Penyaluran_{$labelPeriode}.pdf");
    }
}