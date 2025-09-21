<?php

namespace App\Filament\Api;

use App\Models\MucTieu;
use App\Models\ThuTin;
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

    public function index()
    {
        try {
            return response()->json(ThuTin::latest()->paginate(20));
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function store(Request $request)
    {
        try {
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
                'link_muc_tieu.required'=> 'Link mục tiêu là bắt buộc.',
                'ten_muc_tieu.required' => 'Tên mục tiêu là bắt buộc.',
            ]);


            $mucTieu = MucTieu::updateOrCreate(
                ['link' => $data['link_muc_tieu']], // điều kiện tìm
                [
                    'name' => $data['ten_muc_tieu'],
                    'type' => 6,
                ]
            );


            $data['id_muctieu'] = $mucTieu->id;
            $data['phanloai'] = 5;



            // update nếu link đã tồn tại, nếu không thì tạo mới
            $thuTin = ThuTin::updateOrCreate(
                ['link' => $data['link']], // điều kiện tìm
                $data  // dữ liệu để update hoặc create
            );

            return response()->json($thuTin, 201);

        } catch (Throwable $e) {
            return $this->errorResponse($e);
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
                'file' => 'required|mimes:jpeg,png,gif,bmp,webp,mp4,mov,avi,mkv,webm|max:51200'
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

}
