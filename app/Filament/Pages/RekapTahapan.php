<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Employee;
use App\Models\Department;
use App\Models\RiceStock;
use App\Models\RiceDistribution;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class RekapTahapan extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = 'Cetak Rekap Tahapan';
    protected static ?string $title = 'Rekapitulasi Tahapan';
    protected static ?string $navigationGroup = 'Laporan';
    protected static string $view = 'filament.pages.rekap-tahapan';

    public $tahap;
    public $bulan;
    public $tahun;
    public $department_id;

    public static function canAccess(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return $user->hasRole(['super_admin', 'admin_opd']); 
    }

    public function mount()
    {
        $this->bulan = now()->month;
        $this->tahun = now()->year;

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasRole('super_admin')) {
            $this->department_id = $user->department_id;
        }
    }

    public function getTahapListProperty()
    {
        if (!$this->department_id) {
            return [];
        }

        return RiceStock::where('department_id', $this->department_id)
            ->distinct()
            ->orderBy('tahap', 'asc')
            ->pluck('tahap');
    }

    public function print()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasRole('super_admin')) {
            $this->department_id = $user->department_id;
        }

        $this->validate([
            'tahap'         => 'required',
            'bulan'         => 'required',
            'tahun'         => 'required',
            'department_id' => 'required',
        ]);

        $dept = Department::find($this->department_id);

        if (!$dept) {
            Notification::make()->title('Gagal')->body('Data Dinas tidak ditemukan.')->danger()->send();
            return;
        }

        $pejabat = [
            'nama_instansi' => $dept->name,
            'nama_kadin'    => $dept->nama_kadin ?? '................................',
            'nip_kadin'     => $dept->nip_kadin ?? '................................',
            'alamat'        => $dept->alamat_kantor ?? 'Alamat kantor belum diatur.',
            'logo'          => $dept->logo_kiri,
            'nama_petugas'  => $dept->nama_petugas ?? '................................',
            'nip_petugas'   => $dept->nip_petugas ?? '................................',
        ];

        $employees = Employee::where('department_id', $dept->id)->get();
        $reportData = [];
        $stats = ['lunas' => 0, 'sisa' => 0, 'belum' => 0];

        foreach ($employees as $emp) {
            // Query distribusi untuk menghitung total dan mengambil tanggal terakhir
            $distributionQuery = RiceDistribution::where('employee_id', $emp->id)
                ->where('department_id', $dept->id)
                ->where('tahap', $this->tahap)
                ->where('month', $this->bulan)
                ->where('year', $this->tahun);

            $totalAmbil = (float) $distributionQuery->sum('amount_kg');
            
            // Ambil data pengambilan terakhir untuk mengetahui tanggalnya
            $lastDistribution = $distributionQuery->latest('created_at')->first();
            $tanggalAmbil = $lastDistribution 
                ? $lastDistribution->created_at->format('d/m/Y') 
                : '-';

            $totalJatah = (float) ($emp->jatah_kg ?? 0);
            $sisaGudang = $totalJatah - $totalAmbil;

            if ($totalAmbil >= $totalJatah && $totalJatah > 0) {
                $statusKey = 'lunas';
                $statusText = 'Jatah Lunas';
                $stats['lunas']++;
                $labelColor = 'success';
            } elseif ($totalAmbil > 0 && $sisaGudang > 0) {
                $statusKey = 'sisa';
                $statusText = "Sisa: " . number_format($sisaGudang, 0) . " Kg";
                $stats['sisa']++;
                $labelColor = 'warning';
            } else {
                $statusKey = 'belum';
                $statusText = 'Belum Ambil';
                $stats['belum']++;
                $labelColor = 'gray';
            }

            $reportData[] = [
                'nama'         => $emp->nama_lengkap,
                'nip'          => $emp->nip,
                'jatah'        => $totalJatah,
                'total_ambil'  => $totalAmbil,
                'sisa'         => max(0, $sisaGudang),
                'tanggal'      => $tanggalAmbil, // Keterangan tanggal ditambahkan di sini
                'status_key'   => $statusKey,
                'status_text'  => $statusText,
                'color'        => $labelColor
            ];
        }

        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $bulan_nama = $months[$this->bulan];
        $nama_file = "Rekap-{$dept->code}-Tahap-{$this->tahap}-{$bulan_nama}-{$this->tahun}.pdf";

        return response()->streamDownload(function () use ($reportData, $stats, $bulan_nama, $pejabat) {
            echo Pdf::loadView('pdf.rekap-pengambilan', [
                'data'       => $reportData,
                'stats'      => $stats,
                'bulan_nama' => $bulan_nama,
                'tahun'      => $this->tahun,
                'tahap'      => $this->tahap,
                'pejabat'    => $pejabat
            ])->setPaper('a4', 'portrait')->output();
        }, $nama_file);
    }
}