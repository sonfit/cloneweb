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
                ->label('Đơn vị chưa báo cáo')
                ->color('danger')
                ->modalHeading('Danh sách đơn vị chưa báo cáo')
                ->modalSubmitAction(false)
                ->modalContent(fn() => view('filament.modals.missing-users', [
                    'data' => DangKyResource::getUsersWithoutRecords(request()->all())
                ]))
        ];
    }

    public function getTableRecordKey(Model $record): string
    {
        return (string) ($record->user_id ?? uniqid());
    }




}
