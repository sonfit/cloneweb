<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TraceJobResource\Pages;
use App\Models\TraceJob;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TraceJobResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = TraceJob::class;

    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass';

    protected static ?string $navigationLabel = 'Tra cứu';

    protected static ?string $modelLabel = 'Job Tra cứu';

    protected static ?string $pluralModelLabel = 'Jobs Tra cứu';
    protected static ?string $slug = 'tra-cuu';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Thông tin tra cứu')
                    ->schema([
                        Forms\Components\TextInput::make('payload_sdt')
                            ->label('Số điện thoại')
                            ->placeholder('Nhập số điện thoại (VD: 0912345678)')
                            ->helperText('Nhập số điện thoại cần tra cứu'),
                        Forms\Components\TextInput::make('payload_cccd')
                            ->label('CCCD/CMND')
                            ->placeholder('Nhập số CCCD (VD: 123456789012)')
                            ->helperText('Nhập số CCCD hoặc CMND cần tra cứu'),
                        Forms\Components\TextInput::make('payload_fb')
                            ->label('Facebook UID')
                            ->placeholder('Nhập Facebook UID (VD: 1000123456789)')
                            ->helperText('Nhập Facebook UID cần tra cứu'),
                    ])
                    ->columns(3),
                Forms\Components\Section::make('Kết quả')
                    ->schema([
                        Forms\Components\Textarea::make('formatted_result')
                            ->label('Kết quả tra cứu')
                            ->rows(8)
                            ->disabled()
                            ->helperText('Kết quả sẽ hiển thị ở đây khi tra cứu hoàn thành'),
                    ])
                    ->collapsible(),
                Forms\Components\Section::make('Trạng thái')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Trạng thái')
                            ->options([
                                'pending' => 'Chờ xử lý',
                                'processing' => 'Đang xử lý',
                                'done' => 'Hoàn thành',
                                'failed' => 'Thất bại',
                            ])
                            ->default('pending')
                            ->required(),
                        Forms\Components\DateTimePicker::make('claimed_at')
                            ->label('Thời gian claim')
                            ->disabled(),
                    ])
                    ->columns(2),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payload_sdt')
                    ->label('SĐT'),
                Tables\Columns\TextColumn::make('payload_cccd')
                    ->label('CCCD'),
                Tables\Columns\TextColumn::make('payload_fb')
                    ->label('Facebook'),

                Tables\Columns\TextColumn::make('result')
                    ->label('Kết quả')
                    ->formatStateUsing(function ($state) {

                        if (empty($state)) {
                            return 'Chưa có kết quả';
                        }
                        // Xử lý nếu $state là string (JSON)
                        if (is_string($state)) {
                            $state = '[' . $state . ']';
                            $state = json_decode($state, true);
                        }

                        $formattedResults = [];
                        foreach ($state as $item) {
                            $name = $item['CHU_SO_HUU'] ?? 'Không có';
                            $sdt = $item['SO_DIEN_THOAI'] ?? 'Không có';
                            $cccd = $item['CCCD'] ?? 'Không có';
                            $diachi = $item['DIA_CHI'] ?? 'Không có';
                            $fbName = $item['FB_NAME'] ?? 'Không có';
                            $fbUid = $item['FB_UID'] ?? 'Không có';

                            $result = "<b>{$name}</b> (SĐT: {$sdt}, CCCD: {$cccd})<br>";
                            $result .= "<b>Địa chỉ:</b> {$diachi}<br>";

                            if ($fbUid !== 'Không có' && $fbUid !== '') {
                                $result .= "<b>Facebook:</b> <a href='https://facebook.com/{$fbUid}' target='_blank'>{$fbName}</a>";
                            } else {
                                $result .= "<b>Facebook:</b> {$fbName}";
                            }

                            $formattedResults[] = $result;
                        }

                        return implode("<br><br>", $formattedResults);
                    })
                    ->html()
                    ->wrap()
                    ->searchable(false),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Trạng thái')
                    ->sortable()
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'processing',
                        'success' => 'done',
                        'danger' => 'failed',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tạo lúc')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Cập nhật lúc')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'done' => 'Done',
                        'failed' => 'Failed',
                    ]),
            ])
            ->actions([

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListTraceJobs::route('/'),
            'create' => Pages\CreateTraceJob::route('/create'),
            'view' => Pages\ViewTraceJob::route('/{record}'),
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
