<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HostToken extends Model
{
    protected $fillable = ['event_id','token','label','active','expires_at'];
    protected $casts = [
        'active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function event() { return $this->belongsTo(\App\Models\Event::class); }
}
