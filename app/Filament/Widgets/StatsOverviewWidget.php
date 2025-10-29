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
    public static function canView(): bool
    {
        $user = auth()->user();
        // Cho phép xem nếu có quyền widget hoặc là admin/super_admin
        return $user->can('widget_StatsOverviewWidget') || $user->hasAnyRole(['admin', 'super_admin']);
    }

    protected function getStats(): array
    {
        $user = auth()->user();
        $isAdmin = $user->hasAnyRole(['admin', 'super_admin']);

        $stats = [];

        // Chỉ admin mới hiển thị tổng hợp tình hình
        if ($isAdmin) {
            $totalTongHopTinhhinh = TongHopTinhHinh::count();
            $todayTongHopTinhhinh = TongHopTinhhinh::whereDate('created_at', today())->count();
            $yesterdayTongHopTinhhinh = TongHopTinhhinh::whereDate('created_at', today()->subDay())->count();

            $stats[] = Stat::make('Tổng số tổng hợp', Number::format($totalTongHopTinhhinh))
                ->description($totalTongHopTinhhinh . ' tổng hợp hôm nay')
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->descriptionColor($todayTongHopTinhhinh > $yesterdayTongHopTinhhinh ? 'success' : 'warning')
                ->color('info')
                ->chart($this->getDangKyChartData());
        }

        // Thu thập tin - filter theo role
        $thuTinQuery = ThuTin::query();
        if (!$isAdmin && $user->hasRole('user')) {
            $mucTieuIds = $user->mucTieus()->pluck('muc_tieus.id')->toArray();
            if (!empty($mucTieuIds)) {
                $thuTinQuery->whereIn('id_muctieu', $mucTieuIds);
            } else {
                $thuTinQuery->whereRaw('1 = 0');
            }
        }

        $totalThueTin = $thuTinQuery->count();
        $todayThueTin = (clone $thuTinQuery)->whereDate('created_at', today())->count();
        $yesterdayThueTin = (clone $thuTinQuery)->whereDate('created_at', today()->subDay())->count();

        $stats[] = Stat::make('Tổng số thu thập tin', Number::format($totalThueTin))
            ->description($todayThueTin . ' tin hôm nay')
            ->descriptionIcon('heroicon-o-arrow-trending-up')
            ->descriptionColor($todayThueTin > $yesterdayThueTin ? 'success' : 'warning')
            ->color('success')
            ->chart($this->getThueTinChartData());

        // Chỉ admin mới hiển thị số người dùng
        if ($isAdmin) {
            $totalUsers = User::count();
            $stats[] = Stat::make('Tổng số người dùng', Number::format($totalUsers))
                ->description('Người dùng hệ thống')
                ->descriptionIcon('heroicon-o-users')
                ->color('warning');
        }

        // Chỉ admin mới hiển thị包扎 job chờ
        if ($isAdmin) {
            $pendingJobs = TraceJob::where('status', 'pending')->count();
            $stats[] = Stat::make('Job đang chờ', Number::format($pendingJobs))
                ->description('Chưa được xử lý')
                ->descriptionIcon('heroicon-o-clock')
                ->descriptionColor($pendingJobs > 0 ? 'warning' : 'success')
                ->color($pendingJobs > 0 ? 'danger' : 'success');
        }

        return $stats;
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
        $user = auth()->user();
        $isAdmin = $user->hasAnyRole(['admin', 'super_admin']);

        for ($i = 6; $i >= 0; $i--) {
            $thuTinQuery = ThuTin::whereDate('created_at', today()->subDays($i));

            if (!$isAdmin && $user->hasRole('user')) {
                $mucTieuIds = $user->mucTieus()->pluck('muc_tieus.id')->toArray();
                if (!empty($mucTieuIds)) {
                    $thuTinQuery->whereIn('id_muctieu', $mucTieuIds);
                } else {
                    $thuTinQuery->whereRaw('1 = 0');
                }
            }

            $data[] = $thuTinQuery->count();
        }
        return $data;
    }
}
