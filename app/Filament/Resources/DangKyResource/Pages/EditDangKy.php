<?php

namespace App\Filament\Resources\DangKyResource\Pages;

use App\Filament\Resources\DangKyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDangKy extends EditRecord
{
    protected static string $resource = DangKyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function getTitle(): string
    {
        return 'Chỉnh sửa báo cáo đơn vị: ' . $this->record->user->name_full.' - Ngày:'. $this->record->created_at->format('d/m/Y');
    }
}
