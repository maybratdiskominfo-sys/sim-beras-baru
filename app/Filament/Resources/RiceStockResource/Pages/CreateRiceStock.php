<?php

namespace App\Filament\Resources\RiceStockResource\Pages;

use App\Filament\Resources\RiceStockResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateRiceStock extends CreateRecord
{
    protected static string $resource = RiceStockResource::class;
        //Langkah 3: Redirect kembali ke daftar pegawai setelah sukses.
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
