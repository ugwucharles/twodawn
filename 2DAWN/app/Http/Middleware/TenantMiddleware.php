<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = strtolower($request->getHost() ?? '');
        $tenant = cache()->remember("tenant:".$host, 60, function () use ($host) {
            return Tenant::where('domain', $host)->where('is_active', true)->first();
        });

        if ($tenant) {
            // Dynamically set branding
            config(['app.name' => $tenant->name]);
            view()->share('tenant', $tenant);
        } else {
            view()->share('tenant', null);
        }

        return $next($request);
    }
}