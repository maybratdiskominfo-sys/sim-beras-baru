<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Grid;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Illuminate\Support\Facades\Auth;
class ReportLaporanPenyaluran extends Page implements HasForms
{
    use InteractsWithForms;

    // Properti Sidebar
    protected static ?string $navigationIcon = 'heroicon-o-printer';
    protected static ?string $navigationLabel = 'Laporan Penyaluran Beras';
    protected static ?string $title = 'Laporan Penyaluran Beras';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?int $navigationSort = 10; // Urutan di sidebar

    protected static string $view = 'filament.pages.report-laporan-penyaluran';
    protected static ?string $slug = 'cetak-laporan-khusus';

       // --- PROTEKSI AKSES ---
    public static function canAccess(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return $user ?->hasRole('super_admin') ?? false;
    }
    protected function getHeaderActions(): array
   {
        return [
            Action::make('cetak_laporan')
                ->label('Cetak Laporan')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->modalWidth('md')
                ->form([
                    Grid::make(1)->schema([
                        DatePicker::make('tanggal')
                            ->label('Per Tanggal Spesifik')
                            ->native(false) // Lebih ringan di beberapa browser
                            ->displayFormat('d/m/Y'),
                        
                        Grid::make(2)->schema([
                            Select::make('bulan')
                                ->label('Bulan')
                                ->options([
                                    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
                                    '04' => 'April', '05' => 'Mei', '06' => 'Juni',
                                    '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
                                    '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
                                ]),
                            Select::make('tahun')
                                ->label('Tahun')
                                ->options(fn() => array_combine(range(date('Y'), 2022), range(date('Y'), 2022)))
                                ->default(date('Y')),
                        ]),
                    ])
                ])
                ->action(function (array $data) {
                    $params = array_filter($data);
                    // Gunakan redirect()->away agar membuka di tab baru jika browser mengizinkan
                    return redirect()->to(route('pdf.rekap-stok', $params));
                }),
        ];
    }
}