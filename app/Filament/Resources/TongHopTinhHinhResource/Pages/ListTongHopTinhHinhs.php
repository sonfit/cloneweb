<?php

namespace App\Filament\Resources\TongHopTinhHinhResource\Pages;

use App\Filament\Resources\TongHopTinhHinhResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTongHopTinhHinhs extends ListRecords
{
    protected static string $resource = TongHopTinhHinhResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
