<?php

namespace App\Filament\Pages;

use App\Filament\Resources\TongHopResource;
use App\Filament\Resources\TongHopResource\Pages\CreateTongHop;
use Filament\Facades\Filament;
use Filament\Panel;

class PublicCreateTongHop extends CreateTongHop
{
    protected static string $resource = TongHopResource::class;
    public ?array $data = [];
    public static function getRouteMiddleware(Panel $panel): array
    {
        return ['web'];
    }

    public function mount(): void
    {
        Filament::getCurrentPanel()->navigation(false)
            ->breadcrumbs(false)
            ->topNavigation(false)
            ->sidebarWidth('0px');
        $this->form->fill();
        $this->name = request()->query('name');
        abort_unless(static::canView(), 403);
    }

    public static function canView(): bool
    {
        return true;
    }

}
