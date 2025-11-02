<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    protected function disk()
    {
        return Storage::disk('backups');
    }

    public function index()
    {
        $disk = $this->disk();
        $files = collect($disk->files())
            ->filter(fn($f) => Str::endsWith($f, '.zip'))
            ->map(function ($f) use ($disk) {
                $full = $disk->path($f);
                return [
                    'name' => basename($f),
                    'path' => $f,
                    'size' => is_file($full) ? filesize($full) : 0,
                    'mtime' => is_file($full) ? filemtime($full) : 0,
                ];
            })
            ->sortByDesc('mtime')
            ->values()
            ->all();
        return view('admin.backups.index', compact('files'));
    }

    public function download(string $file): StreamedResponse
    {
        $file = basename($file);
        abort_unless(Str::endsWith($file, '.zip'), 404);
        $disk = $this->disk();
        abort_unless($disk->exists($file), 404);
        return response()->download($disk->path($file), $file);
    }

    public function destroy(Request $request, string $file)
    {
        $file = basename($file);
        abort_unless(Str::endsWith($file, '.zip'), 404);
        $disk = $this->disk();
        if ($disk->exists($file)) {
            $disk->delete($file);
            return back()->with('status', 'Backup deleted: '.$file);
        }
        return back()->withErrors(['backup' => 'File not found']);
    }
}