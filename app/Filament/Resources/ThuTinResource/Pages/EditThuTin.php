<?php

namespace App\Filament\Resources\ThuTinResource\Pages;

use App\Filament\Resources\ThuTinResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditThuTin extends EditRecord
{
    protected static string $resource = ThuTinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
