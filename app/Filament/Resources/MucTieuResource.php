<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MucTieuResource\Pages;
use App\Filament\Resources\MucTieuResource\RelationManagers;
use App\Models\MucTieu;
use App\Services\FunctionHelp;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MucTieuResource extends Resource
{
    protected static ?string $model = MucTieu::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Tổng Hợp';
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
                    ->maxLength(250),


                Forms\Components\TextInput::make('link')
                    ->label('Link mục tiêu')
                    ->url()
                    ->required()
                    ->unique(ignoreRecord: true),

                Forms\Components\Radio::make('phanloai')
                    ->label('Phân loại')
                    ->options(__('options.phanloai'))
                    ->default(1)
                    ->required()
                    ->columns(2)
                    ->extraAttributes(['style' => 'margin-left: 50px;']),

                Forms\Components\Radio::make('type')
                    ->label('Nguồn')
                    ->options(__('options.sources'))
                    ->default(1)
                    ->required()
                    ->columns(2)
                    ->extraAttributes(['style' => 'margin-left: 50px;']),


                Forms\Components\DateTimePicker::make('time_create')
                    ->label('Thời gian tạo trên hệ thống')
                    ->default(now())
                    ->disabled(),

                Forms\Components\DateTimePicker::make('time_crawl')
                    ->label('Lần cuối bot truy cập')
                    ->default(now()),

                Forms\Components\Textarea::make('ghi_chu')
                    ->label('Ghi chú')
                    ->maxLength(1000)
                    ->rows(6),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('time_crawl', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('STT')
                    ->rowIndex(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Tên hiển thị')
                    ->limit(30)
                    ->url(fn ($record) => $record->link, true)
                    ->searchable(['name', 'link'])
                    ->sortable()
                    ->tooltip(fn($record) => $record->name ?? ''),

                Tables\Columns\TextColumn::make('phanloai')
                    ->label('Phân loại')
                    ->formatStateUsing(
                        fn ($state) => trans("options.phanloai.$state") !== "options.phanloai.$state"
                            ? trans("options.phanloai.$state")
                            : 'Chưa xác định'
                    ),

                Tables\Columns\TextColumn::make('type')
                    ->label('Nguồn')
                    ->formatStateUsing(
                        fn ($state) => trans("options.sources.$state") !== "options.sources.$state"
                            ? trans("options.sources.$state")
                            : 'Chưa xác định'
                    ),

                Tables\Columns\TextColumn::make('time_crawl')
                    ->label('Lần bot truy cập')
                    ->dateTime('H:i:s d/m/Y')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => \Carbon\Carbon::parse($state)->format('H:i:s d/m/Y'))
                    ->color(fn ($state) => FunctionHelp::timeBadgeColor($state)) // Đảm bảo $state là giá trị gốc
                    ->badge(),

                Tables\Columns\TextColumn::make('time_create')
                    ->label('Tạo trên hệ thống')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo bản ghi')
                    ->dateTime('H:i:s d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('phanloai')
                    ->label('Phân loại')
                    ->options(trans('options.phanloai')),
                SelectFilter::make('type')
                    ->label('Nguôn')
                    ->options(trans('options.sources')),
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
