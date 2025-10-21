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

        // Auto-promote ADMIN_EMAIL to admin if present (useful for first-boot on Render)
        try {
            $email = env('ADMIN_EMAIL');
            if ($email && \Illuminate\Support\Facades\Schema::hasTable('users')) {
                \App\Models\User::where('email', $email)->update([
                    'is_admin' => true,
                    'email_verified_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            // quietly ignore if DB not ready
        }
    }
}
