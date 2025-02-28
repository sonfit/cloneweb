<?php

namespace App\Filament\Resources\TongHopResource\Pages;

use App\Filament\Resources\TongHopResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTongHop extends ListRecords
{
    protected static string $resource = TongHopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
