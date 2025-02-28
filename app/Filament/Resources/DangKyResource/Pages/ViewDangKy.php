<?php

namespace App\Filament\Resources\DangKyResource\Pages;

use App\Filament\Resources\DangKyResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDangKy extends ViewRecord
{
    protected static string $resource = DangKyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
