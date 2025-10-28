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

    protected function getData(): array
    {
        $labels = [];
        $tongHopTinhhinhData = [];
        $thueTinData = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('d/m');

            $tongHopTinhhinhData[] = TongHopTinhhinh::whereDate('created_at', $date)->count();
            $thueTinData[] = ThuTin::whereDate('created_at', $date)->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Tổng hợp tình hình',
                    'data' => $tongHopTinhhinhData,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                ],
                [
                    'label' => 'Thu thập tin',
                    'data' => $thueTinData,
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                ],
            ],
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

