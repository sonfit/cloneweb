<?php

namespace App\Filament\Resources\DangKyResource\Pages;

use App\Filament\Resources\DangKyResource;
use App\Models\DangKy;
use App\Models\User;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;
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

        // Kiểm tra xem user đã tạo trong ngày chưa
        $existingRecord = DangKy::where('user_id', $userId)
            ->whereDate('created_at', today())
            ->exists();

        if ($existingRecord) {
            Notification::make()
                ->title('Thất bại')
                ->body('Bạn chỉ có thể đăng ký một lần mỗi ngày.')
                ->danger()
                ->send();

            throw ValidationException::withMessages([
                'error' => ['Bạn chỉ có thể đăng ký một lần mỗi ngày.']
            ]);
        }

        $data['ip_user'] = $this->getUserIp();
        $data['user_id'] = $userId;

        if (isset($data['type']) && is_array($data['type'])) {
            $data['type'] = json_encode($data['type'], JSON_UNESCAPED_UNICODE);
        }

        return $data;
    }

    protected function getUserIp(): string
    {
        return request()->header('CF-Connecting-IP') ?? request()->header('X-Forwarded-For') ?? request()->ip();
    }
}
