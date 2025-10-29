<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

class Tag extends Model
{
    use HasRoles;
    protected $fillable = [
        'tag',
        'diem',
        'parent'
    ];

    protected static function booted()
    {
        static::addGlobalScope('with_links_count', function ($query) {
            $query->withCount('links'); // Đếm số lượng link liên kết với tag
        });
    }
    public function links()
    {
        return $this->belongsToMany(Link::class);
    }


    public function thuTins()
    {
        return $this->belongsToMany(ThuTin::class, TagThuTin::class, 'tag_id', 'thu_tin_id')
            ->using(TagThuTin::class)
            ->withPivot('tag_id', 'thu_tin_id');
    }
}
