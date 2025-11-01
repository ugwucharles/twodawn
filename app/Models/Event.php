<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        'pass_fees_to_buyer',
'image_path',
        'gallery',
        'mood',
        'use_custom_slug',
        'slug',
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
        ];
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function getPublicUrlAttribute(): string
    {
        if ($this->use_custom_slug && $this->slug) {
            try { return route('events.slug', $this->slug); } catch (\Throwable $e) { return url('/event/' . $this->slug); }
        }
        try { return route('events.show', $this); } catch (\Throwable $e) { return url('/events/'.$this->id); }
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
        
        // For local storage paths, prefer public disk URL if available
        try {
            if (Storage::disk('public')->exists($this->image_path)) {
                // Use app URL to avoid misconfigured filesystems.public.url
                return url('storage/' . ltrim($this->image_path, '/'));
            }
            return url('storage/' . ltrim($this->image_path, '/'));
        } catch (\Exception $e) {
            // Fallback: if Storage::url() fails, try to construct URL manually
            $disk = config('filesystems.default', 'public');
            if ($disk === 's3') {
                $bucket = config('filesystems.disks.s3.bucket');
                $region = config('filesystems.disks.s3.region');
                $endpoint = config('filesystems.disks.s3.endpoint');
                
                if ($endpoint) {
                    // Custom endpoint (Cloudflare R2, DigitalOcean Spaces, etc.)
                    return rtrim($endpoint, '/') . '/' . $bucket . '/' . $this->image_path;
                } else {
                    // Standard AWS S3
                    return "https://{$bucket}.s3.{$region}.amazonaws.com/{$this->image_path}";
                }
            } else {
                // Local storage fallback
                return url('storage/' . $this->image_path);
            }
        }
    }

    public function getGalleryUrlsAttribute(): array
    {
        $out = [];
        $items = is_array($this->gallery ?? null) ? $this->gallery : [];
        foreach ($items as $p) {
            if (!$p) continue;
            if (str_starts_with($p, 'http')) { $out[] = $p; continue; }
            try {
                if (Storage::disk('public')->exists($p)) {
                    $out[] = url('storage/' . ltrim($p, '/'));
                } else {
                    $out[] = url('storage/' . ltrim($p, '/'));
                }
            } catch (\Throwable $e) {
                $out[] = url('storage/' . ltrim($p, '/'));
            }
        }
        return $out;
    }
}
