<?php

namespace App\Services;

use App\Models\Tag;
use Carbon\Carbon;

class FunctionHelp
{

    public static function isAdminUser($user = null): bool
    {
        $user = $user ?? auth()->user();
        return $user?->hasAnyRole(['admin', 'super_admin']) ?? false;
    }

    public static function isUser($user = null): bool
    {
        $user = $user ?? auth()->user();
        return $user?->hasAnyRole(['user']) ?? false;
    }

}
