<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderRefund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Models\Tenant;

class RefundController extends Controller
{
    public function store(Request $request, Order $order)
    {
        $request->validate([
            'amount' => ['nullable','numeric','min:0'],
            'reason' => ['nullable','string','max:500'],
        ]);

        // Only paid orders can be refunded
        if ($order->status !== 'paid') {
            return $this->respond($request, 422, ['error' => 'Only paid orders can be refunded.']);
        }

        $already = (int) OrderRefund::where('order_id', $order->id)->where('status','succeeded')->sum('amount');
        $amountK = $request->filled('amount') ? (int) round(((float) $request->input('amount')) * 100) : ($order->amount - $already);
        if ($amountK <= 0) {
            return $this->respond($request, 422, ['error' => 'Refund amount must be greater than 0.']);
        }
        if ($already + $amountK > $order->amount) {
            return $this->respond($request, 422, ['error' => 'Refund exceeds paid amount.']);
        }

        // Create pending ledger entry
        $refund = OrderRefund::create([
            'order_id' => $order->id,
            'amount' => $amountK,
            'status' => 'pending',
            'reason' => $request->input('reason'),
            'admin_id' => auth()->id(),
        ]);

        // Call Paystack
        try {
            $secret = config('services.paystack.secret');
            $payload = [
                'transaction' => $order->paystack_reference,
                'amount' => $amountK,
            ];
            $resp = Http::withToken($secret)->post('https://api.paystack.co/refund', $payload);
            $body = $resp->json();
            $ok = $resp->ok() && (bool) data_get($body, 'status');
            $refund->update([
                'status' => $ok ? 'succeeded' : 'failed',
                'provider_ref' => data_get($body, 'data.reference') ?? data_get($body, 'data.id'),
                'payload' => $body,
            ]);
            if (! $ok) {
                return $this->respond($request, 502, ['error' => 'Gateway refund failed', 'details' => data_get($body,'message')]);
            }
        } catch (\Throwable $e) {
            $refund->update(['status' => 'failed']);
            return $this->respond($request, 500, ['error' => 'Refund error', 'details' => $e->getMessage()]);
        }

        // Update order status and capacity
        $refundedTotal = (int) OrderRefund::where('order_id', $order->id)->where('status','succeeded')->sum('amount');
        if ($refundedTotal >= $order->amount) {
            $order->update(['status' => 'refunded']);
            // Restore capacity on full refund
            $event = $order->event;
            if ($event && !is_null($event->capacity)) {
                $event->update(['capacity' => max(0, ((int) $event->capacity) + (int) $order->quantity)]);
            }
        } else {
            $order->update(['status' => 'partially_refunded']);
        }

        // Notify buyer
        try { Mail::to($order->buyer_email)->queue(new \App\Mail\RefundMail($order, $refund)); } catch (\Throwable $e) { /* ignore */ }
        // Notify host/support
        try {
            $support = optional(Tenant::where('domain', strtolower((string) $request->getHost()))->first())->support_email;
            $admin = env('ADMIN_EMAIL');
            $targets = array_values(array_unique(array_filter([$support, $admin])));
            foreach ($targets as $t) { Mail::to($t)->queue(new \App\Mail\RefundAdminMail($order, $refund)); }
        } catch (\Throwable $e) { /* ignore */ }

        return $this->respond($request, 200, ['ok' => true, 'refund_id' => $refund->id]);
    }

    protected function respond(Request $request, int $status, array $data)
    {
        if ($request->expectsJson()) return response()->json($data, $status);
        if ($status !== 200) return back()->withErrors(['general' => $data['error'] ?? 'Refund error']);
        return back()->with('status', 'Refund processed');
    }
}