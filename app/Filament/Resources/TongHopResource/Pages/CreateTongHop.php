<?php

namespace App\Filament\Resources\TongHopResource\Pages;

use App\Filament\Resources\TongHopResource;
use App\Models\TongHop;
use App\Models\User;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Illuminate\Database\Eloquent\Model;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Joaopaulolndev\FilamentGeneralSettings\Models\GeneralSetting;

use OpenAI;

class CreateTongHop extends CreateRecord
{
    protected static string $resource = TongHopResource::class;
    protected function getFormActions(): array
    {
        return [
            Action::make('process')
                ->label('Tóm tắt')
                ->color('warning')
                ->action(fn() => $this->processData()), // Gọi hàm xử lý
            Action::make('save')
                ->label('Lưu')
                ->color('success')
                ->submit('create'),
            Action::make('cancel')
                ->label('Huỷ')
                ->url($this->getResource()::getUrl('index')) // Quay về danh sách
                ->color('gray'),
        ];
    }
    protected function processData()
    {
        $data = $this->form->getState();
        // Validate input
        if (empty($data['url']) && empty($data['raw_text'])) {
            return $this->sendErrorNotification('Vui lòng cung cấp URL hoặc văn bản để tóm tắt');
        }

        // Get API config
        $apiConfig = $this->getApiConfiguration();
        if (!$apiConfig['valid']) {
            return $this->sendErrorNotification($apiConfig['message']);
        }

        // Build AI prompt
        $messages = $this->buildAIPrompt($data['url'], $data['raw_text'], $apiConfig['promptContent']);
        try {
            $client = OpenAI::client($apiConfig['key']);
            $response = $client->chat()->create([
                'model' => $apiConfig['model'],
                'messages' => $messages,
                'temperature' => $apiConfig['temperature'],
                'max_tokens' => $apiConfig['max_tokens'],
            ]);

            $summary = $response->choices[0]->message->content ?? 'Không thể tóm tắt nội dung.';
        } catch (\Exception $e) {
            return 'Lỗi trong quá trình tóm tắt: ' . $e->getMessage();
        }


        $this->form->fill([
            'url' => $data['url'],
            'raw_text' => $data['raw_text'],
            'summary_text' => $summary,
        ]);


        // Thông báo thành công
        return $this->sendSuccessNotification('Tóm tắt nội dung thành công!');

    }
    private function getApiConfiguration(): array
    {
        $settings = GeneralSetting::first();

        return [
            'key' => $settings->more_configs['key_api'] ?? null,
            'model' => $settings->more_configs['model_api'] ?? 'gpt-3.5-turbo',
            'temperature' => $settings->more_configs['temperature_api'] ?? '0.5',
            'max_tokens' => $settings->more_configs['max_tokens_api'] ?? '300',
            'promptContent' => $settings->more_configs[$settings->more_configs['select_prompt'] ?? 'prompt_1'] ?? '',
            'valid' => !empty($settings->more_configs['key_api']),
            'message' => empty($settings) ? 'Cấu hình hệ thống không tồn tại' : 'Cấu hình API không hợp lệ'
        ];
    }
    private function buildAIPrompt(?string $url, ?string $rawText, string $prompt): array
    {
        $sources = array_filter([
            !empty($url) ? "[URL]: $url" : null,
            !empty($rawText) ? "[Raw Text]: $rawText" : null
        ]);
        $sourcesText = implode("\n", $sources);

        $messages = [
            [
                'role' => 'system',
                'content' => $prompt
            ],
            [
                'role' => 'user',
                'content' => $sourcesText
            ],
        ];

        return $messages;
    }
    private function sendErrorNotification(string $message): Notification
    {
        return Notification::make()
            ->title('Thất bại')
            ->danger()
            ->body($message)
            ->send();
    }
    private function sendSuccessNotification(string $message): Notification
    {
        return Notification::make()
            ->title('Thành công')
            ->success()
            ->body($message)
            ->send();
    }
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Lấy user_id từ người dùng đăng nhập hoặc URL
        $userId = auth()->id(); // Mặc định từ người dùng đăng nhập

        // Nếu có query string ?name=aa, ưu tiên lấy từ đó
        if ($name = request()->query('name')) {
            $user = User::where('name', $name)->first();
            $userId = $user?->id;
        }

        // Thêm user_id vào dữ liệu trước khi lưu
        $data['user_id'] = $userId;
        if (isset($data['type']) && is_array($data['type'])) {
            $data['type'] = json_encode($data['type'], JSON_UNESCAPED_UNICODE);
        }

        return $data;
    }
    protected function handleRecordCreation(array $data): Model
    {
        // Đảm bảo dữ liệu là mảng và lưu vào model
        return TongHop::create($data);
    }

}
