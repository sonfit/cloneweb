<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

class Link extends Model
{
    use HasRoles;
    protected $fillable = ['link', 'note'];

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'link_tag');
    }

}
