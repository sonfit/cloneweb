<?php

namespace App\Filament\Pages;

use App\Filament\Resources\DangKyResource;
use App\Filament\Resources\DangKyResource\Pages\CreateDangKy;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Panel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Request;


class PublicCreateDangKy extends CreateDangKy
{

    protected static string $resource = DangKyResource::class;
    public ?array $data = [];
    public User|null $user = null; // Thêm biến để lưu User

    public static function getRouteMiddleware(Panel $panel): array
    {
        return ['web'];
    }

    public function getTitle(): string
    {
        return 'Báo Cáo Đăng Ký Xe' . ($this->user ? ' - ' . $this->user->name_full : '');
    }


    public function mount(): void
    {
        // Ẩn các phần giao diện không cần thiết trong Filament
        Filament::getCurrentPanel()->navigation(false)
            ->breadcrumbs(false)
            ->topNavigation(false)
            ->sidebarWidth('0px');

        // Lấy giá trị 'name' từ URL
        $this->name = Request::query('name');

        // Kiểm tra nếu 'name' không có trong URL
        if (!$this->name) {
            abort(Response::HTTP_FORBIDDEN, 'Vui lòng cung cấp tên người dùng!');
        }

        $currentHour = now()->hour;
        if ($currentHour < 17 || $currentHour > 23) {
            abort(Response::HTTP_FORBIDDEN, 'Truy cập chỉ được phép từ 17h đến 23h59.');
        }

        // Tìm người dùng theo 'name' và lưu vào $this->user
        $this->user = User::where('name', $this->name)->first();

        // Kiểm tra nếu người dùng không tồn tại
        if (!$this->user) {
            abort(Response::HTTP_FORBIDDEN, 'Người dùng không tồn tại!');
        }

        // Kiểm tra xem người dùng đã đăng ký hôm nay chưa
        $hasRecordToday = $this->user->dangkies()->whereDate('created_at', today())->first();

        if ($hasRecordToday) {
            abort(Response::HTTP_FORBIDDEN, "Đã tồn tại bản ghi vào lúc {$hasRecordToday->created_at->format('H:i:s d-m-Y')}.\nNếu sai sót, vui lòng liên hệ theo số điện thoại: ...");
        }

        // Điền dữ liệu vào form nếu không có lỗi
        $this->form->fill();
    }

    public static function canView(): bool
    {
        return true;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $record = parent::handleRecordCreation($data);

        // Hiển thị thông báo thành công
        Notification::make()
            ->title('Thêm mới thành công!')
            ->success()
            ->send();

        $this->form->fill($data)->disabled();
        $this->halt();

        return $record;
    }


}
