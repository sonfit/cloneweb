<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DangKyResource\Pages;
use App\Models\DangKy;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Actions\CreateAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;


class DangKyResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = DangKy::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Báo cáo đăng ký xe';
    protected static ?string $modelLabel = 'Báo cáo đăng ký xe';
    protected static ?string $slug = 'dang-ky';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('oto_muc_3')
                    ->label('Ô tô Mức 3')
                    ->required()
                    ->numeric()
                    ->columnSpan(1), // Chiếm 2 cột để giữ cân đối

                Forms\Components\TextInput::make('xe_may_muc_3')
                    ->label('Xe Máy Mức 3')
                    ->required()
                    ->numeric()
                    ->columnSpan(1),

                Forms\Components\TextInput::make('oto_muc_4')
                    ->label('Ô tô Mức 4')
                    ->required()
                    ->numeric()
                    ->columnSpan(1),

                Forms\Components\TextInput::make('xe_may_muc_4')
                    ->label('Xe Máy Mức 4')
                    ->required()
                    ->numeric()
                    ->columnSpan(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->label('Ten')->separator(', '),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
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
            'index' => Pages\ListDangKies::route('/'),
            'create' => Pages\CreateDangKy::route('/create'),
            'view' => Pages\ViewDangKy::route('/{record}'),
            'edit' => Pages\EditDangKy::route('/{record}/edit'),
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
