<?php

namespace App\Filament\Resources\MucTieuResource\Pages;

use App\Filament\Resources\MucTieuResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMucTieu extends ViewRecord
{
    protected static string $resource = MucTieuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
