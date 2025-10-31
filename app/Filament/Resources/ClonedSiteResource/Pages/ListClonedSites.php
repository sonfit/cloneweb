<?php

namespace App\Filament\Resources\ClonedSiteResource\Pages;

use App\Filament\Resources\ClonedSiteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListClonedSites extends ListRecords
{
    protected static string $resource = ClonedSiteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
