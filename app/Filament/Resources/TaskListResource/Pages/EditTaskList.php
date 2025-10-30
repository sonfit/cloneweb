<?php

namespace App\Filament\Resources\TaskListResource\Pages;

use App\Filament\Resources\TaskListResource;
use App\Services\FunctionHelp;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTaskList extends EditRecord
{
    protected static string $resource = TaskListResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (!FunctionHelp::isAdminUser()) {
            unset($data['user_id']);
        }
        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
