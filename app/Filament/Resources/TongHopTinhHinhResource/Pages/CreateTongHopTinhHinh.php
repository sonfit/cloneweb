<?php

namespace App\Filament\Resources\TongHopTinhHinhResource\Pages;

use App\Filament\Resources\TongHopTinhHinhResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTongHopTinhHinh extends CreateRecord
{
    protected static string $resource = TongHopTinhHinhResource::class;


    public function generateSummary(): void
    {
        $state = $this->form->getState();
        $content = $state['contents_text'] ?? '';

        if (! trim(strip_tags($content))) {
            $this->notify('danger', 'Không có nội dung để tóm tắt.');
            return;
        }

        $summary = $this->summarizeContentWithAI($content);

        if (str_starts_with($summary, 'Lỗi')) {
            $this->notify('danger', $summary);
            return;
        }

        // fill lại form
        $this->form->fill([
            'sumary' => $summary,
        ]);

        $this->notify('success', 'Tóm tắt nội dung thành công!');
    }
}
