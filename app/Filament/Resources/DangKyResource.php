<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DangKyResource\Pages;
use App\Models\DangKy;
use App\Models\User;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Carbon\Carbon;
use Filament\Tables\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;


class DangKyResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = DangKy::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Báo cáo đăng ký xe';
    protected static ?string $modelLabel = 'Báo cáo đăng ký xe';
    protected static ?string $slug = 'dang-ky';

    protected $listeners = ['refreshTable' => '$refresh'];


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Mức 3')
                    ->schema([
                        Grid::make()
                            ->columns([
                                'default' => 3,
                                'sm' => 3,
                                'md' => 3,
                                'lg' => 3,
                                'xl' => 3,
                                '2xl' => 3,
                            ])
                            ->schema([
                                Forms\Components\TextInput::make('oto_muc_3')
                                    ->label('Ô tô')
                                    ->required()
                                    ->numeric(),

                                Forms\Components\TextInput::make('xe_may_muc_3')
                                    ->label('Xe Máy')
                                    ->required()
                                    ->numeric(),

                                Forms\Components\TextInput::make('xe_may_dien_muc_3')
                                    ->label('Xe Điện')
                                    ->required()
                                    ->numeric(),
                            ]),
                    ]),

                Section::make('Mức 4')
                    ->schema([
                        Grid::make()
                            ->columns([
                                'default' => 3,
                                'sm' => 3,
                                'md' => 3,
                                'lg' => 3,
                                'xl' => 3,
                                '2xl' => 3,
                            ])
                            ->schema([
                                Forms\Components\TextInput::make('oto_muc_4')
                                    ->label('Ô tô')
                                    ->required()
                                    ->numeric(),

                                Forms\Components\TextInput::make('xe_may_muc_4')
                                    ->label('Xe Máy')
                                    ->required()
                                    ->numeric(),

                                Forms\Components\TextInput::make('xe_may_dien_muc_4')
                                    ->label('Xe Điện')
                                    ->required()
                                    ->numeric(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(fn() => self::getDangKyQuery())
            ->defaultSort('created_at', 'asc')
            ->striped()
            ->columns([
                TextColumn::make('name_full')
                    ->searchable()
                    ->sortable()
                    ->label('Tên Đơn vị'),
                self::createTotalGroup(),
                self::createMucGroup(3),
                self::createMucGroup(4),

            ])
            ->filters([
                self::createDateFilter()->defaultYesterday(),
            ])
            ->actions([
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->paginationPageOptions([20, 50, 500, 1000])
            ->defaultPaginationPageOption(500);
    }

    private static function vehicleColumns(): array
    {
        return [
            'oto' => 'Oto',
            'xe_may' => 'Xe Máy',
            'xe_may_dien' => 'Xe Điện',
        ];
    }
    // Hàm tạo cột tổng hồ sơ
    public static function createTotalGroup(): ColumnGroup
    {
        return ColumnGroup::make('Tổng hồ sơ')
            ->alignCenter()
            ->columns(
                array_map(
                    fn(string $type, string $label) => TextColumn::make("{$type}_total")
                        ->label($label)
                        ->getStateUsing(fn($record) => ($record->{"{$type}_muc_3"} ?? 0) +
                            ($record->{"{$type}_muc_4"} ?? 0)
                        )
                        ->formatStateUsing(fn($state) => "<strong><em>" . number_format($state, 0, ',', '.') . "</em></strong>") // Format number
                        ->alignRight()
                        ->summarize(
                            Sum::make()
                                ->label($label)
                                ->formatStateUsing(fn($state) => "<strong style='color: red;'>" . number_format($state, 0, ',', '.') . "</strong>")
                                ->html()

                        )
                        ->html()

                    ,

                    array_keys(self::vehicleColumns()),
                    self::vehicleColumns()
                )
            );
    }
    // Hàm tạo nhóm theo mức
    public static function createMucGroup(int $muc): ColumnGroup
    {
        return ColumnGroup::make("Mức $muc")
            ->alignCenter()
            ->columns(
                array_map(
                    fn(string $type, string $label) => TextColumn::make("{$type}_muc_{$muc}")
                        ->label($label)
                        ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.'))
                        ->alignRight()
                        ->summarize(Sum::make()
                            ->label($label.' '.$muc)
                            ->formatStateUsing(fn($state) => "<strong style='color: green;'>" . number_format($state, 0, ',', '.') . "</strong>")
                            ->html()
                        )
                    ,
                    array_keys(self::vehicleColumns()),
                    self::vehicleColumns()
                )
            );
    }
    //Bộ lọc ngày
    public static function createDateFilter(): DateRangeFilter
    {
        return DateRangeFilter::make('created_at')
            ->label('Chọn khoảng thời gian')
            ->query(function (Builder $query, array $data) {
                if (empty($data['created_at'])) {
                    return $query;
                }
                [$from, $to] = explode(' - ', $data['created_at']);
                $from = Carbon::createFromFormat('d/m/Y', trim($from))->startOfDay();
                $to = Carbon::createFromFormat('d/m/Y', trim($to))->endOfDay();
                return $query->whereBetween('dang_kies.created_at', [$from, $to]);
            })
            ->indicateUsing(function (array $data): ?string {
                if (empty($data['created_at'])) {
                    return null;
                }
                [$from, $to] = explode(' - ', $data['created_at']);
                return "Từ ngày $from đến $to";
            });
    }

    private static function getDangKyQuery()
    {
        $user = auth()->user(); // Lấy user đang đăng nhập

        $query = \App\Models\DangKy::query()
            ->join('users', 'dang_kies.user_id', '=', 'users.id');

        // Kiểm tra nếu user có tất cả quyền trong resource
        $allPermissions = ['view', 'view_any', 'create', 'update', 'delete', 'delete_any'];
        $hasFullAccess = collect($allPermissions)->every(fn($perm) => $user->can($perm));
        $hasAdminRole = $user->hasRole(['admin', 'super_admin']);

        // Nếu user không có full quyền và không phải admin, chỉ hiển thị dữ liệu của họ
        if (!$hasFullAccess && !$hasAdminRole) {
            $query->where('dang_kies.user_id', $user->id);
        }
        $query->select([
            'users.name_full as name_full',
            'dang_kies.user_id',
            DB::raw('MIN(dang_kies.user_id) as id'),
            DB::raw('SUM(dang_kies.oto_muc_3) as oto_muc_3'),
            DB::raw('SUM(dang_kies.xe_may_muc_3) as xe_may_muc_3'),
            DB::raw('SUM(dang_kies.oto_muc_4) as oto_muc_4'),
            DB::raw('SUM(dang_kies.xe_may_muc_4) as xe_may_muc_4'),
            DB::raw('SUM(dang_kies.xe_may_dien_muc_3) as xe_may_dien_muc_3'),
            DB::raw('SUM(dang_kies.xe_may_dien_muc_4) as xe_may_dien_muc_4'),

            // Tạo cột tổng hợp mới
            DB::raw('(SUM(dang_kies.oto_muc_3) + SUM(dang_kies.oto_muc_4)) as oto_total'),
            DB::raw('(SUM(dang_kies.xe_may_muc_3) + SUM(dang_kies.xe_may_muc_4)) as xe_may_total'),
            DB::raw('(SUM(dang_kies.xe_may_dien_muc_3) + SUM(dang_kies.xe_may_dien_muc_4)) as xe_may_dien_total'),

            DB::raw('MIN(dang_kies.created_at) as created_at'),
            DB::raw('MAX(dang_kies.updated_at) as updated_at')
        ])
            ->groupBy('dang_kies.user_id', 'users.name_full');
        return $query;
    }

    public static function getUsersWithoutRecords(?array $filters = [])
    {
        $decodedFilters = json_decode($filters['components'][0]['snapshot'] ?? '{}', true);
        $tableFilters = data_get($decodedFilters, 'data.tableFilters', []);
        $createdAtFilter = data_get($tableFilters, '0.created_at.0.created_at', null);

        if (!$createdAtFilter) {
            // Nếu không có, mặc định là hôm qua
            $from = Carbon::yesterday()->startOfDay();
            $to = Carbon::yesterday()->endOfDay();
        } else {
            // Nếu có, xử lý chuỗi ngày tháng
            [$from, $to] = explode(' - ', $createdAtFilter);
            $from = Carbon::createFromFormat('d/m/Y', trim($from))->startOfDay();
            $to = Carbon::createFromFormat('d/m/Y', trim($to))->endOfDay();
        }

        $users = User::where('groupid', '!=', 0)
        ->whereDoesntHave('dangkies', function ($query) use ($from, $to) {
            $query->whereBetween('created_at', [$from, $to]);
        })->get();
        return compact('users', 'from', 'to');

    }




    public static function getUsersWithoutRecords1()
    {
        return User::whereNotIn('id', function ($query) {
            $query->select('user_id')->from('dang_kies');
        })->get();
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDangKies::route('/'),
            'create' => Pages\CreateDangKy::route('/create'),
            'view' => Pages\ViewUser::route('/{record}/chi-tiet'),
            'edit' => Pages\EditDangKy::route('/{record}/edit'),
        ];
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
        ];
    }


    public static function shouldRegisterNavigation(): bool
    {
        return false; // Không hiển thị trong menu/sidebar
    }

    public static function canViewAny(): bool
    {
        return false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

//    public static function canCreate(): bool
//    {
//        return true;
//    }
//
//    public static function canViewAny(): bool
//    {
//        return true;
//    }
//
//    public static function canView(Model $record): bool
//    {
//        return true;
////        return auth()->user()->hasPermissionTo('view_dangky') || auth()->user()->id === $record->user_id;
//    }

}
