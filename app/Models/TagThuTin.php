<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;


class TagThuTin extends Pivot
{
    protected $table = 'tag_thu_tin';

    public $timestamps = false; // bảng pivot không có created_at, updated_at

    protected $fillable = [
        'tag_id',
        'thu_tin_id',
    ];
}
