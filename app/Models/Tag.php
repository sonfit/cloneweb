<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

class Tag extends Model
{
    use HasRoles;
    protected $fillable = ['tag'];

    public function links()
    {
//        return $this->belongsToMany(Link::class, 'link_tag')->withPivot(['note']);
        return $this->belongsToMany(Link::class);
    }

    protected static function booted()
    {
        static::addGlobalScope('with_links_count', function ($query) {
            $query->withCount('links'); // Đếm số lượng link liên kết với tag
        });
    }
}
