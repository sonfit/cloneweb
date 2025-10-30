<?php

namespace App\Filament\Resources\TaskListResource\Pages;

use App\Filament\Resources\TaskListResource;
use App\Services\FunctionHelp;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTaskList extends CreateRecord
{
    protected static string $resource = TaskListResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (!FunctionHelp::isAdminUser()) {
            $data['user_id'] = auth()->id();
        }
        return $data;
    }
}
