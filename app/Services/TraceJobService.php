<?php

namespace App\Services;

use App\Models\TraceJob;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TraceJobService
{
    /**
     * Map payload key -> result array key used in JSON_EXTRACT.
     */
    public static function resultKeyForPayloadKey(string $key): string
    {
        $map = [
            'sdt' => 'SO_DIEN_THOAI',
            'cccd' => 'CCCD',
            'fb' => 'FB_UID',
        ];

        return $map[$key] ?? strtoupper($key);
    }

    /**
     * Tìm job đã DONE có result chứa bất kỳ giá trị trong payload.
     * Nếu tìm thấy: merge payload (nếu khác) và trả về job đó.
     */
    public static function findExistingJobByPayload(array $payload): ?TraceJob
    {
        foreach ($payload as $key => $value) {
            if (!$value) {
                continue;
            }

            $resultKey = self::resultKeyForPayloadKey($key);
            $existingJob = TraceJob::where('status', 'done')
                ->whereNotNull('result')
                ->where(function ($query) use ($resultKey, $key, $value) {
                    $query->whereRaw("JSON_EXTRACT(result, '$[*].{$resultKey}') LIKE ?", ["%\"{$value}\"%"])
                        ->orWhereRaw("JSON_EXTRACT(payload, '$.\"{$key}\"') LIKE ?", ["%\"{$value}\"%"]);
                })
                ->orderBy('updated_at', 'desc')
                ->first();

            if ($existingJob) {
                self::mergePayloadIntoJobIfChanged($existingJob, $payload);
                return $existingJob;
            }
        }

        return null;
    }

    /**
     * Gộp payload vào job nếu có thay đổi.
     */

    public static function mergePayloadIntoJobIfChanged(TraceJob $job, array $payload): void
    {
        // Giải mã payload hiện có nếu là chuỗi JSON
        $currentPayload = is_array($job->payload)
            ? $job->payload
            : (json_decode($job->payload, true) ?: []);



        // Loại bỏ các giá trị null hoặc rỗng từ payload mới
        $filteredPayload = array_filter($payload, fn($v) => !is_null($v) && $v !== '');


        // Gộp hai mảng lại (ưu tiên payload mới)
        $mergedPayload = array_merge($currentPayload, $filteredPayload);



        // Chỉ update khi thực sự khác
        if ($mergedPayload !== $currentPayload) {
            $job->update(['payload' => $mergedPayload]);
        }
    }

    /**
     * Tạo job mới ở trạng thái pending với payload đã lọc rỗng.
     */
    public static function createPendingJob(array $payload): TraceJob
    {
        return TraceJob::create([
            'payload' => array_filter($payload),
            'status' => 'pending',
            'result' => null,
            'claimed_at' => null,
        ]);
    }

    public static function checkUsageLimit(int $maxLimit = 3): ?array
    {
        $userId = Auth::id();
        if (!$userId) {
            // Để route/controller tự xử lý chưa đăng nhập
            return null;
        }

        $today = now()->toDateString();

        $usage = DB::table('trace_usage')
            ->where('user_id', $userId)
            ->where('usage_date', $today)
            ->first();

        $currentCount = $usage->count ?? 0;

        if ($currentCount >= $maxLimit) {
            return [
                'message' => "Đã quá lượt xem ({$maxLimit}) trong ngày, cần quay lại vào ngày hôm sau. Hoặc liên hệ admin.",
            ];
        }

        return null;
    }

    public static function incrementUsage(): void
    {
        $userId = Auth::id();
        if (!$userId) {
            return;
        }

        $today = now()->toDateString();

        DB::table('trace_usage')->updateOrInsert(
            ['user_id' => $userId, 'usage_date' => $today],
            [
                'count' => DB::raw('count + 1'),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    /**
     * Điểm vào duy nhất: kiểm tra payload, giới hạn lượt dùng, tìm job cũ hoặc tạo job mới,
     * tăng lượt sử dụng. Trả về kết quả thống nhất.
     */
    public static function searchOrCreate(array $payload, int $maxLimit = 3): array
    {
        $payload = array_filter($payload, fn($v) => !is_null($v) && $v !== '');
        if (empty($payload)) {
            return [
                'ok' => false,
                'status' => 'failed',
                'job' => null,
                'result' => null,
                'message' => 'Thiếu tham số. Cần tối thiểu một trong: sdt, cccd, fb.',
                'http' => 400,
            ];
        }

        if ($limit = self::checkUsageLimit($maxLimit)) {
            return [
                'ok' => false,
                'status' => 'failed',
                'job' => null,
                'result' => null,
                'message' => $limit['message'] ?? 'Đã quá giới hạn sử dụng',
                'http' => 429,
            ];
        }

        $existing = self::findExistingJobByPayload($payload);
        if ($existing) {
            self::incrementUsage();
            return [
                'ok' => true,
                'status' => 'done',
                'job' => $existing,
                'result' => $existing->result ?: 'Không có dữ liệu',
                'message' => null,
                'http' => 200,
            ];
        }

        $job = self::createPendingJob($payload);
        self::incrementUsage();
        return [
            'ok' => true,
            'status' => 'processing',
            'job' => $job,
            'result' => null,
            'message' => 'Đã tạo job tra cứu, vui lòng chờ kết quả...',
            'http' => 202,
        ];
    }
}

