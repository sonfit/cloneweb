<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function mucTieu()
    {
        return $this->belongsTo(MucTieu::class, 'id_muctieu');
    }
}
