<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bot extends Model
{
    protected $fillable = [
        'ten_bot',
        'loai_bot',
        'lenh_bot',
        'ghi_chu'
    ];
}
