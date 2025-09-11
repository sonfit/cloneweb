<?php

namespace App\Filament\Resources\MucTieuResource\Pages;

use App\Filament\Resources\MucTieuResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMucTieus extends ListRecords
{
    protected static string $resource = MucTieuResource::class;
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
