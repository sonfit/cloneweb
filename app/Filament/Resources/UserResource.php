<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;


class UserResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationGroup = 'Filament Shield';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\TextInput::make('email')->email()->required(),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->nullable() // Cho phép bỏ trống
                    ->dehydrateStateUsing(fn ($state) => !empty($state) ? bcrypt($state) : null) // Mã hóa nếu có nhập
                    ->afterStateHydrated(fn ($set) => $set('password', '')) // Đặt giá trị rỗng khi load form
                    ->required(fn ($livewire) => $livewire instanceof Pages\CreateUser), // Chỉ bắt buộc khi tạo user
                CheckboxList::make('roles')
                    ->relationship('roles', 'name')
                    ->options(function () {
                        $user = auth()->user();

                        // Sử dụng cấu trúc dữ liệu cho phép roles và thứ tự
                        $roleConfig = once(function () use ($user) {
                            return match (true) {
                                $user->hasRole('super_admin') => [
                                    'roles' => ['super_admin', 'admin', 'user'],
                                    'order' => ['super_admin' => 1, 'admin' => 2, 'user' => 3]
                                ],
                                $user->hasRole('admin') => [
                                    'roles' => ['admin', 'user'],
                                    'order' => ['admin' => 1, 'user' => 2]
                                ],
                                default => [
                                    'roles' => ['user'],
                                    'order' => []
                                ]
                            };
                        });

                        return Role::whereIn('name', $roleConfig['roles'])
                            ->get()
                            ->sortBy(fn (Role $role) => $roleConfig['order'][$role->name] ?? PHP_INT_MAX)
                            ->pluck('name', 'id');
                    })
                    ->live()
                    ->afterStateUpdated(function ($set, $get, $state) {
                        if (count($state) > 1) {
                            $set('roles', [array_pop($state)]);
                        }
                    })
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('name_full')->searchable(),
//                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('roles.name') // Lấy tên của vai trò từ quan hệ roles
                ->label('Vai trò')
                    ->badge() // Hiển thị dưới dạng badge
                    ->color(fn (string $state): string => match ($state) {
                        'super_admin' => 'danger',
                        'admin' => 'warning',
                        'user' => 'success',
                    })
                    ->sortable()
                    ->searchable(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }


    public static function canCreate(): bool
    {
        return auth()->user()->hasRole('super_admin');
    }
    public static function canEdit($record): bool
    {
        return auth()->user()->hasRole('super_admin') ||
            (auth()->user()->hasRole('admin') && !$record->hasRole('super_admin'));
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->hasRole('super_admin') && $record->id !== auth()->id();
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
