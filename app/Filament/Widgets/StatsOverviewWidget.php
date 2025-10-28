<?php

namespace App\Filament\Widgets;

use App\Models\DangKy;
use App\Models\ThuTin;
use App\Models\TongHopTinhHinh;
use App\Models\User;
use App\Models\TraceJob;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalTongHopTinhhinh = TongHopTinhHinh::count();
        $totalThueTin = ThuTin::count();
        $totalUsers = User::count();
        $pendingJobs = TraceJob::where('status', 'pending')->count();

        $todayTongHopTinhhinh = TongHopTinhHinh::whereDate('created_at', today())->count();
        $todayThueTin = ThuTin::whereDate('created_at', today())->count();

        $yesterdayTongHopTinhhinh = TongHopTinhhinh::whereDate('created_at', today()->subDay())->count();
        $yesterdayThueTin = ThuTin::whereDate('created_at', today()->subDay())->count();

        return [
            Stat::make('Tổng số tổng hợp', Number::format($totalTongHopTinhhinh))
                ->description($totalTongHopTinhhinh . ' tổng hợp hôm nay')
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->descriptionColor($todayTongHopTinhhinh > $yesterdayTongHopTinhhinh ? 'success' : 'warning')
                ->color('info')
                ->chart($this->getDangKyChartData()),

            Stat::make('Tổng số thu thập tin', Number::format($totalThueTin))
                ->description($todayThueTin . ' tin hôm nay')
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->descriptionColor($todayThueTin > $yesterdayThueTin ? 'success' : 'warning')
                ->color('success')
                ->chart($this->getThueTinChartData()),

            Stat::make('Tổng số người dùng', Number::format($totalUsers))
                ->description('Người dùng hệ thống')
                ->descriptionIcon('heroicon-o-users')
                ->color('warning'),

            Stat::make('Job đang chờ', Number::format($pendingJobs))
                ->description('Chưa được xử lý')
                ->descriptionIcon('heroicon-o-clock')
                ->descriptionColor($pendingJobs > 0 ? 'warning' : 'success')
                ->color($pendingJobs > 0 ? 'danger' : 'success'),
        ];
    }

    protected function getDangKyChartData(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $data[] = DangKy::whereDate('created_at', today()->subDays($i))->count();
        }
        return $data;
    }

    protected function getThueTinChartData(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $data[] = ThuTin::whereDate('created_at', today()->subDays($i))->count();
        }
        return $data;
    }
}

