<?php

namespace App\Filament\Resources\TaskListResource\Pages;

use App\Filament\Resources\TaskListResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTaskLists extends ListRecords
{
    protected static string $resource = TaskListResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
