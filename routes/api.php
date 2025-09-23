<?php
use App\Filament\Api\ThuTinApiResource;
use Illuminate\Support\Facades\Route;


Route::middleware('api.key')->group(function () {
    ThuTinApiResource::routes();
    Route::post('/upload-files', [ThuTinApiResource::class, 'upload']);
    Route::get('/get-bot', [ThuTinApiResource::class, 'getBot']);
});
