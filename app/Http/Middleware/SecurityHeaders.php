<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Add security headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Restrict camera globally; allow only on admin scanner routes
        $permissions = 'geolocation=(), microphone=(), camera=()';
        if ($request->is('admin/scanner*')) {
            $permissions = 'geolocation=(), microphone=(), camera=(self)';
        }
        $response->headers->set('Permissions-Policy', $permissions);
        
        // Content Security Policy (production-safe)
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://js.paystack.co https://www.googletagmanager.com https://plausible.io https://cdn.jsdelivr.net; " .
               "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.bunny.net https://cdn.jsdelivr.net; " .
               "img-src 'self' data: https: blob:; " .
               "font-src 'self' data: https://fonts.gstatic.com https://fonts.bunny.net; " .
               "connect-src 'self' https://api.paystack.co https://res.cloudinary.com https://www.google-analytics.com https://region1.google-analytics.com https://*.google-analytics.com https://www.googletagmanager.com https://plausible.io; " .
               "frame-src https://js.paystack.co https://checkout.paystack.com; " .
               "object-src 'none'; " .
               "base-uri 'self'; " .
               "form-action 'self' https:; " .
               "frame-ancestors 'none';";
        
        $response->headers->set('Content-Security-Policy', $csp);

        // HSTS in production (advises browsers to use HTTPS)
        if (app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        return $response;
    }
}
