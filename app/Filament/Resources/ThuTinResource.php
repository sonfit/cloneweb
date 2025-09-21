<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ThuTinResource\Pages;
use App\Filament\Resources\ThuTinResource\RelationManagers;
use App\Models\ThuTin;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
class ThuTinResource extends Resource
{
    protected static ?string $model = ThuTin::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationLabel = 'Thu tin';
    protected static ?string $modelLabel = 'Thu tin';
    protected static ?string $slug = 'thu-tin';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
                        return (__('options.sources.' . $record->type, [], 'Không rõ') . ' - ' . $record->name ?? 'Không rõ');
                    }),


                Forms\Components\Radio::make('phanloai')
                    ->label('Phân loại tin tức')
                    ->options(__('options.phanloai'))
                    ->default(1)
                    ->required()
                    ->columns(2)
                    ->extraAttributes(['style' => 'margin-left: 50px;']),

                Forms\Components\FileUpload::make('pic')
                    ->label('Ảnh chụp màn hình')
                    ->image()
                    ->disk('public')
                    ->directory(fn() => 'uploads/thutin/' . now()->format('Ymd'))
                    ->maxSize(512000)
                    ->nullable()
                    ->multiple()
                    ->acceptedFileTypes([
                        'image/jpeg',
                        'image/png',
                        'image/webp',
                        'video/mp4',
                        'video/avi',
                        'video/mpeg',
                        'video/quicktime',
                    ]),


                Forms\Components\Textarea::make('contents_text')
                    ->label('Nội dung bài viết')
                    ->rows(7),

                Forms\Components\Radio::make('level')
                    ->label('Mức độ quan trọng')
                    ->options(__('options.levels'))
                    ->default(1)
                    ->extraAttributes(['style' => 'margin-left: 50px;'])
                    ->required(),

                Forms\Components\Select::make('id_user')
                    ->label('Người chia sẻ')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->default(auth()->id()),

                Forms\Components\DateTimePicker::make('time')
                    ->label('Thời gian ghi nhận')
                    ->seconds(false)
                    ->default(now()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
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
                    ->sortable(),

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

                            if (in_array($ext, ['mp4','webm','ogg','mov','avi'])) {
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



                // Phân loại
                Tables\Columns\TextColumn::make('phanloai')
                    ->label('Phân loại')
                    ->formatStateUsing(fn($state) => trans('options.phanloai.' . $state, [], 'Chưa phân loại'))
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        1, 2 => 'danger',
                        3, 4 => 'warning',
                        5 => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('level')
                    ->label('Mức độ')
                    ->badge()
                    ->colors([
                        'gray' => 1,
                        'info'    => 2,
                        'success' => 3,
                        'warning'  => 4,
                        'danger'    => 5,
                    ])
                    ->formatStateUsing(fn($state) => trans('options.levels.' . $state, [], 'Chưa xác định'))
                    ->sortable(),

                // Người ghi nhận (user)
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Người ghi nhận')
                    ->sortable(),

                // Thời gian
                Tables\Columns\TextColumn::make('time')
                    ->label('Time')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
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
