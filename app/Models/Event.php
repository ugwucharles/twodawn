<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'title',
        'description',
        'venue',
        'starts_at',
        'ends_at',
        'price',
        'early_bird_price',
        'early_bird_ends_at',
        'capacity',
        'is_published',
        'image_path',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_published' => 'boolean',
            'price' => 'decimal:2',
            'early_bird_price' => 'decimal:2',
        ];
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
