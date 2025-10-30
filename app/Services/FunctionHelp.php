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
    public static function chamDiemTuKhoa(?string $contents_text): array
    {
        if (!$contents_text) {
            return ['diem' => 0, 'tag_ids' => []];
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

        return [
            'diem'    => $tongDiem,
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
            return 'gray';
        } elseif ($diffHours > 2) {
            return 'info';
        } else {
            return 'success';
        }
    }

    /**
     * Quy đổi điểm sang level (1-5)
     *
     * @param int $diem Điểm cần quy đổi
     * @return int Level từ 1-5
     */
    public static function diemToLevel(int $diem): int
    {
        return match (true) {
            $diem >= 100 => 5,
            $diem >= 70  => 4,
            $diem >= 40  => 3,
            $diem >= 20  => 2,
            default      => 1,
        };
    }

    /**
     * Trả về màu badge cho từng level
     *
     * @param int $level Level từ 1-5
     * @return string Màu badge (gray, info, success, warning, danger)
     */
    public static function levelBadgeColor(int $level): string
    {
        return match ($level) {
            1 => 'gray',
            2 => 'info',
            3 => 'success',
            4 => 'warning',
            5 => 'danger',
            default => 'gray',
        };
    }
}
