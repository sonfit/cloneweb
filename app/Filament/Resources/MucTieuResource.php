<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MucTieuResource\Pages;
use App\Filament\Resources\MucTieuResource\RelationManagers;
use App\Models\MucTieu;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MucTieuResource extends Resource
{
    protected static ?string $model = MucTieu::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Mục tiêu';
    protected static ?string $modelLabel = 'Mục tiêu';
    protected static ?string $slug = 'muc-tieu';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Tên hiển thị')
                    ->required()
                    ->maxLength(50),

                Forms\Components\Select::make('type')
                    ->label('Phân loại')
                    ->required()
                    ->options([
                        1 => 'Facebook cá nhân',
                        2 => 'Fanpage',
                        3 => 'Group',
                        4 => 'TikTok',
                        5 => 'Channel Telegram',
                        6 => 'Group Telegram',
                    ]),

                Forms\Components\TextInput::make('link')
                    ->label('Link mục tiêu')
                    ->url()
                    ->required(),

                Forms\Components\DateTimePicker::make('time_create')
                    ->label('Thời gian tạo trên hệ thống')
                    ->default(now())
                    ->disabled(),

                Forms\Components\DateTimePicker::make('time_crawl')
                    ->label('Lần cuối bot truy cập')
                    ->default(now())
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('STT')
                    ->rowIndex(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Tên hiển thị')
                    ->url(fn ($record) => $record->link, true)
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Phân loại')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        1 => 'Facebook cá nhân',
                        2 => 'Fanpage',
                        3 => 'Group',
                        4 => 'TikTok',
                        5 => 'Channel Telegram',
                        6 => 'Group Telegram',
                        default => 'Khác',
                    }),

//                Tables\Columns\TextColumn::make('link')
//                    ->label('Link')
//                    ->url(fn ($record) => $record->link, true)
//                    ->limit(50),

                Tables\Columns\TextColumn::make('time_create')
                    ->label('Tạo trên hệ thống')
                    ->dateTime('d/m/Y H:i'),

                Tables\Columns\TextColumn::make('time_crawl')
                    ->label('Lần bot truy cập')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo bản ghi')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
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
            'index' => Pages\ListMucTieus::route('/'),
            'create' => Pages\CreateMucTieu::route('/create'),
            'view' => Pages\ViewMucTieu::route('/{record}'),
            'edit' => Pages\EditMucTieu::route('/{record}/edit'),
        ];
    }
}
