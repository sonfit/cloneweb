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
        'time',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function bot()
    {
        return $this->belongsTo(Bot::class, 'id_bot');
    }
}
