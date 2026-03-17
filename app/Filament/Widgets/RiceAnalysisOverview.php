<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use App\Models\RiceStock;
use App\Models\RiceDistribution;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class RiceAnalysisOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        $deptId = Auth::user()->department_id;
        $isSuperAdmin = Gate::allows('super_admin');

        // 1. Ambil daftar Tahap yang unik (berdasarkan stok yang diinput)
        $stagesQuery = RiceStock::query();
        if (!$isSuperAdmin) $stagesQuery->where('department_id', $deptId);
        $activeStages = $stagesQuery->distinct()->pluck('tahap');
        $countStages = $activeStages->count();

        // 2. Hitung Jatah Dasar & Total Kewajiban (Jatah x Jumlah Tahap)
        $queryEmp = Employee::where('is_active', true);
        if (!$isSuperAdmin) $queryEmp->where('department_id', $deptId);
        $baseKebutuhan = $queryEmp->sum('jatah_kg');
        $totalKebutuhanMultiStage = $baseKebutuhan * ($countStages ?: 1);

        // 3. Hitung Total Stok & Penyaluran Keseluruhan
        $totalStokMasuk = RiceStock::when(!$isSuperAdmin, fn($q) => $q->where('department_id', $deptId))->sum('total_kg');
        $totalDisalurkan = RiceDistribution::when(!$isSuperAdmin, fn($q) => $q->where('department_id', $deptId))->sum('amount_kg');
        $sisaGudangGlobal = $totalStokMasuk - $totalDisalurkan;

        // 4. Logic Breakdown untuk Stat 2 (Sisa Stok per Tahap) & Stat 3 (Progress per Tahap)
        $stockBreakdownText = [];
        $distBreakdownText = [];
        
        foreach ($activeStages as $stg) {
            // Data per Tahap
            $stokStg = RiceStock::where('tahap', $stg)
                ->when(!$isSuperAdmin, fn($q) => $q->where('department_id', $deptId))
                ->sum('total_kg');
            
            $distStg = RiceDistribution::where('tahap', $stg)
                ->when(!$isSuperAdmin, fn($q) => $q->where('department_id', $deptId))
                ->sum('amount_kg');

            // Hitung Sisa & Persentase
            $sisaStg = $stokStg - $distStg;
            $persenStg = ($baseKebutuhan > 0) ? ($distStg / $baseKebutuhan) * 100 : 0;

            $stockBreakdownText[] = "{$stg}: " . number_format($sisaStg, 0, ',', '.') . " Kg";
            $distBreakdownText[] = "{$stg}: " . number_format($persenStg, 1) . "%";
        }

        $stockDescription = implode(' | ', $stockBreakdownText);
        $distDescription = implode(' | ', $distBreakdownText);

        // Hitung Progress Global untuk Warna Stat 3
        $progressGlobal = ($totalKebutuhanMultiStage > 0) ? ($totalDisalurkan / $totalKebutuhanMultiStage) * 100 : 0;
        
        // Tentukan warna berdasarkan progress
        if ($progressGlobal >= 100) {
            $colorStat3 = 'success';
        } elseif ($progressGlobal > 0) {
            $colorStat3 = 'warning';
        } else {
            $colorStat3 = 'gray';
        }

        return [
           // STAT 1: TOTAL KEWAJIBAN DINAMIS
            Stat::make('Total Kewajiban Beras', number_format($totalKebutuhanMultiStage, 0, ',', '.') . ' Kg')
                ->description("Kebutuhan untuk {$countStages} Tahap Aktif")
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('info'),
                // ->chart([$baseKebutuhan, $totalKebutuhanMultiStage]),

            // STAT 2: SISA STOK DENGAN BREAKDOWN PER TAHAP
            Stat::make('Sisa Stok Gudang', number_format($sisaGudangGlobal, 0, ',', '.') . ' Kg')
                ->description($stockDescription ?: 'Belum ada input stok')
                ->descriptionIcon('heroicon-m-circle-stack')
                ->color($sisaGudangGlobal < ($totalKebutuhanMultiStage - $totalDisalurkan) ? 'danger' : 'success'),
                // ->chart([$totalStokMasuk, $sisaGudangGlobal]),

            // STAT 3: REALISASI DENGAN BREAKDOWN PERSENTASE PER TAHAP
            Stat::make('Total Sudah Disalurkan', number_format($totalDisalurkan, 0, ',', '.') . ' Kg')
                ->description($distDescription ?: 'Belum ada penyaluran')
                ->descriptionIcon('heroicon-m-truck')
                ->color($colorStat3) // Menggunakan variabel, bukan closure
                // ->chart([0, $totalDisalurkan]),
        ];
    }
}