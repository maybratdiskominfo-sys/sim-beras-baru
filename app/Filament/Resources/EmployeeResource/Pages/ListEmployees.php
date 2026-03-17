<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListEmployees extends ListRecords
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->label('Tambah Data Pegawai')
                ->modalHeading('Buat data pegawai baru')
                ->modalWidth('2xl')
                ->slideOver() // Opsional: gunakan slideOver jika ingin modal muncul dari samping (lebih elegan)
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Berhasil')
                        ->body('Data pegawai telah dicatat ke sistem.')
                )
                // Memastikan tabel di belakang langsung ter-update
                ->after(function () {
                    $this->dispatch('refreshTable'); 
                }),
        ];
    }
}
