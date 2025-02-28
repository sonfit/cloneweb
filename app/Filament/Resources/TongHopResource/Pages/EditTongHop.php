<?php

namespace App\Filament\Resources\TongHopResource\Pages;

use App\Filament\Resources\TongHopResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTongHop extends EditRecord
{
    protected static string $resource = TongHopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Đảm bảo dữ liệu 'type' là mảng khi hiển thị form
        $data['type'] = is_array($data['type']) ? $data['type'] : json_decode($data['type'], true) ?? [];
        return $data;
    }
}
