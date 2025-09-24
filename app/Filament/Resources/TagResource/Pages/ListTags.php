<?php

namespace App\Filament\Resources\TagResource\Pages;

use App\Filament\Resources\TagResource;
use App\Models\Tag;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Filament\Forms;
class ListTags extends ListRecords
{
    protected static string $resource = TagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),



            Actions\Action::make('importTags')
                ->label('Thêm nhiều Tag')
                ->icon('heroicon-o-plus')
                ->color('info')
                ->modalHeading('Nhập nhiều Tag')
                ->modalWidth('lg')
                ->form([
                    Forms\Components\Textarea::make('tags_input')
                        ->label('Danh sách Tag (JSON hoặc mỗi dòng một tag, ví dụ: "Hà Nội|10" hoặc "Hà Nội")')
                        ->rows(10)
                        ->required()
                        ->helperText('Hỗ trợ định dạng JSON (ví dụ: [{"tag": "Hà Nội", "diem": 10}]) hoặc danh sách tag (mỗi dòng một tag). Điểm từ 1-10, mặc định 0 nếu không nhập điểm.'),
                ])
                ->action(function (array $data): void {
                    $input = trim($data['tags_input'] ?? '');
                    if (empty($input)) {
                        Notification::make()
                            ->title('Lỗi')
                            ->body('Vui lòng nhập danh sách tag.')
                            ->danger()
                            ->send();
                        return;
                    }

                    $items = [];
                    // Thử sửa JSON không hợp lệ (bọc trong [] nếu cần)
                    $jsonInput = $input;

                    // Thử decode JSON
                    $jsonData = json_decode($jsonInput, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($jsonData)) {
                        $items = $jsonData;
                    } else {
                        // Nếu không phải JSON, parse theo dòng
                        $lines = preg_split('/\r\n|\r|\n/', $input);
                        foreach ($lines as $line) {
                            $line = trim($line);
                            if (empty($line)) continue;

                            // Hỗ trợ: "Tag|10", "Tag - 10", hoặc chỉ "Tag"
                            if (preg_match('/^(.+?)\s*[\|\-]\s*(\d{1,2})$/u', $line, $matches)) {
                                $tag = trim($matches[1]);
                                $diem = (int)$matches[2];
                                if ($diem < 1 || $diem > 10) {
                                    Notification::make()
                                        ->title('Lỗi')
                                        ->body("Điểm của tag '$tag' phải từ 1 đến 10.")
                                        ->danger()
                                        ->send();
                                    return;
                                }
                                $items[] = ['tag' => $tag, 'diem' => $diem];
                            } else {
                                $items[] = ['tag' => $line, 'diem' => 0];
                            }
                        }
                    }


                    if (empty($items)) {
                        Notification::make()
                            ->title('Lỗi')
                            ->body('Không tìm thấy tag hợp lệ. Vui lòng kiểm tra định dạng JSON hoặc danh sách dòng.')
                            ->danger()
                            ->send();
                        return;
                    }

                    $created = 0;
                    $skipped = 0;
                    DB::beginTransaction();
                    try {
                        foreach ($items as $item) {
                            if (!is_array($item) || empty(trim($item['tag'] ?? ''))) continue;

                            $tagName = trim($item['tag']);
                            $diem = isset($item['diem']) ? (int)$item['diem'] : 0;

                            // Kiểm tra điểm hợp lệ (1-10) cho JSON input
                            if ($diem < 0 || $diem > 10) {
                                throw new \Exception("Điểm của tag '$tagName' phải từ 1 đến 10.");
                            }

                            // Kiểm tra tag đã tồn tại
                            if (Tag::where('tag', $tagName)->exists()) {
                                $skipped++;
                                continue;
                            }

                            Tag::create([
                                'tag' => $tagName,
                                'diem' => $diem,
                            ]);
                            $created++;
                        }

                        DB::commit();

                        $message = "Đã thêm {$created} tag.";
                        if ($skipped > 0) {
                            $message .= " Bỏ qua {$skipped} tag trùng lặp.";
                        }

                        Notification::make()
                            ->title('Thành công')
                            ->body($message)
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        DB::rollBack();
                        Notification::make()
                            ->title('Lỗi khi thêm tag')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),


        ];
    }
}
