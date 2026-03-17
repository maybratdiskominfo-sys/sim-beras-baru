<?php

namespace App\Filament\Resources\RiceDistributionResource\Pages;

use App\Filament\Resources\RiceDistributionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRiceDistribution extends EditRecord
{
    protected static string $resource = RiceDistributionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
