<?php

namespace App\Filament\Resources\DangKyResource\Pages;

use App\Filament\Resources\DangKyResource;
use App\Models\DangKy;
use App\Models\User;
use Filament\Tables\Table;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables;
use Illuminate\Support\Facades\DB;


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
                TextColumn::make('')
                    ->label(''),
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


    protected function getDangKyQuery1()
    {
        $query = DangKy::query()->where('dang_kies.user_id', $this->user_id);
        return $query;
    }

    protected function getDangKyQuery()
    {
        $query = DangKy::query()
            ->join('users', 'dang_kies.user_id', '=', 'users.id')
            ->select([
                'dang_kies.id',
                DB::raw('SUM(dang_kies.oto_muc_3) as oto_muc_3'),
                DB::raw('SUM(dang_kies.xe_may_muc_3) as xe_may_muc_3'),
                DB::raw('SUM(dang_kies.oto_muc_4) as oto_muc_4'),
                DB::raw('SUM(dang_kies.xe_may_muc_4) as xe_may_muc_4'),
                DB::raw('SUM(dang_kies.xe_may_dien_muc_3) as xe_may_dien_muc_3'),
                DB::raw('SUM(dang_kies.xe_may_dien_muc_4) as xe_may_dien_muc_4'),

                // Cột tổng hợp mới
                DB::raw('SUM(dang_kies.oto_muc_3 + dang_kies.oto_muc_4) as oto_total'),
                DB::raw('SUM(dang_kies.xe_may_muc_3 + dang_kies.xe_may_muc_4) as xe_may_total'),
                DB::raw('SUM(dang_kies.xe_may_dien_muc_3 + dang_kies.xe_may_dien_muc_4) as xe_may_dien_total'),

                DB::raw('MIN(dang_kies.created_at) as created_at'),
                DB::raw('MAX(dang_kies.updated_at) as updated_at')
            ])
            ->where('dang_kies.user_id', $this->user_id)
            ->groupBy('dang_kies.id'); // Bắt buộc để tránh lỗi

        return $query;
    }

}
