<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HostRequest extends Model
{
    protected $fillable = [
        'name','email','phone','event_title','event_date','venue','expected_attendees','budget_kobo','message','status'
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'datetime',
            'expected_attendees' => 'integer',
            'budget_kobo' => 'integer',
        ];
    }
}
