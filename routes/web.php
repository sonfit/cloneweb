<?php

use App\Filament\Pages\PublicCreateDangKy;
use App\Filament\Pages\PublicCreateTongHop;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/post', PublicCreateTongHop::class)
    ->name('post')
    ->middleware('web');

Route::get('/dk', PublicCreateDangKy::class)
    ->name('dang_ky')
    ->middleware('web');

Route::get('/clear', function () {
    $commands = [
        'optimize:clear',
        'cache:clear',
        'config:clear',
        'route:clear',
        'view:clear',
        'event:clear'
    ];

    foreach ($commands as $command) {
        Artisan::call($command);
        echo "Đã chạy: $command <br>";
    }

    Notification::make()
        ->title('Thành công!')
        ->body('✨ Hệ thống đã được dọn dẹp thành công! 🚀')
        ->success()
        ->send();

    return redirect()->back();

});
