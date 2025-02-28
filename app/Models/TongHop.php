<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TongHop extends Model
{
    protected $fillable = [
        'name',
        'user_id',
        'type',
        'url',
        'raw_text',
        'summary_text'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
