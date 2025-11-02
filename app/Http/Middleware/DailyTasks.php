<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class DailyTasks
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Only run in production and for regular web requests
            if (app()->environment(['production']) && $request->isMethod('GET')) {
                $key = 'daily_tasks_'.now()->format('Ymd');
                if (! Cache::get($key)) {
                    Cache::put($key, 1, now()->endOfDay());
                    // Run a quick DB-only backup and monitor in-process
                    try { Artisan::call('backup:run', ['--only-db' => true]); } catch (\Throwable $e) { Log::warning('backup:run failed: '.$e->getMessage()); }
                    try { Artisan::call('backup:monitor'); } catch (\Throwable $e) { /* ignore */ }
                }
            }
        } catch (\Throwable $e) { /* never block the request */ }

        return $next($request);
    }
}