<?php

namespace App\Filament\Resources\TraceJobResource\Pages;

use App\Filament\Resources\TraceJobResource;
use App\Services\TraceJobService;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTraceJob extends CreateRecord
{
    protected static string $resource = TraceJobResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Đảm bảo payload luôn có giá trị
        $payload = [];
        if (!empty($data['payload_sdt'])) $payload['sdt'] = $data['payload_sdt'];
        if (!empty($data['payload_cccd'])) $payload['cccd'] = $data['payload_cccd'];
        if (!empty($data['payload_fb'])) $payload['fb'] = $data['payload_fb'];


        // Tìm job có kết quả chứa thông tin cần tra cứu (dùng service)
        $existingJob = TraceJobService::findExistingJobByPayload($payload);

        if ($existingJob) {
            // Nếu tìm thấy job có kết quả, redirect đến trang xem của resource (không hardcode route name)
            $this->redirect(TraceJobResource::getUrl('view', ['record' => $existingJob]));
            $this->halt(); // Dừng tiến trình tạo bản ghi mới
        }

        $data['payload'] = array_filter($payload);
        unset($data['payload_sdt'], $data['payload_cccd'], $data['payload_fb']);

        return $data;
    }

}
