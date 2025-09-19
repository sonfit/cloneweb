<?php

namespace App\Filament\Resources\ThuTinResource\Pages;

use App\Filament\Resources\ThuTinResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewThuTin extends ViewRecord
{
    protected static string $resource = ThuTinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
