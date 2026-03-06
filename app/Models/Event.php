<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Order; // for free tickets calculations

class Event extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'must_know',
        'venue',
        'starts_at',
        'ends_at',
        'price',
        'early_bird_price',
        'early_bird_ends_at',
        'capacity',
        'free_tickets_count',
        'is_published',
        'pass_fees_to_buyer',
        'image_path',
        'gallery',
        'mood',
        'use_custom_slug',
        'slug',
        'whatsapp_group_url',
        'state',
        'ticket_types',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_published' => 'boolean',
            'pass_fees_to_buyer' => 'boolean',
            'use_custom_slug' => 'boolean',
            'price' => 'decimal:2',
            'early_bird_price' => 'decimal:2',
            'gallery' => 'array',
            'ticket_types' => 'array',
        ];
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getPublicUrlAttribute(): string
    {
        if ($this->use_custom_slug && $this->slug) {
            try {
                return route('events.slug', $this->slug);
            }
            catch (\Throwable $e) {
                return url('/event/' . $this->slug);
            }
        }
        try {
            return route('events.show', $this);
        }
        catch (\Throwable $e) {
            return url('/events/' . $this->id);
        }
    }

    /**
     * Remaining promotional free tickets (first-N free).
     * Counts all paid orders (free and paid amounts have status 'paid').
     */
    public function getFreeTicketsRemainingAttribute(): int
    {
        $limit = (int)($this->free_tickets_count ?? 0);
        if ($limit <= 0)
            return 0;
        try {
            $sold = (int)Order::where('event_id', $this->id)->where('status', 'paid')->sum('quantity');
        }
        catch (\Throwable $e) {
            $sold = 0;
        }
        return max(0, $limit - $sold);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (empty($this->image_path)) {
            return null;
        }

        // If it's already a full URL (Cloudinary), return as-is
        if (str_starts_with($this->image_path, 'http')) {
            return $this->image_path;
        }

        // Strip "storage/" if it's accidentally stored in the DB (common mistake)
        $cleanPath = ltrim($this->image_path, '/');
        if (str_starts_with($cleanPath, 'storage/')) {
            $cleanPath = substr($cleanPath, 8);
        }

        try {
            // Use Storage::url which is the standard Laravel way
            return Storage::disk('public')->url($cleanPath);
        }
        catch (\Exception $e) {
            // Fallback for local
            return url('storage/' . $cleanPath);
        }
    }

    public function getGalleryUrlsAttribute(): array
    {
        $out = [];
        $items = is_array($this->gallery ?? null) ? $this->gallery : [];
        foreach ($items as $p) {
            if (!$p)
                continue;
            if (str_starts_with($p, 'http')) {
                $out[] = $p;
                continue;
            }
            try {
                if (Storage::disk('public')->exists($p)) {
                    $out[] = url('storage/' . ltrim($p, '/'));
                }
                else {
                    $out[] = url('storage/' . ltrim($p, '/'));
                }
            }
            catch (\Throwable $e) {
                $out[] = url('storage/' . ltrim($p, '/'));
            }
        }
        return $out;
    }
}
