<?php

use App\Filament\Pages\PublicCreateDangKy;
use App\Filament\Pages\PublicCreateTongHop;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\TraceJob;
use App\Services\TraceJobService;

use Illuminate\Support\Facades\File;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use App\Filament\Widgets;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function() {
    return redirect('/admin/login');
})->name('login');

//Route::get('/post', PublicCreateTongHop::class)
//    ->name('post')
//    ->middleware('web');
//
//Route::get('/dk', PublicCreateDangKy::class)
//    ->name('dang_ky')
//    ->middleware('web');



Route::prefix('/tra-cuu')->middleware(['web'])->group(function () {

    $requireLogin = function (Request $request) {
        if (!Auth::check()) {
            return view('trace', [
                'query' => $request->only(['sdt', 'cccd', 'fb']),
                'error' => 'Bạn cần đăng nhập để sử dụng chức năng tra cứu.',
            ]);
        }


        return null;
    };

    // Trang giao diện chính
    Route::get('/', function (Request $request) use ($requireLogin) {
        if ($response = $requireLogin($request)) {
            return $response;
        }

        return view('trace', [
            'query' => $request->only(['sdt', 'cccd', 'fb']),
        ]);
    });

    // Nhóm API (vẫn trong /tra-cuu)
    Route::prefix('/api')->group(function () use ($requireLogin) {

        // API tra cứu theo thông tin đầu vào
        Route::get('/', function (Request $request) use ($requireLogin) {
            if ($response = $requireLogin($request)) {

                // API thì nên trả JSON thay vì view
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Bạn cần đăng nhập để sử dụng chức năng tra cứu.',
                ], 401);
            }

            $out = TraceJobService::searchOrCreate($request->only(['sdt','cccd','fb']), 3);
            return response()->json([
                'status' => $out['status'],
                'job_id' => $out['job']->id ?? null,
                'result' => $out['result'] ?? null,
                'message' => $out['message'] ?? null,
            ], $out['http']);
        });

        // API lấy kết quả theo job ID
        Route::get('/{id}', function (Request $request, int $id) use ($requireLogin) {
            if ($response = $requireLogin($request)) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Bạn cần đăng nhập để sử dụng chức năng tra cứu.',
                ], 401);
            }

            $job = TraceJob::find($id);
            if (!$job) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Không tìm thấy job.',
                ], 404);
            }

            $result = $job->result;
            if (empty($result)) {
                $result = 'Không có dữ liệu';
            }

            return response()->json([
                'status' => $job->status,
                'job_id' => $job->id,
                'result' => $result,
                'message' => in_array($job->status, ['pending', 'processing'])
                    ? 'Đang xử lý dữ liệu, vui lòng chờ...'
                    : null,
            ]);
        });
    });
});





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
