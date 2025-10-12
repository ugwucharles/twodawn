<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class OptimizeImage extends Command
{
    protected $signature = 'image:optimize {source : Source image path (relative to base path or absolute)} {--width=1200 : Max width} {--quality=75 : Quality (0-100)} {--format=webp : Output format webp|jpg|png} {--dest= : Optional destination path}';

    protected $description = 'Optimize and convert an image using PHP GD (no external deps)';

    public function handle(): int
    {
        $source = $this->argument('source');
        $maxWidth = (int) $this->option('width');
        $quality = (int) $this->option('quality');
        $format = strtolower((string) $this->option('format'));
        $dest = $this->option('dest');

        $sourcePath = $this->resolvePath($source);
        if (!file_exists($sourcePath)) {
            $this->error('Source not found: '.$sourcePath);
            return self::FAILURE;
        }

        [$origW, $origH, $type] = getimagesize($sourcePath);
        if (!$origW || !$origH) {
            $this->error('Unable to read image size.');
            return self::FAILURE;
        }

        // Load source using GD
        $img = $this->loadImage($sourcePath, $type);
        if (!$img) {
            $this->error('Unsupported image format or GD not available.');
            return self::FAILURE;
        }

        // Compute new dimensions
        $newW = min($origW, $maxWidth);
        $newH = (int) round($origH * ($newW / $origW));

        // Resample
        $dst = imagecreatetruecolor($newW, $newH);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagecopyresampled($dst, $img, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

        // Destination path
        if (!$dest) {
            $pi = pathinfo($sourcePath);
            $basename = $pi['filename'];
            $dir = $pi['dirname'];
            $ext = $format === 'jpg' ? 'jpg' : ($format === 'png' ? 'png' : 'webp');
            $dest = $dir.DIRECTORY_SEPARATOR.$basename.'.'.$ext;
        } else {
            $dest = $this->resolvePath($dest);
        }

        // Save in requested format
        $ok = false;
        if ($format === 'webp') {
            if (!function_exists('imagewebp')) {
                $this->error('GD WebP support is not available.');
                return self::FAILURE;
            }
            $ok = imagewebp($dst, $dest, max(0, min(100, $quality)));
        } elseif ($format === 'jpg' || $format === 'jpeg') {
            if (!function_exists('imagejpeg')) {
                $this->error('GD JPEG support is not available.');
                return self::FAILURE;
            }
            // For transparent PNGs converted to JPG, fill white background
            $bg = imagecreatetruecolor($newW, $newH);
            $white = imagecolorallocate($bg, 255, 255, 255);
            imagefilledrectangle($bg, 0, 0, $newW, $newH, $white);
            imagecopy($bg, $dst, 0, 0, 0, 0, $newW, $newH);
            $ok = imagejpeg($bg, $dest, max(0, min(100, $quality)));
            imagedestroy($bg);
        } elseif ($format === 'png') {
            if (!function_exists('imagepng')) {
                $this->error('GD PNG support is not available.');
                return self::FAILURE;
            }
            // PNG compression level 0 (no compression) to 9
            $compression = (int) round((100 - max(0, min(100, $quality))) * 9 / 100);
            $ok = imagepng($dst, $dest, $compression);
        } else {
            $this->error('Unsupported output format: '.$format);
            return self::FAILURE;
        }

        imagedestroy($img);
        imagedestroy($dst);

        if (!$ok) {
            $this->error('Failed to save optimized image.');
            return self::FAILURE;
        }

        $this->info('Optimized image saved: '.$dest);
        return self::SUCCESS;
    }

    protected function resolvePath(string $p): string
    {
        if (str_starts_with($p, DIRECTORY_SEPARATOR) || preg_match('~^[A-Za-z]:\\\\~', $p)) {
            return $p;
        }
        return base_path($p);
    }

    protected function loadImage(string $path, int $type)
    {
        switch ($type) {
            case IMAGETYPE_JPEG:
                return function_exists('imagecreatefromjpeg') ? imagecreatefromjpeg($path) : null;
            case IMAGETYPE_PNG:
                return function_exists('imagecreatefrompng') ? imagecreatefrompng($path) : null;
            case IMAGETYPE_WEBP:
                return function_exists('imagecreatefromwebp') ? imagecreatefromwebp($path) : null;
            default:
                // Try to guess by extension
                $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                if ($ext === 'jpg' || $ext === 'jpeg') return @imagecreatefromjpeg($path);
                if ($ext === 'png') return @imagecreatefrompng($path);
                if ($ext === 'webp') return @imagecreatefromwebp($path);
                return null;
        }
    }
}
