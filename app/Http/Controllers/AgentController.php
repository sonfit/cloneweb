<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class AgentController extends Controller
{
    private function agentAuthorized(Request $request): bool
    {
        $token = $request->bearerToken();
        return $token === config('app.agent_token', 'your-secret-token');
    }

    public function pending(Request $request): JsonResponse
    {
        if (!$this->agentAuthorized($request)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $limit = (int) $request->query('limit', 1);
        $jobs = DB::table('trace_jobs')
            ->where('status', 'pending')
            ->orderBy('id')
            ->limit($limit)
            ->get(['id', 'payload', 'created_at']);

        // Convert to array to ensure proper JSON serialization
        return response()->json($jobs->toArray());
    }

    public function claim(Request $request, int $id): JsonResponse
    {
        if (!$this->agentAuthorized($request)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $updated = DB::table('trace_jobs')
            ->where('id', $id)
            ->where('status', 'pending')
            ->update([
                'status' => 'processing',
                'claimed_at' => now(),
                'updated_at' => now(),
            ]);

        if ($updated === 0) {
            return response()->json(['message' => 'Job không ở trạng thái pending'], 409);
        }

        return response()->json(['message' => 'claimed']);
    }

    public function result(Request $request, int $id): JsonResponse
    {
        if (!$this->agentAuthorized($request)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'status' => 'required|in:done,failed',
            'result' => 'nullable',
        ]);

        $job = DB::table('trace_jobs')->where('id', $id)->first(['id', 'payload']);

        // Cập nhật job hiện tại
        DB::table('trace_jobs')->where('id', $id)->update([
            'status' => $validated['status'],
            'result' => array_key_exists('result', $validated) ? json_encode($validated['result'], JSON_UNESCAPED_UNICODE) : null,
            'updated_at' => now(),
        ]);

        // Synchronize other jobs with the same payload (cùng sdt, cccd, hoặc fb)
        if ($job && $job->payload) {
            $payload = json_decode($job->payload, true);
            
            // Tìm tất cả job có cùng ít nhất 1 thông tin (sdt, cccd, fb)
            $syncQuery = DB::table('trace_jobs')
                ->where('id', '!=', $id)
                ->whereIn('status', ['pending', 'processing']);

            $conditions = [];
            $values = [];
            
            foreach ($payload as $key => $value) {
                if ($value !== null && $value !== '') {
                    $conditions[] = "JSON_EXTRACT(payload, '$.{$key}') = ?";
                    $values[] = $value;
                }
            }
            
            if (!empty($conditions)) {
                $whereClause = implode(' OR ', $conditions);
                $syncQuery->whereRaw($whereClause, $values);
                
                $updated = $syncQuery->update([
                    'status' => $validated['status'],
                    'result' => array_key_exists('result', $validated) ? json_encode($validated['result'], JSON_UNESCAPED_UNICODE) : null,
                    'updated_at' => now(),
                ]);
                
                \Log::info("Synced {$updated} jobs with same payload data");
            }
        }

        return response()->json(['message' => 'updated']);
    }
}
