<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TongHopResource\Pages;
use App\Models\TongHop;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

use Illuminate\Support\Arr;

class TongHopResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = TongHop::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Tổng Hợp';
    protected static ?string $navigationLabel = 'Tổng Hợp';
    protected static ?string $modelLabel = 'Tổng Hợp';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\CheckboxList::make('type')
                    ->options([
                        'an_ninh' => 'ANQG',
                        'ttxt' => 'TTXH',
                        'trend' => 'Trend MXH',
                    ])
                    ->columns(3)
                    ->default([]),
                Forms\Components\TextInput::make('name')
                    ->placeholder('Nhập tên bài viết')
                    ->required()
                    ->columnSpanFull()
                    ->maxLength(255),
                Forms\Components\TextInput::make('url')
                    ->placeholder('Nhập URL bài viết')
                    ->required()
                    ->columnSpanFull()
                    ->maxLength(255),
                Forms\Components\Textarea::make('raw_text')
                    ->placeholder('Nhập nội dung bổ sung (tùy chọn)')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('summary_text')
                    ->columnSpanFull(),
            ]);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->formatStateUsing(function ($state) {
                        $colorMap = [
                            'an_ninh' => 'red',
                            'ttxt' => 'orange',
                            'trend' => 'green',
                        ];

                        return collect(Arr::wrap(json_decode($state, true) ?? $state))
                            ->map(function ($item) use ($colorMap) {
                                $color = $colorMap[$item] ?? 'gray';
                                return <<<HTML
                                    <span class="inline-block px-2 py-1 rounded text-white text-sm" style="background-color: {$color}">
                                        {$item}
                                    </span>
                                HTML;
                            })
                            ->implode(' ');
                    })
                    ->html()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListTongHop::route('/'),
            'create' => Pages\CreateTongHop::route('/create'),
            'view' => Pages\ViewTongHop::route('/{record}'),
            'edit' => Pages\EditTongHop::route('/{record}/edit'),
        ];
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
        ];
    }

    public static function canCreate(): bool
    {
        return true;
    }

    public static function canViewAny(): bool
    {
        return true;
    }

}
