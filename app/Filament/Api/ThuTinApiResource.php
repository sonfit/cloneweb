<?php

namespace App\Filament\Api;

use App\Models\Bot;
use App\Models\MucTieu;
use App\Models\ThuTin;
use App\Services\FunctionHelp;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;

class ThuTinApiResource
{
    public static function routes()
    {
        Route::get('thu-tins', [self::class, 'index']);
        Route::post('thu-tins', [self::class, 'store']);
        Route::get('thu-tins/{thuTin}', [self::class, 'show']);
        Route::put('thu-tins/{thuTin}', [self::class, 'update']);
        Route::delete('thu-tins/{thuTin}', [self::class, 'destroy']);
    }

    public function index(Request $request)
    {
        try {
            $query = ThuTin::query();
            if ($request->has('check_link')) {
                $link = "https://t.me/".$request->query('check_link');
                $exists = $query->where('link', $link)->first();

                if ($exists) {
                    return response()->json([
                        'data' => [$exists],
                    ]);
                }

                return response()->json([
                    'data' => []
                ]);
            }
            return response()->json(
                $query->latest()->paginate(20)
            );
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }


    public function store(Request $request)
    {
        try {
            // 1. Validate dữ liệu
            $data = $request->validate([
                'id_bot'        => 'nullable|exists:bots,id',
                'id_user'       => 'nullable|exists:users,id',
                'link'          => 'required|string|max:150',
                'contents_text' => 'nullable|string',
                'pic'           => 'nullable|array',
                'pic.*'         => 'string',
                'phanloai'      => 'nullable|integer',
                'level'         => 'integer|min:1|max:5',
                'time'          => 'nullable|date',
                'link_muc_tieu' => 'required|string|max:150',
                'ten_muc_tieu'  => 'required|string|max:150',
            ], [
                'link.required'         => 'Link bài viết là bắt buộc.',
                'link.string'           => 'Link phải là chuỗi ký tự.',
                'link.max'              => 'Link không được dài quá 150 ký tự.',
                'contents_text.string'  => 'Nội dung bài viết phải là chuỗi ký tự.',
                'pic.string'            => 'Tên file ảnh phải là chuỗi.',
                'phanloai.integer'      => 'Phân loại phải là số nguyên.',
                'level.integer'         => 'Mức độ phải là số nguyên.',
                'level.min'             => 'Mức độ tối thiểu là 1.',
                'level.max'             => 'Mức độ tối đa là 5.',
                'time.date'             => 'Trường time phải là ngày giờ hợp lệ (YYYY-MM-DD HH:MM:SS).',
                'id_bot.exists'         => 'id_bot không tồn tại trong bảng bots.',
                'id_user.exists'        => 'id_user không tồn tại trong bảng users.',
                'link_muc_tieu.required'=> 'Link mục tiêu là bắt buộc.',
                'ten_muc_tieu.required' => 'Tên mục tiêu là bắt buộc.',
            ]);

            // 2. Xử lý bảng Mục Tiêu
            $mucTieu = MucTieu::firstOrCreate(
                ['link' => $data['link_muc_tieu']],
                [
                    'name'       => $data['ten_muc_tieu'],
                    'type'       => 6,
                    'time_crawl' => $data['time'] ?? null,
                ]
            );

            if (!empty($data['time'])) {
                $newTime = Carbon::parse($data['time']);
                if (!$mucTieu->time_crawl || $newTime->greaterThan($mucTieu->time_crawl)) {
                    $mucTieu->update([
                        'name'       => $data['ten_muc_tieu'],
                        'type'       => 6,
                        'time_crawl' => $newTime,
                    ]);
                } else {
                    $mucTieu->update([
                        'name' => $data['ten_muc_tieu'],
                        'type' => 6,
                    ]);
                }
            }

            $data['id_muctieu'] = $mucTieu->id;
            $data['phanloai']   = $mucTieu->phanloai;

            // 3. Chuẩn hoá dữ liệu
            $incomingContent = isset($data['contents_text']) ? trim(strip_tags($data['contents_text'])) : null;
            $normalizedIncoming = $incomingContent ? mb_strtolower($incomingContent) : null;
            $incomingPics = $data['pic'] ?? [];

            // 4. Kiểm tra tồn tại theo link
            $existingByLink = ThuTin::where('link', $data['link'])->first();

            if ($existingByLink) {
                // So sánh nội dung
                $existingContent = $existingByLink->contents_text ? trim(strip_tags($existingByLink->contents_text)) : null;
                $normalizedExisting = $existingContent ? mb_strtolower($existingContent) : null;

                if ($normalizedIncoming && $normalizedIncoming === $normalizedExisting) {
                    // Trùng => bỏ qua và xoá ảnh thừa
                    $this->deleteOrphanPics($incomingPics, $existingByLink->pic ?? []);
                    return response()->json([
                        'message' => 'Link đã tồn tại và nội dung trùng, bỏ qua.',
                        'data'    => $existingByLink,
                    ], 200);
                }

                // Nội dung khác => cập nhật
                $existingByLink->update($data);

                $result = FunctionHelp::chamDiemTuKhoa($existingByLink->contents_text);
                $existingByLink->update(['level' => $result['level']]);

                if (!empty($result['tag_ids'])) {
                    $existingByLink->tags()->sync($result['tag_ids']);
                }

                return response()->json([
                    'message' => 'Link đã tồn tại, nội dung khác nên đã cập nhật.',
                    'data'    => $existingByLink,
                ], 200);
            }

            // 5. Kiểm tra tồn tại theo nội dung
            if ($normalizedIncoming) {
                $existingByContent = ThuTin::whereRaw('LOWER(TRIM(contents_text)) = ?', [$normalizedIncoming])->first();

                if ($existingByContent) {
                    $this->deleteOrphanPics($incomingPics, $existingByLink->pic ?? []);
                    return response()->json([
                        'message' => 'Nội dung đã tồn tại ở một link khác, bỏ qua.',
                        'data'    => $existingByContent,
                    ], 200);
                }
            }

            // 6. Tạo mới
            $result = FunctionHelp::chamDiemTuKhoa($data['contents_text']);
            $data['level'] = $result['level'];

            $thuTin = ThuTin::create($data);

            if (!empty($result['tag_ids'])) {
                $thuTin->tags()->sync($result['tag_ids']);
            }

            return response()->json($thuTin, 201);

        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Xoá ảnh không còn sử dụng
     */
    protected function deleteOrphanPics(array $incomingPics, array $currentPics = []): void
    {
        // Những ảnh nằm trong incoming nhưng không có trong current -> orphan
        $orphanPics = array_diff($incomingPics, $currentPics);

        foreach ($orphanPics as $p) {
            if (!$p || !is_string($p)) {
                continue;
            }
            try {
                if (Storage::disk('public')->exists($p)) {
                    Storage::disk('public')->delete($p);
                } else {
                    Log::info("Pic not found on disk (skipped): {$p}");
                }
            } catch (\Throwable $e) {
                Log::error('Error while deleting pic', [
                    'pic' => $p,
                    'err' => $e->getMessage(),
                ]);
            }
        }
    }



    public function show($id)
    {
        try {
            $thuTin = ThuTin::findOrFail($id);
            return response()->json($thuTin);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'ThuTin not found'], 404);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $thuTin = ThuTin::findOrFail($id);

            $data = $request->validate([
                'id_bot'        => 'nullable|exists:bots,id',
                'id_user'       => 'nullable|exists:users,id',
                'link'          => 'required|string|max:150',
                'contents_text' => 'nullable|string',
                'pic'           => 'nullable|string|max:150',
                'phanloai'      => 'nullable|integer',
                'level'         => 'integer|min:1|max:5',
                'time'          => 'nullable|date',
            ], [
                'link.required'     => 'Link bài viết là bắt buộc.',
                'link.string'       => 'Link phải là chuỗi ký tự.',
                'link.max'          => 'Link không được dài quá 150 ký tự.',

                'contents_text.string' => 'Nội dung bài viết phải là chuỗi ký tự.',

                'pic.string'        => 'Tên file ảnh phải là chuỗi.',
                'pic.max'           => 'Tên file ảnh không được dài quá 150 ký tự.',

                'phanloai.integer'  => 'Phân loại phải là số nguyên.',
                'level.integer'     => 'Mức độ phải là số nguyên.',
                'level.min'         => 'Mức độ tối thiểu là 1.',
                'level.max'         => 'Mức độ tối đa là 5.',

                'time.date'         => 'Trường time phải là ngày giờ hợp lệ (YYYY-MM-DD HH:MM:SS).',

                'id_bot.exists'     => 'id_bot không tồn tại trong bảng bots.',
                'id_user.exists'    => 'id_user không tồn tại trong bảng users.',
            ]);

            $thuTin->update($data);
            return response()->json($thuTin);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'ThuTin not found'], 404);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function destroy($id)
    {
        try {
            $thuTin = ThuTin::findOrFail($id);
            $thuTin->delete();
            return response()->json(null, 204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'ThuTin not found'], 404);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    private function errorResponse(Throwable $e, int $status = 500)
    {
        return response()->json([
            'error'   => $e->getMessage(),
            'type'    => class_basename($e),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
        ], $status);
    }

    public function upload(Request $request)
    {
        Log::info('Received file upload request', [
            'file' => $request->hasFile('file') ? $request->file('file')->getClientOriginalName() : 'No file'
        ]);

        try {
            $request->validate([
                'file' => 'required|mimes:jpeg,png,gif,bmp,webp,mp4,mov,avi,mkv,webm|max:512000'
                // ảnh ≤ 20MB, video cho phép lớn hơn (ở đây set max 50MB)
            ]);

            $file = $request->file('file');
            $date = now()->format('Ymd');
            $directory = "uploads/thutin/{$date}";
            Storage::disk('public')->makeDirectory($directory);

            $extension = strtolower($file->getClientOriginalExtension());
            $isImage = in_array($extension, ['jpeg', 'jpg', 'png', 'gif', 'bmp', 'webp']);

            if ($isImage) {
                // Ảnh → convert sang WebP
                $fileName = time() . '_' . uniqid() . '.webp';
                $path = $directory . '/' . $fileName;

                $image = Image::make($file)->encode('webp', 80);
                Storage::disk('public')->put($path, $image);
            } else {
                // Video → giữ nguyên extension
                $fileName = time() . '_' . uniqid() . '.' . $extension;
                $path = $directory . '/' . $fileName;

                Storage::disk('public')->putFileAs($directory, $file, $fileName);
            }

            return response()->json([
                'status' => 'success',
                'path'   => $path
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('File upload validation failed', [
                'errors' => $e->errors(),
                'file'   => $request->hasFile('file') ? $request->file('file')->getClientOriginalName() : 'No file'
            ]);
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
                'file'    => $request->hasFile('file') ? $request->file('file')->getClientOriginalName() : 'No file'
            ], 422);
        } catch (\Exception $e) {
            Log::error('File upload error', [
                'error' => $e->getMessage(),
                'file'  => $request->hasFile('file') ? $request->file('file')->getClientOriginalName() : 'No file'
            ]);
            return response()->json([
                'status'  => 'error',
                'message' => 'An unexpected error occurred',
                'error'   => $e->getMessage(),
                'file'    => $request->hasFile('file') ? $request->file('file')->getClientOriginalName() : 'No file'
            ], 500);
        }
    }

    public function getBot(Request $request)
    {
        try {
            $query = Bot::query();
            $query->with(['mucTieus:id,link,time_crawl']);

            if ($request->has('id_bot')) {
                $id_bot = $request->query('id_bot');
                $bot = $query->where('id', $id_bot)->first();
                if ($bot) {
                    $bot->update(['time_crawl' => now()]);
                    return response()->json([
                        'data' => [
                            [
                                'id' => $bot->id,
                                'ten_bot' => $bot->ten_bot,
                                'muc_tieus' => $bot->mucTieus->map(fn($mt) => [
                                    'id' => $mt->id,
                                    'link' => $mt->link,
                                    'time_crawl' => $mt->time_crawl
                                        ? Carbon::parse($mt->time_crawl)->format('Y-m-d H:i:s')
                                        : now()->subDay()->format('Y-m-d H:i:s'),
                                ]),
                            ]
                        ],
                    ]);
                }

                return response()->json([
                    'data' => []
                ]);
            }

            // Nếu không có id_bot, trả về danh sách với pagination
            $bots = $query->latest()->paginate(20);

            // Tùy chỉnh dữ liệu trả về
            $bots->getCollection()->transform(function($bot) {
                return [
                    'id' => $bot->id,
                    'ten_bot' => $bot->ten_bot,
                    'muc_tieus' => $bot->mucTieus->map(fn($mt) => [
                        'id' => $mt->id,
                        'name' => $mt->name,
                        'link' => $mt->link,
                        'time_crawl' => $mt->time_crawl
                            ? Carbon::parse($mt->time_crawl)->format('Y-m-d H:i:s')
                            : now()->subDay()->format('Y-m-d H:i:s'),
                    ]),
                ];
            });

            return response()->json($bots);

        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

}
