<?php
use App\Filament\Api\ThuTinApiResource;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AgentController;


Route::middleware('api.key')->group(function () {
    ThuTinApiResource::routes();
    Route::post('/upload-files', [ThuTinApiResource::class, 'upload']);
    Route::get('/get-bot', [ThuTinApiResource::class, 'getBot']);
});

// Endpoint cho Agent (máy A) – dùng Bearer token
Route::get('/agent/jobs', [AgentController::class, 'pending']);
Route::patch('/agent/jobs/{id}/claim', [AgentController::class, 'claim']);
Route::post('/agent/jobs/{id}/result', [AgentController::class, 'result']);
