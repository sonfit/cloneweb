<?php

namespace App\Filament\Widgets;

use App\Models\ThuTin;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Str;

class LatestThuTinWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ThuTin::query()
                    ->with(['mucTieu', 'bot'])
                    ->latest('created_at')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('bot.ten_bot')
                    ->label('Tên Bot'),

                Tables\Columns\TextColumn::make('link')
                    ->label('Link')
                    ->url(fn($record) => $record->link, true)
                    ->limit(50)
                    ->wrap()
                    ->searchable(),

                Tables\Columns\TextColumn::make('mucTieu.name')
                    ->label('Mục tiêu')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('phanloai')
                    ->label('Phân loại')
                    ->badge()
                    ->formatStateUsing(
                        fn ($state) => trans("options.phanloai.$state") !== "options.phanloai.$state"
                            ? trans("options.phanloai.$state")
                            : 'Chưa xác định'
                    )
                    ->color(fn($state) => match($state) {
                        1, 2 => 'danger',
                        3, 4 => 'warning',
                        5 => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('level')
                    ->label('Mức độ')
                    ->badge()
                    ->formatStateUsing(
                        fn ($state) => trans("options.levels.$state") !== "options.levels.$state"
                            ? trans("options.levels.$state")
                            : 'Chưa xác định'
                    )
                    ->color(fn($state) => match($state) {
                        1 => 'gray',
                        2 => 'info',
                        3 => 'success',
                        4 => 'warning',
                        5 => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('time')
                    ->label('Thời gian')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->heading('Tin tức mới nhất')
            ->description('10 tin tức được thu thập gần đây nhất')
            ->paginated(false);
    }
}

