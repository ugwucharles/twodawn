<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderRefund extends Model
{
    protected $fillable = [
        'order_id','amount','status','provider_ref','reason','admin_id','payload'
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'payload' => 'array',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}