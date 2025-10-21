<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

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

        // Ensure users table has is_admin column (some envs may miss addon migration)
        try {
            if (Schema::hasTable('users') && !Schema::hasColumn('users', 'is_admin')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->boolean('is_admin')->default(false);
                });
            }
        } catch (\Throwable $e) {
            // ignore if migration race; next request will succeed
        }

        // Auto-promote ADMIN_EMAIL to admin if present (useful for first-boot on Render)
        try {
            $email = env('ADMIN_EMAIL');
            if ($email && Schema::hasTable('users') && Schema::hasColumn('users','is_admin')) {
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
