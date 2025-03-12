<?php

namespace App\Filament\Resources\DangKyResource\Pages;

use App\Filament\Resources\DangKyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;


class ListDangKies extends ListRecords
{
    protected static string $resource = DangKyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('showMissingUsers')
                ->label('User không có bản ghi')
                ->color('danger')
                ->modalHeading('Danh sách đơn vi chưa báo cáo')
                ->modalSubmitAction(false)
                ->modalContent(fn() => view('filament.modals.missing-users', [
                    'users' => DangKyResource::getUsersWithoutRecords(request()->query('tableFilters', []))
                ]))->disabled()
        ];
    }

    public function getTableRecordKey(Model $record): string
    {
        return (string) ($record->user_id ?? uniqid());
    }




}
