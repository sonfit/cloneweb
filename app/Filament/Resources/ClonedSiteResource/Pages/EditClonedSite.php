<?php

namespace App\Filament\Resources\ClonedSiteResource\Pages;

use App\Filament\Resources\ClonedSiteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClonedSite extends EditRecord
{
    protected static string $resource = ClonedSiteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
