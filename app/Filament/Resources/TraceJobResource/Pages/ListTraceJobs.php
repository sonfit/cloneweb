<?php

namespace App\Filament\Resources\TraceJobResource\Pages;

use App\Filament\Resources\TraceJobResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTraceJobs extends ListRecords
{
    protected static string $resource = TraceJobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
