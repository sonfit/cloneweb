<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClonedSiteResource\Pages;
use App\Filament\Resources\ClonedSiteResource\RelationManagers;
use App\Models\ClonedSite;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClonedSiteResource extends Resource
{
    protected static ?string $model = ClonedSite::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\TextInput::make('domain')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('web_clone')
                ->required()
                ->maxLength(255),

            Forms\Components\Repeater::make('string_replace_arr')
            ->label('Chuỗi thay thế')
            ->createItemButtonLabel('Thêm chuỗi')
            ->grid(2)
            ->schema([
                Forms\Components\Card::make([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('find')
                            ->label('Tìm (Search)')
                            ->required(),
                        Forms\Components\TextInput::make('replace')
                            ->label('Thay thế (Replace)')
                            ->required(),
                    ]),
                ])->compact(), // dùng compact để bo gọn khoảng cách
            ])
            ->columnSpanFull(),
        ]);
    }



    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('domain')
                    ->searchable(),
                Tables\Columns\TextColumn::make('web_clone')
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
            'index' => Pages\ListClonedSites::route('/'),
            'create' => Pages\CreateClonedSite::route('/create'),
            'edit' => Pages\EditClonedSite::route('/{record}/edit'),
        ];
    }
}
