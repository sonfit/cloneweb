<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BotResource\Pages;
use App\Filament\Resources\BotResource\RelationManagers;
use App\Models\Bot;
use App\Models\MucTieu;
use App\Services\FunctionHelp;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;

class BotResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Bot::class;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';
    protected static ?string $navigationGroup = 'Tổng Hợp';
    protected static ?string $navigationLabel = 'Bot';
    protected static ?string $modelLabel = 'Bot';
    protected static ?string $slug = 'bot';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Thông tin cơ bản')
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
                                Forms\Components\TextInput::make('ten_bot')
                                    ->label('Tên Bot')
                                    ->required()
                                    ->maxLength(150)
                                    ->unique(ignoreRecord: true),

                                Forms\Components\TextInput::make('loai_bot')
                                    ->label('Loại Bot')
                                    ->maxLength(150)
                                    ->required(),

                                Forms\Components\TextInput::make('lenh_bot')
                                    ->label('Lệnh Bot')
                                    ->maxLength(150)
                                    ->required(),
                            ]),
                    ]),




                Section::make('Mục tiêu')
                    ->description('Chọn các mục tiêu mà bot sẽ theo dõi')
                    ->schema([
                        Forms\Components\Tabs::make('Mục tiêu')
                            ->contained()
                            ->tabs(function () {
                                $mucTieus = MucTieu::all()->groupBy('type');
                                $tabs = [];

                                foreach ($mucTieus as $type => $items) {
                                    $typeLabel = Lang::has('options.type.' . $type)
                                        ? trans('options.type.' . $type)
                                        : 'Không rõ';

                                    $options = $items->mapWithKeys(function ($item) {
                                        return [$item->id => $item->name ?? 'Không rõ'];
                                    })->toArray();

                                    $tabs[] = Forms\Components\Tabs\Tab::make($typeLabel)
                                        ->badge(count($options))
                                        ->schema([
                                            Forms\Components\CheckboxList::make('mucTieus')
                                                ->label('')
                                                ->relationship('mucTieus', 'name')
                                                ->options($options)
                                                ->searchable()
                                                ->columns(3)
                                                ->gridDirection('row')
                                                ->bulkToggleable(),
                                        ]);
                                }

                                return $tabs;
                            })
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(false),

                Section::make('Ghi chú')
                    ->description('Thêm ghi chú hoặc mô tả chi tiết về bot')
                    ->schema([
                        Forms\Components\Textarea::make('ghi_chu')
                            ->label('Ghi chú')
                            ->maxLength(1000)
                            ->rows(4)
                            ->placeholder('Nhập ghi chú về bot...')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('STT')
                    ->rowIndex(),

                Tables\Columns\TextColumn::make('ten_bot')
                    ->label('Tên Bot')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('loai_bot')
                    ->label('Loại Bot')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TagsColumn::make('mucTieus.name')
                    ->label('Mục tiêu')
                    ->getStateUsing(function ($record) {
                        $mucTieus = $record->mucTieus->unique('name')->pluck('name'); // Lấy danh sách tên mục tiêu, loại bỏ trùng lặp
                        $limit = 4;

                        if ($mucTieus->count() > $limit) {
                            $extraCount = $mucTieus->count() - $limit;
                            return $mucTieus->take($limit)->push("+{$extraCount} mục tiêu khác");
                        }
                        return $mucTieus;
                    })
                    ->separator(', ')
                    ->badge()
                    ->sortable(query: function ($query, $direction) {
                        return $query->withCount('mucTieus')->orderBy('mucTieus_count', $direction);
                    })

                    ->color(function ($record, $state) {
                        $colors = ['primary', 'success', 'info', 'danger', 'gray'];
                        $index = $record->mucTieus->search(function ($item) use ($state) {
                                return in_array($item->name, (array) $state);
                            }) % count($colors); // Lấy chỉ số dựa trên vị trí mục tiêu, lặp lại nếu vượt quá
                        return $colors[$index];
                    }),

                Tables\Columns\TextColumn::make('time_crawl')
                    ->label('Lần bot truy cập')
                    ->dateTime('H:i:s d/m/Y')
                    ->sortable()
                    ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->format('H:i:s d/m/Y'))
                    ->color(fn($state) => FunctionHelp::timeBadgeColor($state)) // Đảm bảo $state là giá trị gốc
                    ->badge(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListBots::route('/'),
            'create' => Pages\CreateBot::route('/create'),
            'view' => Pages\ViewBot::route('/{record}'),
            'edit' => Pages\EditBot::route('/{record}/edit'),
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
}
