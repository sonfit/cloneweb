<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MucTieu extends Model
{
    protected $table = 'muc_tieus';

    protected $fillable = [
        'name',
        'phanloai',
        'type',
        'link',
        'time_create',
        'time_crawl',
        'ghi_chu',
    ];

    protected $casts = [
        'time_create' => 'datetime',
        'time_crawl' => 'datetime',
    ];

    public function bots()
    {
        return $this->belongsToMany(Bot::class, 'bot_muc_tieu');
    }

    // Các user đang theo dõi mục tiêu này
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_muc_tieu');
    }
}
