<?php

namespace App\Filament\Widgets;

use App\Models\DangKy;
use App\Models\ThuTin;
use App\Models\TongHopTinhHinh;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class ChartStatsWidget extends ChartWidget
{
    protected static ?int $sort = 3;
    protected static ?string $heading = 'Biểu đồ thống kê 30 ngày qua';
    protected int | string | array $columnSpan = 'full';
    protected static ?string $maxHeight = '400px';

    public static function canView(): bool
    {
        $user = auth()->user();
        // Cho phép xem nếu có quyền widget hoặc là admin/super_admin
        return $user->can('widget_ChartStatsWidget') || $user->hasAnyRole(['admin', 'super_admin']);
    }

    protected function getData(): array
    {
        $labels = [];
        $tongHopTinhhinhData = [];
        $thuTinData = [];

        $user = auth()->user();
        $isAdmin = $user->hasAnyRole(['admin', 'super_admin']);

        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('d/m');

            // Chỉ admin và super_admin mới hiển thị tổng hợp tình hình
            if ($isAdmin) {
                $tongHopTinhhinhData[] = TongHopTinhHinh::whereDate('created_at', $date)->count();
            }

            // Thu thập tin - filter theo role
            $thuTinQuery = ThuTin::whereDate('created_at', $date);

            if (!$isAdmin && $user->hasRole('user')) {
                // User chỉ xem tin của mục tiêu đã theo dõi
                $mucTieuIds = $user->mucTieus()->pluck('muc_tieus.id')->toArray();
                if (!empty($mucTieuIds)) {
                    $thuTinQuery->whereIn('id_muctieu', $mucTieuIds);
                } else {
                    $thuTinQuery->whereRaw('1 = 0'); // Không hiển thị gì nếu chưa theo dõi mục tiêu nào
                }
            }

            $thuTinData[] = $thuTinQuery->count();
        }

        $datasets = [
            [
                'label' => 'Thu thập tin',
                'data' => $thuTinData,
                'borderColor' => 'rgb(34, 197, 94)',
                'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
            ],
        ];

        // Chỉ thêm dataset tổng hợp tình hình cho admin
        if ($isAdmin) {
            array_unshift($datasets, [
                'label' => 'Tổng hợp tình hình',
                'data' => $tongHopTinhhinhData,
                'borderColor' => 'rgb(59, 130, 246)',
                'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
            ]);
        }

        return [
            'datasets' => $datasets,
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'top',
                ],
            ],
        ];
    }
}

