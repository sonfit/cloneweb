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
                        ->label('Danh sách Tag')
                        ->rows(10)
                        ->required()
                        ->helperText('Định dạng: Nhóm phân loại|Tên tag|Điểm (mỗi dòng một tag).'),
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
                    $lines = preg_split('/\r\n|\r|\n/', $input);

                    foreach ($lines as $line) {
                        $line = trim($line);
                        if (empty($line)) continue;

                        // Định dạng: "Nhóm|Tên tag|Điểm" hoặc "Nhóm|Tên tag"
                        if (preg_match('/^(.+?)\|(.+?)(?:\|(\d+))?$/u', $line, $matches)) {
                            $parent = trim($matches[1]);
                            $tag = trim($matches[2]);
                            $diem = isset($matches[3]) ? (int)$matches[3] : 0;

                            if ($diem < 0 || $diem > 100) {
                                Notification::make()
                                    ->title('Lỗi')
                                    ->body("Điểm của tag '$tag' phải từ 0 đến 100.")
                                    ->danger()
                                    ->send();
                                return;
                            }

                            $items[] = [
                                'tag' => $tag,
                                'diem' => $diem,
                                'parent' => $parent
                            ];
                        } else {
                            Notification::make()
                                ->title('Lỗi')
                                ->body("Định dạng không hợp lệ: '$line'. Định dạng đúng: Nhóm|Tên tag|Điểm")
                                ->danger()
                                ->send();
                            return;
                        }
                    }


                    if (empty($items)) {
                        Notification::make()
                            ->title('Lỗi')
                            ->body('Không tìm thấy tag hợp lệ.')
                            ->danger()
                            ->send();
                        return;
                    }

                    $created = 0;
                    $updated = 0;
                    DB::beginTransaction();
                    try {
                        foreach ($items as $item) {
                            $tagName = $item['tag'];
                            $diem = $item['diem'];
                            $parent = $item['parent'];

                            // Kiểm tra tag đã tồn tại, nếu có thì update, nếu không thì create
                            $existingTag = Tag::where('tag', $tagName)->first();

                            if ($existingTag) {
                                // Update tag đã tồn tại
                                $existingTag->update([
                                    'diem' => $diem,
                                    'parent' => $parent,
                                ]);
                                $updated++;
                            } else {
                                // Create tag mới
                                Tag::create([
                                    'tag' => $tagName,
                                    'diem' => $diem,
                                    'parent' => $parent,
                                ]);
                                $created++;
                            }
                        }

                        DB::commit();

                        $message = "Đã thêm {$created} tag mới.";
                        if ($updated > 0) {
                            $message .= " Cập nhật {$updated} tag đã tồn tại.";
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

            Actions\Action::make('changeDiemByParent')
                ->label('Thay đổi điểm theo nhóm')
                ->icon('heroicon-o-adjustments-horizontal')
                ->color('success')
                ->modalHeading('Thay đổi điểm hàng loạt theo nhóm')
                ->form([
                    Forms\Components\Select::make('parents')
                        ->label('Chọn nhóm phân loại')
                        ->options(function () {
                            return Tag::whereNotNull('parent')
                                ->distinct()
                                ->pluck('parent', 'parent')
                                ->toArray();
                        })
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->required()
                        ->helperText('Chọn một hoặc nhiều nhóm để thay đổi điểm cho TẤT CẢ tags thuộc nhóm đó'),

                    Forms\Components\TextInput::make('new_diem')
                        ->label('Điểm mới')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->required()
                        ->helperText('Điểm sẽ được áp dụng cho tất cả tags thuộc các nhóm đã chọn'),
                ])
                ->action(function (array $data): void {
                    $selectedParents = $data['parents'];
                    $newDiem = $data['new_diem'];
                    $count = 0;

                    DB::beginTransaction();
                    try {
                        foreach ($selectedParents as $parent) {
                            $updated = Tag::where('parent', $parent)->update(['diem' => $newDiem]);
                            $count += $updated;
                        }

                        DB::commit();

                        Notification::make()
                            ->title('Thành công')
                            ->body("Đã cập nhật điểm cho {$count} tag thuộc " . count($selectedParents) . " nhóm.")
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        DB::rollBack();
                        Notification::make()
                            ->title('Lỗi')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

        ];
    }
}
