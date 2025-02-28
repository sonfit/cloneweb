<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DangKy extends Model
{
    protected $fillable = [
        'user_id',
        'ip_user',
        'oto_muc_3',
        'xe_may_muc_3',
        'oto_muc_4',
        'xe_may_muc_4'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
