<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\User;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\ReportSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RekapPresensi extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-printer';
    protected static ?string $navigationLabel = 'Cetak Rekap Presensi';
    protected static ?string $title = 'Rekapitulasi Presensi';
    protected static ?string $navigationGroup = 'Laporan';
    protected static string $view = 'filament.pages.rekap-presensi';

    // Properti publik untuk binding ke form di view
    public $bulan;
    public $tahun;
    public $department_id;

    /**
     * Mengatur hak akses halaman.
     * Hanya Super Admin dan Admin OPD yang bisa mengakses.
     */
    public static function canAccess(): bool
    {
        /** @var User $user */
        $user = Auth::user();
        return $user && $user->hasRole(['super_admin', 'admin_opd']);
    }

    /**
     * Inisialisasi data saat halaman pertama kali dibuka.
     */
    public function mount(): void
    {
        // Pastikan default value di-cast ke integer untuk mencegah error Carbon
        $this->bulan = (int) now()->month;
        $this->tahun = (int) now()->year;

        /** @var User $user */
        $user = Auth::user();
        
        // Jika bukan super_admin, kunci department_id sesuai departemen user tersebut
        if ($user && !$user->hasRole('super_admin')) {
            $this->department_id = $user->department_id;
        }
    }

    /**
     * Fungsi utama untuk memproses data dan mengunduh PDF.
     */
    public function print()
    {
        // 1. Validasi Input & Force Casting ke Integer
        // Livewire terkadang mengirimkan input form sebagai string ("03") 
        // yang memicu error setUnit() pada Carbon jika tidak dikonversi ke int.
        $this->validate([
            'bulan' => 'required',
            'tahun' => 'required',
            'department_id' => 'required',
        ]);

        $bulanInt = (int) $this->bulan;
        $tahunInt = (int) $this->tahun;

        $dept = Department::find($this->department_id);
        $global = ReportSetting::getValues();

        if (!$dept) {
            Notification::make()->title('Gagal')->body('Data Dinas tidak ditemukan.')->danger()->send();
            return;
        }

        // 2. Generate Rentang Tanggal sesuai Bulan & Tahun Pilihan
        // Kita mengunci Carbon ke tanggal 1 di bulan/tahun terpilih
        $targetDate = Carbon::create($tahunInt, $bulanInt, 1);
        $daysInMonth = $targetDate->daysInMonth;
        
        $dateRange = [];
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $date = Carbon::create($tahunInt, $bulanInt, $i);
            $dateRange[] = [
                'tgl' => $i,
                'hari' => $date->isoFormat('dd'), // Nama hari singkat (Sen, Sel, dst)
                'is_weekend' => $date->isWeekend(),
            ];
        }

        // 3. Ambil Data Pegawai & Map Kehadiran ke dalam Matrix
        // Kita hanya mengambil pegawai yang berada di departemen terpilih
        $employees = Employee::where('department_id', $dept->id)->get();
        $reportData = [];

        foreach ($employees as $emp) {
            // Filter kehadiran: Mengunci ke ID pegawai, bulan terpilih, dan tahun terpilih
            $attendances = Attendance::where('employee_id', $emp->id)
                ->whereMonth('date', $bulanInt)
                ->whereYear('date', $tahunInt)
                ->get()
                ->keyBy(function($item) {
                    // Indexing berdasarkan tanggal (day) untuk memudahkan pencocokan di loop matrix
                    return Carbon::parse($item->date)->day;
                });

            $details = [];
            // Loop sebanyak jumlah hari dalam bulan tersebut (1-28/30/31)
            foreach (range(1, $daysInMonth) as $day) {
                if (isset($attendances[$day])) {
                    $status = $attendances[$day]->status;
                    // Konversi status panjang menjadi inisial untuk tampilan tabel matrix
                    $details[$day] = match($status) {
                        'Hadir', 'Terlambat' => 'H',
                        'Izin' => 'I',
                        'Sakit' => 'S',
                        'Alpa' => 'A',
                        default => '.',
                    };
                } else {
                    $details[$day] = null; // Tidak ada record presensi pada tanggal tersebut
                }
            }

            // Gabungkan data pegawai dengan matrix kehadirannya
            $reportData[] = [
                'nama'    => $emp->nama_lengkap,
                'nip'     => $emp->nip,
                'details' => $details,
                'hadir'   => $attendances->whereIn('status', ['Hadir', 'Terlambat'])->count(),
                'izin'    => $attendances->where('status', 'Izin')->count(),
                'sakit'   => $attendances->where('status', 'Sakit')->count(),
                'alpa'    => $attendances->where('status', 'Alpa')->count(),
            ];
        }

        // 4. Hitung Statistik Keseluruhan (Total di bagian bawah tabel)
        $stats = [
            'total_hadir' => collect($reportData)->sum('hadir'),
            'total_izin'  => collect($reportData)->sum('izin') + collect($reportData)->sum('sakit'),
            'total_alpa'  => collect($reportData)->sum('alpa'),
            'total_hari_kerja' => collect($dateRange)->where('is_weekend', false)->count(),
        ];

        // Nama bulan untuk judul file & laporan
        $nama_bulan = Carbon::create()->month($bulanInt)->isoFormat('MMMM');
        $nama_file = "Rekap_Presensi_{$dept->code}_{$nama_bulan}_{$tahunInt}.pdf";

        // Mencegah timeout saat memproses data PDF yang besar/banyak pegawai
        ini_set('max_execution_time', 300);

        // 5. Stream Download PDF menggunakan DomPDF
        return response()->streamDownload(function () use ($reportData, $dept, $global, $nama_bulan, $stats, $dateRange, $tahunInt) {
            echo Pdf::loadView('pdf.rekap-presensi', [
                'data'       => $reportData,
                'stats'      => $stats,
                'dateRange'  => $dateRange,
                'bulan_nama' => $nama_bulan,
                'tahun'      => $tahunInt,
                'header'     => [
                    'logo'   => $dept->logo_path ?? $global->logo_path,
                    'pemda'  => strtoupper($global->nama_pemda),
                    'dinas'  => strtoupper($dept->name),
                    'alamat' => $dept->alamat_kantor ?? $global->alamat,
                ],
                'footer'     => [
                    'jabatan'     => "Kepala " . $dept->name,
                    'nama'        => $dept->nama_kadin ?? $global->nama_kanan,
                    'nip'         => $dept->nip_kadin ?? $global->nip_kanan,
                    'show_ttd'    => $global->aktifkan_ttd_digital,
                    'ttd_path'    => $dept->ttd_path ?? $global->ttd_kanan_path,
                    'kota'        => 'Kumurkek', // Bisa disesuaikan dinamis
                    'petugas'     => $dept->nama_petugas ?? '...........................',
                    'nip_petugas' => $dept->nip_petugas ?? '...........................',
                ]
            ])->setPaper('a4', 'landscape')->output(); // Landscape wajib karena tabel melebar ke samping (31 hari)
        }, $nama_file);
    }
}