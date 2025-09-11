<?php

namespace App\Filament\Resources\MucTieuResource\Pages;

use App\Filament\Resources\MucTieuResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMucTieu extends EditRecord
{
    protected static string $resource = MucTieuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
