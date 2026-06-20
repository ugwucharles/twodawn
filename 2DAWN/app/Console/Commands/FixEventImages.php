<?php

namespace App\Console\Commands;

use App\Models\Event;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class FixEventImages extends Command
{
    protected $signature = 'images:fix-events {--dry-run : Show what would be fixed without making changes}';
    protected $description = 'Fix broken event image URLs by updating image_path values';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('DRY RUN MODE - No changes will be made');
        }

        $events = Event::whereNotNull('image_path')->get();
        $fixed = 0;
        $skipped = 0;

        foreach ($events as $event) {
            $originalPath = $event->image_path;
            $currentUrl = $event->image_url;
            
            // Check if the current URL is accessible
            $isAccessible = $this->isUrlAccessible($currentUrl);
            
            if ($isAccessible) {
                $this->line("✓ Event '{$event->title}' - Image accessible: {$currentUrl}");
                $skipped++;
                continue;
            }

            // Try to fix the path
            $fixedPath = $this->fixImagePath($originalPath);
            
            if ($fixedPath && $fixedPath !== $originalPath) {
                if (!$dryRun) {
                    $event->update(['image_path' => $fixedPath]);
                }
                
                $newUrl = $event->fresh()->image_url;
                $this->info("✓ Fixed Event '{$event->title}'");
                $this->line("  Original: {$originalPath}");
                $this->line("  Fixed: {$fixedPath}");
                $this->line("  URL: {$newUrl}");
                $fixed++;
            } else {
                $this->warn("✗ Could not fix Event '{$event->title}' - Path: {$originalPath}");
                $skipped++;
            }
        }

        $this->newLine();
        $this->info("Summary:");
        $this->line("  Fixed: {$fixed}");
        $this->line("  Skipped: {$skipped}");
        $this->line("  Total: " . ($fixed + $skipped));

        return self::SUCCESS;
    }

    private function isUrlAccessible(string $url): bool
    {
        if (empty($url)) {
            return false;
        }

        try {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 5,
                    'method' => 'HEAD'
                ]
            ]);
            
            $headers = @get_headers($url, 1, $context);
            return $headers && strpos($headers[0], '200') !== false;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function fixImagePath(string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        // If it's already a full URL, return as-is
        if (str_starts_with($path, 'http')) {
            return $path;
        }

        // Check if file exists in current storage
        if (Storage::exists($path)) {
            return $path;
        }

        // Try common variations
        $variations = [
            $path,
            'events/' . basename($path),
            'public/events/' . basename($path),
            'storage/app/public/events/' . basename($path),
        ];

        foreach ($variations as $variation) {
            if (Storage::exists($variation)) {
                return $variation;
            }
        }

        return null;
    }
}
