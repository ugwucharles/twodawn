<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'event_id', 'buyer_name', 'buyer_email', 'buyer_phone', 'quantity', 'amount', 'paystack_reference', 'status', 'created_ip'
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'amount' => 'integer',
        ];
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function checkins()
    {
        return $this->hasMany(\App\Models\OrderCheckin::class);
    }

}
