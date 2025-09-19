<?php

namespace App\Filament\Resources\ThuTinResource\Pages;

use App\Filament\Resources\ThuTinResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListThuTins extends ListRecords
{
    protected static string $resource = ThuTinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
