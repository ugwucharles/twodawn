<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class LoggerService
{
    /**
     * Log payment-related events
     */
    public static function logPayment(string $action, array $context = []): void
    {
        Log::channel('payments')->info("Payment: {$action}", array_merge([
            'timestamp' => now()->toISOString(),
            'action' => $action,
        ], $context));
    }

    /**
     * Log payment failures with detailed context
     */
    public static function logPaymentFailure(string $reason, array $context = []): void
    {
        Log::channel('payments')->error("Payment Failed: {$reason}", array_merge([
            'timestamp' => now()->toISOString(),
            'reason' => $reason,
            'severity' => 'error',
        ], $context));
    }

    /**
     * Log security events
     */
    public static function logSecurity(string $event, array $context = []): void
    {
        Log::channel('security')->warning("Security Event: {$event}", array_merge([
            'timestamp' => now()->toISOString(),
            'event' => $event,
            'severity' => 'warning',
        ], $context));
    }

    /**
     * Log user actions
     */
    public static function logUserAction(string $action, array $context = []): void
    {
        Log::channel('user_actions')->info("User Action: {$action}", array_merge([
            'timestamp' => now()->toISOString(),
            'action' => $action,
        ], $context));
    }

    /**
     * Log performance metrics
     */
    public static function logPerformance(string $operation, float $duration, array $context = []): void
    {
        $level = $duration > 5.0 ? 'warning' : 'info';
        Log::channel('performance')->{$level}("Performance: {$operation}", array_merge([
            'timestamp' => now()->toISOString(),
            'operation' => $operation,
            'duration_seconds' => $duration,
            'severity' => $level,
        ], $context));
    }

    /**
     * Log admin actions
     */
    public static function logAdminAction(string $action, int $adminId, array $context = []): void
    {
        Log::channel('admin')->info("Admin Action: {$action}", array_merge([
            'timestamp' => now()->toISOString(),
            'action' => $action,
            'admin_id' => $adminId,
        ], $context));
    }

    /**
     * Extract request context for logging
     */
    public static function getRequestContext(Request $request): array
    {
        return [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'user_id' => auth()->id(),
            'session_id' => session()->getId(),
        ];
    }

    /**
     * Log image upload events
     */
    public static function logImageUpload(string $action, array $context = []): void
    {
        Log::channel('uploads')->info("Image Upload: {$action}", array_merge([
            'timestamp' => now()->toISOString(),
            'action' => $action,
        ], $context));
    }

    /**
     * Log order events
     */
    public static function logOrder(string $action, string $orderReference, array $context = []): void
    {
        Log::channel('orders')->info("Order: {$action}", array_merge([
            'timestamp' => now()->toISOString(),
            'action' => $action,
            'order_reference' => $orderReference,
        ], $context));
    }
}
