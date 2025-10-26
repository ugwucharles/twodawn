<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\TicketMail;
use App\Models\Order;

class PaystackWebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        $secret = (string) config('services.paystack.secret');
        if ($secret === '') {
            return response()->json(['ok' => false, 'error' => 'Not configured'], 200);
        }

        // Verify signature
        $signature = (string) $request->header('x-paystack-signature', '');
        $payload = $request->getContent();
        $expected = hash_hmac('sha512', $payload, $secret);
        if ($signature === '' || ! hash_equals($expected, $signature)) {
            return response()->json(['ok' => false, 'error' => 'Invalid signature'], 401);
        }

        $data = $request->json()->all();
        $event = (string) data_get($data, 'event', '');
        if ($event !== 'charge.success') {
            return response()->json(['ok' => true]);
        }

        $reference = (string) data_get($data, 'data.reference', '');
        if ($reference === '') {
            return response()->json(['ok' => false, 'error' => 'Missing reference'], 400);
        }

        $order = Order::where('paystack_reference', $reference)->first();
        if (! $order) {
            // Unknown reference; acknowledge to prevent endless retries.
            return response()->json(['ok' => true]);
        }
        if ($order->status === 'paid') {
            return response()->json(['ok' => true]);
        }

        // Verify transaction details with Paystack
        $verify = Http::withToken($secret)->get('https://api.paystack.co/transaction/verify/'.$reference);
        $body = $verify->json();
        $status = (string) data_get($body, 'data.status');
        $amount = (int) data_get($body, 'data.amount');
        $currency = (string) data_get($body, 'data.currency');

        if (! $verify->ok() || $status !== 'success' || $amount !== (int) $order->amount || $currency !== 'NGN') {
            $order->update(['status' => 'failed']);
            return response()->json(['ok' => true]);
        }

        try {
            DB::transaction(function () use ($order) {
                $event = $order->event()->lockForUpdate()->first();
                if (! $event) {
                    throw new \RuntimeException('Event missing');
                }
                if (! is_null($event->capacity)) {
                    $needed = (int) $order->quantity;
                    $current = (int) $event->capacity;
                    if ($current < $needed) {
                        $order->update(['status' => 'failed']);
                        throw new \RuntimeException('Sold out');
                    }
                    $event->update(['capacity' => $current - $needed]);
                }
                if ($order->coupon_code) {
                    \App\Models\Coupon::where('code', $order->coupon_code)->increment('uses');
                }
                $order->update(['status' => 'paid']);
            });
        } catch (\Throwable $e) {
            return response()->json(['ok' => true]);
        }

        try { Mail::to($order->buyer_email)->queue(new TicketMail($order)); } catch (\Throwable $e) {}

        return response()->json(['ok' => true]);
    }
}
