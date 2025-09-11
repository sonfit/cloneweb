<?php

namespace App\Filament\Resources\TongHopTinhHinhResource\Pages;

use App\Filament\Resources\TongHopTinhHinhResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTongHopTinhHinh extends ViewRecord
{
    protected static string $resource = TongHopTinhHinhResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
