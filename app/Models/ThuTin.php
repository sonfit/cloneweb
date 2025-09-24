<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ThuTin extends Model
{
    protected $fillable = [
        'link',
        'contents_text',
        'pic',
        'phanloai',
        'level',
        'id_bot',
        'id_user',
        'id_muctieu',
        'time',
    ];

    protected $casts = [
        'pic' => 'array',
    ];


    protected static function booted()
    {
        static::deleting(function ($record) {
            if ($record->pic) {
                Storage::disk('public')->delete($record->pic); // pic là array => ok
            }
        });

        static::updating(function ($record) {
            if ($record->isDirty('pic')) {
                $oldPic = json_decode($record->getOriginal('pic'), true); // convert JSON -> array
                $newPic = $record->pic ?? [];

                // Xóa những file cũ không còn trong danh sách mới
                $toDelete = array_diff($oldPic ?? [], $newPic);
                if (!empty($toDelete)) {
                    Storage::disk('public')->delete($toDelete);
                }
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function bot()
    {
        return $this->belongsTo(Bot::class, 'id_bot');
    }
    public function mucTieu()
    {
        return $this->belongsTo(MucTieu::class, 'id_muctieu');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, TagThuTin::class, 'thu_tin_id', 'tag_id')
            ->using(TagThuTin::class) // dùng Pivot model
            ->withPivot('tag_id', 'thu_tin_id');
    }
}
