<?php

namespace App\Services;

use App\Models\Tag;
use Carbon\Carbon;

class FunctionHelp
{
    public static function chamDiemTuKhoa(?string $contents_text): array
    {
        if (!$contents_text) {
            return ['level' => 1, 'tag_ids' => []];
        }

        $noiDung = mb_strtolower(strip_tags($contents_text));
        $tuKhoaList = Tag::all(['id', 'tag', 'diem']);

        $tongDiem   = 0;
        $matchedIds = [];

        foreach ($tuKhoaList as $tuKhoa) {
            $pattern = '/' . preg_quote(mb_strtolower($tuKhoa->tag), '/') . '/u';
            if (preg_match($pattern, $noiDung)) {
                $tongDiem    += $tuKhoa->diem;
                $matchedIds[] = $tuKhoa->id;
            }
        }

        // Map điểm -> level
        $level = match (true) {
            $tongDiem >= 100 => 5,
            $tongDiem >= 70  => 4,
            $tongDiem >= 40  => 3,
            $tongDiem >= 20  => 2,
            default          => 1,
        };
        return [
            'level'   => $level,
            'tag_ids' => $matchedIds,
        ];
    }

    public static function timeBadgeColor($time): string
    {
        $time = Carbon::parse($time);
        $diffHours = abs(Carbon::now()->diffInHours($time)); // Dương cho quá khứ

        if ($diffHours > 5) {
            return 'danger';
        } elseif ($diffHours > 4) {
            return 'warning';
        } elseif ($diffHours > 3) {
            return 'primary';
        } elseif ($diffHours > 2) {
            return 'info';
        } else {
            return 'success';
        }
    }
}
