<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

//class TraceJob extends Model
class TraceJob extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;
    protected $fillable = [
        'payload',
        'status',
        'result',
        'claimed_at',
//        'payload_sdt',
//        'payload_cccd',
//        'payload_fb',
    ];

    protected $attributes = [
        'payload' => '{}',
        'status' => 'pending',
    ];

    protected $casts = [
        'payload' => 'array',
        'result' => 'array',
        'claimed_at' => 'datetime',
    ];

    protected $appends = [
        'payload_sdt',
        'payload_cccd',
        'payload_fb',
        'formatted_result',
    ];

    public function getPayloadSdtAttribute()
    {
        return $this->payload['sdt'] ?? null;
    }

    public function getPayloadCccdAttribute()
    {
        return $this->payload['cccd'] ?? null;
    }

    public function getPayloadFbAttribute()
    {
        return $this->payload['fb'] ?? null;
    }

    public function getFormattedResultAttribute()
    {
        if (is_array($this->result)) {
            return json_encode($this->result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
        return $this->result;
    }

}
