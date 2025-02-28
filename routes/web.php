<?php

use App\Filament\Pages\PublicCreateDangKy;
use App\Filament\Pages\PublicCreateTongHop;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/post', PublicCreateTongHop::class)
    ->name('post')
    ->middleware('web');

Route::get('/dk', PublicCreateDangKy::class)
    ->name('post')
    ->middleware('web');

