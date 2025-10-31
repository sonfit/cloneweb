<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CloneProxyController;

//Route::get('/', function () {
//    return view('welcome');
//});

// Nếu cần truy cập dashboard hoặc filament admin:
// Route::middleware(["auth", "verified"])->group(function () {
//     Route::get('/dashboard', function () {
//         return view('dashboard');
//     })->name('dashboard');
// });

//require __DIR__.'/auth.php';

Route::any('/{any}', [CloneProxyController::class, 'handle'])->where('any', '.*');
