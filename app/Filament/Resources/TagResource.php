<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TagResource\Pages;
use App\Models\Tag;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;


class TagResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Tag::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Tổng Hợp';
    protected static ?string $navigationLabel = 'Từ khoá';
    protected static ?string $modelLabel = 'Từ khóa';
    protected static ?string $slug = 'tu-khoa';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('tag')
                    ->required(),
                Forms\Components\TextInput::make('diem')
                    ->label('Điểm')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(10)
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tag')->label('Từ khoá')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('diem')->label('Điểm')->sortable()->searchable(),
            ])
            ->filters([
                SelectFilter::make('diem')
                    ->label('Lọc theo điểm')
                    ->options([
                        '0' => 'Không có điểm',
                        '1' => '1',
                        '2' => '2',
                        '3' => '3',
                        '4' => '4',
                        '5' => '5',
                        '6' => '6',
                        '7' => '7',
                        '8' => '8',
                        '9' => '9',
                        '10' => '10',
                    ])
                    ->placeholder('Chọn điểm')
                    ->default(null) // Không chọn mặc định
                    ->multiple() // Cho phép chọn nhiều điểm để lọc
                    ->query(function ($query, array $data) {
                        if (!empty($data['values'])) {
                            $query->whereIn('diem', $data['values']);
                        }
                    }),
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
            'index' => Pages\ListTags::route('/'),
            'create' => Pages\CreateTag::route('/create'),
            'view' => Pages\ViewTag::route('/{record}'),
            'edit' => Pages\EditTag::route('/{record}/edit'),
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
}
