<?php

namespace App\Filament\Resources\TraceJobResource\Pages;

use App\Filament\Resources\TraceJobResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTraceJob extends EditRecord
{
    protected static string $resource = TraceJobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Chuyển payload thành các field riêng biệt
        if (isset($data['payload']) && is_array($data['payload'])) {
            $data['payload_sdt'] = $data['payload']['sdt'] ?? '';
            $data['payload_cccd'] = $data['payload']['cccd'] ?? '';
            $data['payload_fb'] = $data['payload']['fb'] ?? '';
        }
        
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Đảm bảo payload luôn có giá trị
        $payload = [];
        if (!empty($data['payload_sdt'])) $payload['sdt'] = $data['payload_sdt'];
        if (!empty($data['payload_cccd'])) $payload['cccd'] = $data['payload_cccd'];
        if (!empty($data['payload_fb'])) $payload['fb'] = $data['payload_fb'];
        
        $data['payload'] = $payload;
        unset($data['payload_sdt'], $data['payload_cccd'], $data['payload_fb']);
        
        return $data;
    }
}
