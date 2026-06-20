<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\BackupService;

class DailyTasks {
    public function handle(Request $request, Closure $next): Response {
        try {
            if (app()->environment(['production']) && $request->isMethod('GET')) {
                $key = 'daily_tasks_'.now()->format('Ymd');
                if (! Cache::get($key)) {
                    Cache::put($key, 1, now()->endOfDay());
                    try { BackupService::run(true); }
                    catch (\Throwable $e) { Log::warning('daily backup failed: '.$e->getMessage()); }
                }
            }
        } catch (\Throwable $e) { /* never block the request */ }
        return $next($request);
    }
}
