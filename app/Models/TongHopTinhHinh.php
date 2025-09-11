<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class TongHopTinhHinh extends Model
{
    protected $table = 'tong_hop_tinh_hinhs';

    protected $fillable = [
        'link',
        'contents_text',
        'id_muctieu',
        'pic',
        'sumary',
        'phanloai',
        'id_user',
        'time',
    ];

    protected static function booted()
    {
        static::deleting(function ($record) {
            if ($record->pic) {
                Storage::disk('public')->delete($record->pic);
            }
        });

        static::updating(function ($record) {
            if ($record->isDirty('pic')) { // Nếu ảnh thay đổi
                $oldPic = $record->getOriginal('pic');
                if ($oldPic && $oldPic !== $record->pic) {
                    Storage::disk('public')->delete($oldPic);
                }
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function mucTieu()
    {
        return $this->belongsTo(MucTieu::class, 'id_muctieu');
    }
}
