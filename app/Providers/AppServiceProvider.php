<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS URLs in production (and when APP_URL uses https)
        $appUrl = (string) env('APP_URL', '');
        if ($this->app->environment('production') && (env('FORCE_HTTPS', false) || str_starts_with($appUrl, 'https://'))) {
            URL::forceScheme('https');
        }
    }
}
