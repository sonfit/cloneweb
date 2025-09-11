<?php

namespace App\Filament\Resources\TongHopTinhHinhResource\Pages;

use App\Filament\Resources\TongHopTinhHinhResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTongHopTinhHinh extends EditRecord
{
    protected static string $resource = TongHopTinhHinhResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
