<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;

class TicketScanController extends Controller
{
    public function index()
    {
        return view('admin.scanner');
    }

    // Verify order QR or reference (no mutation)
    public function verify(Request $request)
    {
        $text = (string) $request->input('text', '');
        $text = trim($text);
        $ref = null;
        if (preg_match('/(PA_[A-Za-z0-9]{6,})/', $text, $m)) { $ref = $m[1]; } else { $ref = $text; }

        $order = Order::with('event')->where('paystack_reference', $ref)->first();
        if (! $order) {
            return response()->json(['ok'=>false,'valid'=>false,'type'=>'order','message'=>'Order not found'], 404);
        }
        $valid = $order->status === 'paid';
        return response()->json([
            'ok' => true,
            'valid' => $valid,
            'type' => 'order',
            'reference' => $order->paystack_reference,
            'buyer' => [ 'name' => $order->buyer_name, 'email' => $order->buyer_email ],
            'event' => [ 'id' => $order->event?->id, 'title' => $order->event?->title ],
            'status' => $order->status,
            'quantity' => $order->quantity,
        ]);
    }

    // Backward-compatible endpoint; now behaves like verify (no state changes)
    public function redeem(Request $request)
    {
        $data = $request->validate([
            'code' => ['required','string','max:64'],
        ]);
        $ref = trim($data['code']);
        if (preg_match('/(PA_[A-Za-z0-9]{6,})/', $ref, $m)) { $ref = $m[1]; }

        $order = Order::with('event')->where('paystack_reference', $ref)->first();
        if (! $order) {
            return response()->json([
                'ok' => false,
                'status' => 'not_found',
                'message' => 'Order not found',
            ], 404);
        }
        return response()->json([
            'ok' => true,
            'kind' => 'order',
            'status' => $order->status,
            'reference' => $order->paystack_reference,
            'event' => [ 'id' => $order->event?->id, 'title' => $order->event?->title ],
            'buyer' => [ 'name' => $order->buyer_name, 'email' => $order->buyer_email ],
            'quantity' => $order->quantity,
        ]);
    }
}
