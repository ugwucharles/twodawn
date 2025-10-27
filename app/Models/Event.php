<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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
        'mood',
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
}
