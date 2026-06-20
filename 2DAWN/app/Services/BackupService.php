<?php

namespace App\Services;

use ZipArchive;
use Illuminate\Support\Facades\Storage;

class BackupService
{
    public static function run(bool $dbOnly = false): string
    {
        $backupDir = storage_path('app/backups');
        if (! is_dir($backupDir)) mkdir($backupDir, 0775, true);
        $ts = now()->format('Ymd_His');
        $zipPath = $backupDir . "/backup_{$ts}.zip";

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            throw new \RuntimeException('Unable to create backup zip');
        }

        // Database (sqlite only by default)
        $conn = env('DB_CONNECTION', 'sqlite');
        if ($conn === 'sqlite') {
            $dbFile = database_path('database.sqlite');
            if (file_exists($dbFile)) $zip->addFile($dbFile, 'db/database.sqlite');
        }
        // Add more drivers here if needed (mysqldump, pg_dump) when available.

        if (! $dbOnly) {
            // Public uploads
            $publicPath = storage_path('app/public');
            if (is_dir($publicPath)) self::addDirToZip($zip, $publicPath, 'storage/public');
        }

        $zip->close();
        return $zipPath;
    }

    protected static function addDirToZip(ZipArchive $zip, string $path, string $base): void
    {
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS));
        foreach ($files as $file) {
            $local = $base . '/' . ltrim(str_replace($path, '', $file->getPathname()), '/\\');
            if ($file->isFile()) $zip->addFile($file->getPathname(), $local);
        }
    }
}