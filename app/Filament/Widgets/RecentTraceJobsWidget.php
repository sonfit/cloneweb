<?php

namespace App\Filament\Widgets;

use App\Models\TraceJob;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentTraceJobsWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = auth()->user();
        // Cho phép xem nếu có quyền widget hoặc là admin/super_admin
        return $user->can('widget_RecentTraceJobsWidget') || $user->hasAnyRole(['admin', 'super_admin']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                TraceJob::query()
                    ->latest('created_at')
                    ->limit(5)
            )
            ->columns([

                Tables\Columns\TextColumn::make('payload_sdt')
                    ->label('SDT')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('payload_cccd')
                    ->label('CCCD')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('payload_fb')
                    ->label('Facebook')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->formatStateUsing(fn($state) => match($state) {
                        'pending' => 'Đang chờ',
                        'processing' => 'Đang xử lý',
                        'completed' => 'Hoàn thành',
                        'failed' => 'Thất bại',
                        default => $state,
                    })
                    ->color(fn($state) => match($state) {
                        'pending' => 'warning',
                        'processing' => 'info',
                        'completed' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('claimed_at')
                    ->label('Thời gian xử lý')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable()
                    ->toggleable(),
            ])
            ->heading('Job gần đây')
            ->description('5 job được xử lý gần đây nhất')
            ->paginated(false);
    }
}

