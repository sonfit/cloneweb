<?php

namespace App\Filament\Resources\TraceJobResource\Pages;

use App\Filament\Resources\TraceJobResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTraceJob extends ViewRecord
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

        // Thêm formatted_result để hiển thị đẹp
        if (isset($data['result'])) {
            $data['formatted_result'] = $this->record->formatted_result;
        }

        return $data;
    }
}
