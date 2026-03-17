<?php

namespace App\Filament\Resources\RiceStockResource\Pages;

use App\Filament\Resources\RiceStockResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListRiceStocks extends ListRecords
{
    protected static string $resource = RiceStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Ubah Actions\CreateAction agar menggunakan Modal
            Actions\CreateAction::make()
                ->label('Tambah Stock')
                ->modalHeading('Buat Stock Beras Baru')
                ->modalWidth('2xl')
                ->slideOver() // Opsional: gunakan slideOver jika ingin modal muncul dari samping (lebih elegan)
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Berhasil')
                        ->body('Data Stock telah dicatat ke sistem.')
                )
                // Memastikan tabel di belakang langsung ter-update
                ->after(function () {
                    $this->dispatch('refreshTable'); 
                }),
        ];
    }
}
