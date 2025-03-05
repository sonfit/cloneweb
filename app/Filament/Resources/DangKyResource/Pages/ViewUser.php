<?php

namespace App\Filament\Resources\DangKyResource\Pages;

use App\Filament\Resources\DangKyResource;
use App\Models\DangKy;
use App\Models\User;
use Filament\Tables\Table;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables;


class ViewUser extends ListRecords
{
    protected static string $resource = DangKyResource::class;
    public $user_id;
    public ?User $user = null;

    public function getTitle(): string
    {
        return 'Báo Cáo Chi tiết Đăng Ký Xe' . ($this->user ? ' - ' . $this->user->name_full : '');
    }

    public function mount(): void
    {
        parent::mount();
        $this->user_id = request()->route('record');
        $this->user = User::find($this->user_id);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn() => $this->getDangKyQuery()) // Không dùng static
            ->defaultSort('created_at', 'asc')
            ->striped()
            ->columns([
                DangKyResource::createTotalGroup(),
                DangKyResource::createMucGroup(3),
                DangKyResource::createMucGroup(4),
                TextColumn::make('created_at')->date('d-m-Y')->sortable(),
                TextColumn::make('updated_at')->date('d-m-Y')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                DangKyResource::createDateFilter(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([]) ->recordUrl(null);
    }


    protected function getDangKyQuery()
    {
        return DangKy::query()
            ->where('dang_kies.user_id', $this->user_id);
    }
}
