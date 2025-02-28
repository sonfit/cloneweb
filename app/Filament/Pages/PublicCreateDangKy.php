<?php

namespace App\Filament\Pages;

use App\Filament\Resources\DangKyResource;
use App\Filament\Resources\DangKyResource\Pages\CreateDangKy;
use App\Models\User;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Panel;

class PublicCreateDangKy extends CreateDangKy
{
    protected static string $resource = DangKyResource::class;
    public ?array $data = [];
    public static function getRouteMiddleware(Panel $panel): array
    {
        return ['web'];
    }

    public function mount(): void
    {
        $this->form->fill();
        $this->name = request()->query('name');

        $name = $this->name;
        if ($name) {
            $user = User::where('name', $name)->first();
            dd($user->dangKys->where('created_at', '=', Carbon::now()));
            if(!$user){
                abort(403);
            }
            $bao_cao_today = $name;
            dd($bao_cao_today);
        }
        abort_unless(static::canView(), 403);
    }

    public static function canView(): bool
    {
        return true;
    }

}
