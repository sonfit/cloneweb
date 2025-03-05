<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DangKyResource\Pages;
use App\Models\DangKy;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;


use Filament\Forms\Components\Toggle;

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
        $enableTotal = self::isEnableTotalActive(); // Lấy giá trị của enable_total

        return $table
            ->persistFiltersInSession()
            ->query(function (Builder $query) use ($enableTotal) {
                // Truy vấn mặc định
                $query = \App\Models\DangKy::query()
                    ->join('users', 'dang_kies.user_id', '=', 'users.id')
                    ->select([
                        'dang_kies.id',
                        'users.name_full as name_full',
                        'dang_kies.user_id',
                        'dang_kies.oto_muc_3',
                        'dang_kies.xe_may_muc_3',
                        'dang_kies.oto_muc_4',
                        'dang_kies.xe_may_muc_4',
                        'dang_kies.xe_may_dien_muc_3',
                        'dang_kies.xe_may_dien_muc_4',
                        'dang_kies.created_at',
                        'dang_kies.updated_at'
                    ])
                    ->whereNotNull('dang_kies.user_id');

                // Nếu filter tổng được bật, áp dụng SUM()
                if ($enableTotal) {
                    $query->select([
                        DB::raw('MIN(dang_kies.id) as id'),
                        'users.name_full as name_full',
                        'dang_kies.user_id',
                        DB::raw('SUM(dang_kies.oto_muc_3) as oto_muc_3'),
                        DB::raw('SUM(dang_kies.xe_may_muc_3) as xe_may_muc_3'),
                        DB::raw('SUM(dang_kies.oto_muc_4) as oto_muc_4'),
                        DB::raw('SUM(dang_kies.xe_may_muc_4) as xe_may_muc_4'),
                        DB::raw('SUM(dang_kies.xe_may_dien_muc_3) as xe_may_dien_muc_3'),
                        DB::raw('SUM(dang_kies.xe_may_dien_muc_4) as xe_may_dien_muc_4'),
                        DB::raw('MIN(dang_kies.created_at) as created_at'),
                        DB::raw('MAX(dang_kies.updated_at) as updated_at')
                    ])->groupBy('dang_kies.user_id');
                }

                return $query;
            })
            ->defaultSort('created_at', 'asc')
            ->striped()
            ->columns([
                TextColumn::make('user.name_full')
                    ->searchable()
                    ->sortable()
                    ->label('Tên Đơn vị'),
                self::createTotalGroup(),
                self::createMucGroup(3),
                self::createMucGroup(4),
                TextColumn::make('created_at')
                    ->date('d-m-Y')
                    ->sortable()
                    ->hidden(fn() => !$enableTotal), // Ẩn nếu enableTotal = false
                TextColumn::make('updated_at')
                    ->date('d-m-Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                self::createTotalCheckbox(),
                self::createDateFilter(),
            ])
            ->actions([
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->paginationPageOptions([20, 50, 500, 1000]);

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
    private static function createTotalGroup(): ColumnGroup
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

                        ->html()
                        ->alignCenter()
                        ->extraHeaderAttributes(['class' => 'text-center']),
                    array_keys(self::vehicleColumns()),
                    self::vehicleColumns()
                )
            );
    }
    // Hàm tạo nhóm theo mức
    private static function createMucGroup(int $muc): ColumnGroup
    {
        return ColumnGroup::make("Mức $muc")
            ->alignCenter()
            ->columns(
                array_map(
                    fn(string $type, string $label) => TextColumn::make("{$type}_muc_{$muc}")
                        ->label($label)
                        ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')), // Format number
                    array_keys(self::vehicleColumns()),
                    self::vehicleColumns()
                )
            );
    }
    // Bộ lọc ngày
    private static function createDateFilter(): DateRangeFilter
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

    // Thêm checkbox filter để bật/tắt chế độ tổng hợp
    private static function createTotalCheckbox(): Filter
    {
        return Filter::make('enable_total')
            ->label('Hiển thị tổng')
            ->toggle()->default(false);
    }
    private static function isEnableTotalActive(): bool
    {

//        return request()->input('tableFilters.enable_total.isActive', false);

        $filters = collect(request()->input('components.0.updates', []));
        $enableTotal = $filters->get('tableFilters.enable_total.isActive', false);
        return $enableTotal;
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
            'view' => Pages\ViewDangKy::route('/{record}'),
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

    public static function canCreate(): bool
    {
        return true;
    }

    public static function canViewAny(): bool
    {
        return true;
    }
}
