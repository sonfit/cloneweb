<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TongHopTinhHinhResource\Pages;
use App\Filament\Resources\TongHopTinhHinhResource\RelationManagers;
use App\Models\TongHopTinhHinh;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;

class TongHopTinhHinhResource extends Resource
{
    protected static ?string $model = TongHopTinhHinh::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Tổng hợp tình hình';
    protected static ?string $modelLabel = 'Tổng hợp tình hình';
    protected static ?string $slug = 'tong-hop-tinh-hinh';

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
                    ->nullable(),

                Forms\Components\Radio::make('phanloai')
                    ->label('Phân loại tin tức')
                    ->options([
                        1 => 'ANQG trong địa bàn',
                        2 => 'ANQG ngoài địa bàn',
                        3 => 'TTXH trong địa bàn',
                        4 => 'TTXH ngoài địa bàn',
                        5 => 'Dư luận xã hội liên quan CTP',
                    ])
                    ->default(1)
                    ->inline() // hiện radio theo hàng ngang
                    ->required(),

                Forms\Components\FileUpload::make('pic')
                    ->label('Ảnh chụp màn hình')
                    ->image()
                    ->disk('public')
                    ->directory(fn () => 'uploads/tinhhinh/' . Carbon::now()->format('Ymd'))
                    ->maxSize(20480)
                    ->nullable(),

                Forms\Components\Textarea::make('contents_text')
                    ->label('Nội dung bài viết')
                    ->rows(6),

                Forms\Components\Textarea::make('sumary')
                    ->label('Tóm tắt nội dung')
                    ->maxLength(500)
                    ->rows(6),

                Forms\Components\Select::make('id_user')
                    ->label('Người chia sẻ')
                    ->relationship('user', 'name')
                    ->searchable(),

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
                // STT
                Tables\Columns\TextColumn::make('stt')
                    ->label('STT')
                    ->rowIndex(), // Tự động đánh số theo thứ tự hiển thị

                // Nội dung tóm tắt
                Tables\Columns\TextColumn::make('link')
                    ->label('Link bài viết')
                    ->url(fn ($record) => $record->link, true) // click được, mở tab mới
                    ->limit(20) // cắt ngắn link cho gọn
                    ->wrap()
                    ->description(fn ($record) => $record->sumary ?? '') // tóm tắt hiển thị dưới
                    ->sortable(),

                // Mục tiêu (liên kết từ bảng muc_tieus)
                Tables\Columns\TextColumn::make('muctieu.name')
                    ->label('Mục tiêu')
                    ->url(fn ($record) => $record->link, true) // link sang bài gốc
                    ->color('primary')
                    ->wrap(),

                // Hình ảnh (thumbnail)
                Tables\Columns\ImageColumn::make('pic')
                    ->label('Hình ảnh')
                    ->disk('public')     // dùng same disk như FileUpload
                    ->height(80)
                    ->width(120),

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
                    ->formatStateUsing(fn ($state) => match ($state) {
                        1 => 'ANQG trong địa bàn',
                        2 => 'ANQG ngoài địa bàn',
                        3 => 'TTXH trong địa bàn',
                        4 => 'TTXH ngoài địa bàn',
                        5 => 'Dư luận xã hội liên quan CTP',
                        default => 'Chưa phân loại',
                    })
                    ->badge() // hiển thị badge màu đẹp
                    ->color(fn ($state) => match ($state) {
                        1, 2 => 'danger',
                        3, 4 => 'warning',
                        5 => 'info',
                        default => 'gray',
                    }),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListTongHopTinhHinhs::route('/'),
            'create' => Pages\CreateTongHopTinhHinh::route('/create'),
            'view' => Pages\ViewTongHopTinhHinh::route('/{record}'),
            'edit' => Pages\EditTongHopTinhHinh::route('/{record}/edit'),
        ];
    }
}
