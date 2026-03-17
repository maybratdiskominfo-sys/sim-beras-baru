<?php

namespace App\Filament\Resources\RiceDistributionResource\Pages;

use App\Filament\Widgets\RiceAnalysisOverview;
use App\Filament\Resources\RiceDistributionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification; // BENAR

class ListRiceDistributions extends ListRecords
{
    protected static string $resource = RiceDistributionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Ubah Actions\CreateAction agar menggunakan Modal
            Actions\CreateAction::make()
                ->label('Tambah Pengambilan')
                ->modalHeading('Buat Pengambilan Beras Baru')
                ->modalWidth('2xl')
                ->slideOver() // Opsional: gunakan slideOver jika ingin modal muncul dari samping (lebih elegan)
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Berhasil')
                        ->body('Data pengambilan telah dicatat ke sistem.')
                )
                // Memastikan tabel di belakang langsung ter-update
                ->after(function () {
                    $this->dispatch('refreshTable'); 
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            RiceAnalysisOverview::class,
        ];
    }

}
