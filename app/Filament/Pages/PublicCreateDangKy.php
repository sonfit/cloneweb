<?php

namespace App\Filament\Pages;

use App\Filament\Resources\DangKyResource;
use App\Filament\Resources\DangKyResource\Pages\CreateDangKy;
use App\Models\User;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Panel;

class PublicCreateDangKy extends CreateDangKy
{
    protected static string $resource = DangKyResource::class;
    public ?array $data = [];
    public static function getRouteMiddleware(Panel $panel): array
    {
        return ['web'];
    }

    public function mount(): void
    {
        $currentHour = now()->hour;
        if ($currentHour < 17 || $currentHour > 23) {
            die('Truy cập chỉ được phép từ 17h đến 23h59.');
        }
        $this->form->fill();
        $this->name = request()->query('name');
        if ($this->name) {
            $user = User::where('name', $this->name)->first();
            if (!$user) {
                die('Người dùng không tồn tại!');
            }
            $hasRecordToday = $user->dangKys()
                ->whereDate('created_at', today())
                ->first();

            if ($hasRecordToday) {
                echo "<pre>";
                echo("Đã tồn tại bản ghi vào lúc " . $hasRecordToday->created_at->format('H:i:s d-m-Y') . "\nNếu sai sót, vui lòng liên hệ theo số điện thoại: ...\n");
                echo "</pre>";
                exit();
            }
        }else{
            die('Vui lòng cung cấp tên người dùng!');
        }
    }

    public static function canView(): bool
    {
        return true;
    }

}
