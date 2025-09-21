<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
