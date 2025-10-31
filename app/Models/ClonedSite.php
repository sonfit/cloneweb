<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClonedSite extends Model
{
    protected $fillable = [
        'domain',
        'web_clone',
        'string_replace_arr',
    ];

    protected $casts = [
        'string_replace_arr' => 'array',
    ];
}
