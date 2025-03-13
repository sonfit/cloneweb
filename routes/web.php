<?php

use App\Filament\Pages\PublicCreateDangKy;
use App\Filament\Pages\PublicCreateTongHop;
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

Route::get('/clear',function (){
    echo  Artisan::call('optimize:clear');
    echo  Artisan::call('cache:clear');
    echo  Artisan::call('config:cache');
    echo  Artisan::call('route:cache');
});
