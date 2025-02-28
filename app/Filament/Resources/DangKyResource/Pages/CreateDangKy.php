<?php

namespace App\Filament\Resources\DangKyResource\Pages;

use App\Filament\Resources\DangKyResource;
use App\Models\User;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Livewire\Attributes\Url;

class CreateDangKy extends CreateRecord
{
    protected static string $resource = DangKyResource::class;

    #[Url] // Tự động lấy query string ?name= vào biến này
    public ?string $name = null;
    public ?string $ip = null;

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Lưu')
                ->color('success')
                ->submit('create')
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $userId = auth()->id();

        $name = $this->name;
        if ($name) {
            $user = User::where('name', $name)->first();
            $userId = $user?->id;
        }
        $data['ip_user'] = '127.0.0.1';
        $data['user_id'] = $userId;
        if (isset($data['type']) && is_array($data['type'])) {
            $data['type'] = json_encode($data['type'], JSON_UNESCAPED_UNICODE);
        }
        return $data;
    }
}
