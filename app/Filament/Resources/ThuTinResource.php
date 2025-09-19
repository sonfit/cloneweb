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

                Forms\Components\Select::make('id_bot')
                    ->label('Bot')
                    ->relationship('Bot', 'ten_bot')
                    ->searchable()
                    ->preload()
                    ->nullable(),


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
                    ->directory(fn() => 'uploads/tinhhinh/' . now()->format('Ymd'))
                    ->maxSize(20480)
                    ->nullable()
                    ->optimize('webp'),


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
                    ->sortable(),

                Tables\Columns\TextColumn::make('bot.ten_bot')
                    ->label('Bot')
                    ->color('primary')
                    ->wrap(),

                Tables\Columns\ImageColumn::make('pic')
                    ->label('Hình ảnh')
                    ->disk('public')
                    ->height(80)
                    ->width(120)
                    ->action(
                        Tables\Actions\Action::make('Xem ảnh')
                            ->modalHeading('Xem ảnh')
                            ->modalContent(fn($record) => view('filament.modals.preview-image', [
                                'url' => Storage::disk('public')->url($record->pic)
                            ])
                            )
                            ->modalSubmitAction(false)
                    ),

                // Người ghi nhận (user)
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Người ghi nhận')
                    ->sortable(),

                // Thời gian
                Tables\Columns\TextColumn::make('time')
                    ->label('Time')
                    ->dateTime('d/m/Y')
                    ->sortable(),

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
