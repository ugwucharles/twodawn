<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderCheckin extends Model
{
    protected $fillable = ['order_id','host_token_id','count','source'];

    public function order() { return $this->belongsTo(\App\Models\Order::class); }
    public function hostToken() { return $this->belongsTo(\App\Models\HostToken::class); }
}
