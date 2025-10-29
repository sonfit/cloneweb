<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ThuTinResource\Pages;
use App\Models\ThuTin;
use App\Models\Bookmark;
use App\Services\FunctionHelp;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class ThuTinResource extends Resource
{
    protected static ?string $model = ThuTin::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationGroup = 'Tổng Hợp';
    protected static ?string $navigationLabel = 'Thu tin';
    protected static ?string $modelLabel = 'Thu tin';
    protected static ?string $slug = 'thu-tin';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('id_user')
                    ->label('Người chia sẻ')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->default(auth()->id()),

                Forms\Components\DateTimePicker::make('time')
                    ->label('Thời gian ghi nhận')
                    ->seconds(false)
                    ->default(now()),

                Forms\Components\TextInput::make('link')
                    ->label('Link bài viết')
                    ->maxLength(150)
                    ->required()
                    ->unique(ignoreRecord: true),

                Forms\Components\Select::make('id_muctieu')
                    ->label('Mục tiêu')
                    ->relationship('mucTieu', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->getOptionLabelFromRecordUsing(function ($record) {
                        $sourceKey = 'options.type.' . $record->type;
                        $source = Lang::has($sourceKey) ? trans($sourceKey) : 'Không rõ';
                        return $source . ' - ' . ($record->name ?? 'Không rõ');
                    }),


                Forms\Components\Radio::make('phanloai')
                    ->label('Phân loại tin tức')
                    ->options(__('options.phanloai'))
                    ->default(1)
                    ->required()
                    ->columns(2)
                    ->extraAttributes(['style' => 'margin-left: 50px;']),

                Forms\Components\TextInput::make('diem')
                    ->label('Điểm')
                    ->helperText(new HtmlString('
                        <strong>Điểm từ 0–100:</strong><br>
                        - điểm ≥ 100 → level 5<br>
                        - điểm ≥ 70 → level 4<br>
                        - điểm ≥ 40 → level 3<br>
                        - điểm ≥ 20 → level 2<br>
                        - điểm < 20 → level 1
                    '))
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->required(),

                Forms\Components\Textarea::make('contents_text')
                    ->label('Nội dung bài viết')
                    ->rows(10),

                Forms\Components\FileUpload::make('pic')
                    ->label('Ảnh chụp màn hình')
                    ->image()
                    ->disk('public')
                    ->directory(fn() => 'uploads/thutin/' . now()->format('Ymd'))
                    ->maxSize(512000)
                    ->nullable()
                    ->multiple()
                    ->imagePreviewHeight('200')
                    ->panelLayout('grid')
                    ->reorderable()
                    ->acceptedFileTypes([
                        'image/jpeg',
                        'image/png',
                        'image/webp',
                        'video/mp4',
                        'video/avi',
                        'video/mpeg',
                        'video/quicktime',
                    ]),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $user = auth()->user();
                $query->with(['bookmarks.user'])->withCount('bookmarks');
                
                // Nếu user có quyền admin hoặc super_admin thì xem tất cả
                if ($user->hasAnyRole(['admin', 'super_admin'])) {
                    return $query;
                }
                
                // Nếu user có quyền user thì chỉ xem tin thuộc mục tiêu mà user theo dõi
                if ($user->hasRole('user')) {
                    $mucTieuIds = $user->mucTieus()->pluck('muc_tieus.id')->toArray();
                    
                    // Nếu user chưa theo dõi mục tiêu nào thì không hiển thị gì
                    if (empty($mucTieuIds)) {
                        return $query->whereRaw('1 = 0'); // Always false condition
                    }
                    
                    return $query->whereIn('id_muctieu', $mucTieuIds);
                }
                
                return $query;
            })
            ->defaultSort('time', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('stt')
                    ->label('STT')
                    ->rowIndex(),

                Tables\Columns\TextColumn::make('link')
                    ->label('Link bài viết')
                    ->url(fn($record) => $record->link, true)
                    ->limit(50) // cắt ngắn link cho gọn
                    ->wrap()
                    ->description(fn($record) => $record->contents_text ? Str::limit($record->contents_text, 100) : '')
                    ->tooltip(fn($record) => $record->contents_text ?? '')
                    ->sortable()
                    ->searchable(['link', 'contents_text']),

                Tables\Columns\TextColumn::make('muctieu.name')
                    ->label('Mục tiêu')
                    ->url(fn($record) => $record->muctieu?->link, true) // link sang bài gốc
                    ->color('primary')
                    ->wrap(),

                Tables\Columns\ImageColumn::make('pic')
                    ->label('Hình ảnh')
                    ->disk('public')
                    ->circular()
                    ->stacked()
                    ->limit(3)
                    ->limitedRemainingText()
                    ->getStateUsing(function ($record) {
                        return collect($record->pic)->map(function ($p) {
                            $url = Storage::disk('public')->url($p);
                            $ext = strtolower(pathinfo($url, PATHINFO_EXTENSION));

                            if (in_array($ext, ['mp4', 'webm', 'ogg', 'mov', 'avi'])) {
                                // Nếu là video -> dùng ảnh placeholder
                                return asset('video-placeholder.jpg');
                            }
                            return $url;
                        })->toArray();
                    })
                    ->action(
                        Tables\Actions\Action::make('Xem ảnh')
                            ->modalHeading('Xem media')
                            ->modalContent(fn($record) => view('filament.modals.preview-media', [
                                'urls' => collect($record->pic)->map(fn($p) => Storage::disk('public')->url($p))->toArray()
                            ]))
                            ->modalSubmitAction(false)
                    ),

//                 Phân loại
                Tables\Columns\TextColumn::make('phanloai')
                    ->label('Phân loại')
                    ->formatStateUsing(
                        fn($state) => trans("options.phanloai.$state") !== "options.phanloai.$state"
                            ? trans("options.phanloai.$state")
                            : 'Chưa xác định'
                    )
//                    ->formatStateUsing(fn($state) => trans('options.phanloai.' . $state, [], 'Chưa phân loại'))
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        1, 2 => 'danger',
                        3, 4 => 'warning',
                        5 => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('diem')
                    ->label('Mức độ')
                    ->badge()
                    ->formatStateUsing(function ($state) {
                        $level = FunctionHelp::diemToLevel($state);
                        return "Level {$level} ({$state} điểm)";
                    })
                    ->color(function ($state) {
                        $level = FunctionHelp::diemToLevel($state);
                        return FunctionHelp::levelBadgeColor($level);
                    })
                    ->sortable(),

                Tables\Columns\TagsColumn::make('tags')
                    ->label('Tags')
                    ->getStateUsing(function ($record) {
                        $tags = $record->tags->pluck('tag'); // Lấy danh sách tag name
                        $limit = 3;

                        if ($tags->count() > $limit) {
                            $extraCount = $tags->count() - $limit;
                            return $tags->take($limit)->push("+{$extraCount} Tag");
                        }
                        return $tags;
                    })
                    ->separator(', ')
                    ->badge()
                    ->sortable(query: function ($query, $direction) {
                        return $query->withCount('tags')->orderBy('tags_count', $direction);
                    })
                    ->color(function ($record, $state) {
                        $colors = ['primary', 'success', 'danger', 'info'];
                        $index = $record->tags->search(function ($item) use ($state) {
//                            dd($state, $item);

                                return in_array($item->tag, (array)$state);
                            }) % count($colors); // Lấy chỉ số dựa trên vị trí mục tiêu, lặp lại nếu vượt quá
                        return $colors[$index];
                    }),

                // Người ghi nhận (user)
                Tables\Columns\TextColumn::make('bot.ten_bot')
                    ->label('Bot')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // Thời gian
                Tables\Columns\TextColumn::make('time')
                    ->label('Thời gian')
                    ->dateTime('H:i:s d/m/Y')
                    ->sortable()
                    ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->format('H:i:s d/m/Y'))
                    ->color(fn($state) => FunctionHelp::timeBadgeColor($state)) // Đảm bảo $state là giá trị gốc
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('bot_id')
                    ->label('Bot')
                    ->relationship('bot', 'ten_bot'),

                SelectFilter::make('muctieu_id')
                    ->label('Mục tiêu')
                    ->relationship('muctieu', 'name'),

                SelectFilter::make('phanloai')
                    ->label('Phân loại')
                    ->options(trans('options.phanloai')),

                Tables\Filters\Filter::make('diem')
                    ->form([
                        Forms\Components\TextInput::make('diem_from')
                            ->label('Điểm từ')
                            ->numeric()
                            ->placeholder('0'),
                        Forms\Components\TextInput::make('diem_to')
                            ->label('Điểm đến')
                            ->numeric()
                            ->placeholder('100'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['diem_from'], fn($query, $from) => $query->where('diem', '>=', $from))
                            ->when($data['diem_to'], fn($query, $to) => $query->where('diem', '<=', $to));
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('bookmark')
                    ->label('Bookmark')
                    ->icon('heroicon-o-bookmark')
                    ->color(fn(\App\Models\ThuTin $record) => ($record->bookmarks_count ?? $record->bookmarks()->count()) > 0 ? 'success' : 'gray')
                    ->tooltip(function (\App\Models\ThuTin $record) {
                        $names = $record->bookmarks?->pluck('name') ?? collect();
                        return $names->isNotEmpty() ? $names->join(', ') : 'Chưa có bookmark';
                    })
                    ->form(function () {
                        return [
                            Forms\Components\Select::make('bookmark_ids')
                                ->label('Chọn bookmark')
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->options(function () {
                                    $user = auth()->user();
                                    $isAdmin = $user->hasAnyRole(['admin', 'super_admin']);

                                    return Bookmark::query()
                                        ->when(!$isAdmin, fn($q) => $q->where('user_id', $user->id))
                                        ->with('user')
                                        ->latest()
                                        ->limit(50)
                                        ->get()
                                        ->mapWithKeys(function ($b) use ($isAdmin) {
                                            $date = $b->created_at?->format('d/m/Y H:i');
                                            $label = $isAdmin
                                                ? ($b->name . ' - (' . ($b->user?->name ?? 'user') . ') - ' . $date)
                                                : ($b->name . ' - ' . $date);
                                            return [$b->id => $label];
                                        })
                                        ->toArray();
                                })
                                ->getSearchResultsUsing(function (string $search) {
                                    $user = auth()->user();
                                    $isAdmin = $user->hasAnyRole(['admin', 'super_admin']);

                                    return Bookmark::query()
                                        ->when(!$isAdmin, fn($q) => $q->where('user_id', $user->id))
                                        ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
                                        ->with('user')
                                        ->latest()
                                        ->limit(50)
                                        ->get()
                                        ->mapWithKeys(function ($b) use ($isAdmin) {
                                            $date = $b->created_at?->format('d/m/Y H:i');
                                            $label = $isAdmin
                                                ? ($b->name . ' - (' . ($b->user?->name ?? 'user') . ') - ' . $date)
                                                : ($b->name . ' - ' . $date);
                                            return [$b->id => $label];
                                        })
                                        ->toArray();
                                })
                                ->getOptionLabelUsing(function ($value) {
                                    if (!$value) return null;
                                    $user = auth()->user();
                                    $isAdmin = $user->hasAnyRole(['admin', 'super_admin']);
                                    $b = Bookmark::with('user')->find($value);
                                    if (!$b) return null;
                                    $date = $b->created_at?->format('d/m/Y H:i');
                                    return $isAdmin
                                        ? ($b->name . ' - (' . ($b->user?->name ?? 'user') . ') - ' . $date)
                                        : ($b->name . ' - ' . $date);
                                })
                                ->createOptionForm([
                                    Forms\Components\TextInput::make('name')
                                        ->label('Tên bookmark')
                                        ->required()
                                        ->maxLength(255),
                                ])
                                ->createOptionUsing(function (array $data) {
                                    $user = auth()->user();
                                    $bookmark = Bookmark::create([
                                        'user_id' => $user->id,
                                        'name' => $data['name'] ?? '',
                                    ]);
                                    return $bookmark->id;
                                })
                                ->nullable(),
                        ];
                    })
                    ->mountUsing(function (\Filament\Forms\ComponentContainer $form, ThuTin $record) {
                        $form->fill([
                            'bookmark_ids' => $record->bookmarks()->pluck('bookmarks.id')->toArray(),
                        ]);
                    })
                    ->action(function (ThuTin $record, array $data) {
                        $user = auth()->user();
                        $selectedIds = collect($data['bookmark_ids'] ?? [])->map(fn($v) => (int)$v)->unique()->values();
                        $isAdmin = $user->hasAnyRole(['admin', 'super_admin']);
                        $allowedIds = Bookmark::query()
                            ->when(!$isAdmin, fn($q) => $q->where('user_id', $user->id))
                            ->whereIn('id', $selectedIds)
                            ->pluck('id');

                        $record->bookmarks()->sync($allowedIds);
                    })
                    ->successNotificationTitle('Đã cập nhật bookmark')
                    ->visible(fn() => auth()->check())
                    ->modalHeading('Thêm vào bookmark')
                    ->modalSubmitActionLabel('Lưu'),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListThuTins::route('/'),
            'create' => Pages\CreateThuTin::route('/create'),
            'view' => Pages\ViewThuTin::route('/{record}'),
            'edit' => Pages\EditThuTin::route('/{record}/edit'),
        ];
    }
}
