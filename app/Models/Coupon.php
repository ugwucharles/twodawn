<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code','type','value','event_id','max_uses','uses','starts_at','ends_at','active'
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'active' => 'boolean',
        ];
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function scopeValidFor($query, ?int $eventId)
    {
        return $query->where('active', true)
            ->when($eventId, fn($q) => $q->whereNull('event_id')->orWhere('event_id', $eventId))
            ->where(function($q){
                $now = now();
                $q->whereNull('starts_at')->orWhere('starts_at','<=',$now);
            })
            ->where(function($q){
                $now = now();
                $q->whereNull('ends_at')->orWhere('ends_at','>=',$now);
            })
            ->where(function($q){
                $q->whereNull('max_uses')->orWhereColumn('uses','<','max_uses');
            });
    }
}
