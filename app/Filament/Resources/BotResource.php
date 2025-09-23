<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BotResource\Pages;
use App\Filament\Resources\BotResource\RelationManagers;
use App\Models\Bot;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BotResource extends Resource
{
    protected static ?string $model = Bot::class;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';
    protected static ?string $navigationLabel = 'Bot';
    protected static ?string $modelLabel = 'Bot';
    protected static ?string $slug = 'bot';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('ten_bot')
                    ->label('Tên Bot')
                    ->required()
                    ->maxLength(150)
                    ->unique(ignoreRecord: true),

                Forms\Components\TextInput::make('loai_bot')
                    ->label('Loại Bot')
                    ->maxLength(150)
                    ->required(),

                Forms\Components\TextInput::make('lenh_bot')
                    ->label('Lệnh Bot')
                    ->maxLength(150)
                    ->required(),

                Forms\Components\Select::make('mucTieus')
                    ->multiple()
                    ->label('Mục tiêu')
                    ->relationship('mucTieus', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->getOptionLabelFromRecordUsing(function ($record) {
                        return (__('options.sources.' . $record->type, [], 'Không rõ') . ' - ' . $record->name ?? 'Không rõ');
                    }),

                Forms\Components\Textarea::make('ghi_chu')
                    ->label('Ghi chú')
                    ->maxLength(1000)
                    ->rows(6),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('STT')
                    ->rowIndex(),

                Tables\Columns\TextColumn::make('ten_bot')
                    ->label('Tên Bot')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('loai_bot')
                    ->label('Loại Bot')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('mucTieus.name')
                    ->label('Mục tiêu')
                    ->badge()
                    ->separator(', ')
                    ->color(fn () => collect([
                        'primary',
                        'secondary',
                        'success',
                        'warning',
                        'danger',
                        'info',
                    ])->random())
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\TextColumn::make('time_crawl')
                    ->label('Lần bot truy cập')
                    ->dateTime('H:i:s d/m/Y')
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
            'index' => Pages\ListBots::route('/'),
            'create' => Pages\CreateBot::route('/create'),
            'view' => Pages\ViewBot::route('/{record}'),
            'edit' => Pages\EditBot::route('/{record}/edit'),
        ];
    }
}
