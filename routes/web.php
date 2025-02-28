<?php

use App\Filament\Resources\TongHopResource\Pages\CreateTongHop;
use App\Livewire\PublicTongHopForm;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/post', function () {
    return redirect()->route('filament.admin.resources.tong-hops.create');
})->name('post')
    ->middleware(['web']);

Route::get('/admin/resources/tong-hops/create', CreateTongHop::class)
    ->name('filament.admin.resources.tong-hops.create')
    ->middleware(['web']);
