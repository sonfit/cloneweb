<?php

namespace App\Services;

use App\Models\ThuTin;
use App\Models\Tag;

class TinhDiemTuKhoa
{
    public static function chamDiem(?string $contents_text): array
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
}
