<?php

namespace App\Filament\Resources\RiceStockResource\Pages;

use App\Filament\Resources\RiceStockResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRiceStock extends EditRecord
{
    protected static string $resource = RiceStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
