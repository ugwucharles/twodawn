<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;

class PaystackHealthController extends Controller
{
    public function __invoke(Request $request)
    {
        $secret = (string) config('services.paystack.secret');
        $public = (string) config('services.paystack.public');
        $callback = (string) (config('services.paystack.callback_url') ?: route('paystack.callback'));

        $configured = ($secret !== '' && $public !== '');

        $probe = null; $probe_status = null; $probe_ok = null; $probe_error = null;
        if ($configured) {
            try {
                // Safe probe: initialize with test values; Paystack won't charge without user completing checkout
                $ref = 'HEALTH_'.Str::random(8);
                $resp = Http::timeout(10)->withToken($secret)->post('https://api.paystack.co/transaction/initialize', [
                    'email' => 'health@example.com',
                    'amount' => 100, // 1 NGN (test only)
                    'reference' => $ref,
                    'callback_url' => $callback,
                ]);
                $probe_status = $resp->status();
                $body = $resp->json();
                $probe_ok = (bool) data_get($body, 'status');
                $probe = [
                    'status' => $probe_status,
                    'ok' => $probe_ok,
                    'message' => data_get($body, 'message'),
                ];
            } catch (\Throwable $e) {
                $probe_error = $e->getMessage();
            }
        }

        return response()->json([
            'app_url' => config('app.url', url('/')),
            'configured' => $configured,
            'public_present' => $public !== '',
            'secret_present' => $secret !== '',
            'callback_url' => $callback,
            'probe' => $probe,
            'probe_error' => $probe_error,
        ]);
    }
}