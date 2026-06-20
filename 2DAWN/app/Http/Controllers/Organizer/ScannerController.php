<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderCheckin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScannerController extends Controller
{
    public function index()
    {
        return view('organizer.scanner');
    }

    public function verify(Request $request)
    {
        $text = trim((string) $request->input('text', ''));
        $ref  = null;

        // Extract Paystack reference from QR payload or raw text
        if (preg_match('/(PA_[A-Za-z0-9]{6,})/', $text, $m)) {
            $ref = $m[1];
        } else {
            $ref = $text;
        }

        // Load the order with its event and existing check-ins
        $order = Order::with('event', 'checkins')
            ->where('paystack_reference', $ref)
            ->first();

        if (! $order) {
            return response()->json([
                'ok'      => false,
                'valid'   => false,
                'message' => 'Ticket not found.',
            ], 404);
        }

        // Ensure the ticket belongs to an event owned by this organizer
        if (! $order->event || $order->event->user_id !== Auth::id()) {
            return response()->json([
                'ok'      => false,
                'valid'   => false,
                'message' => 'This ticket does not belong to your event.',
            ], 403);
        }

        // Must be a paid order
        if ($order->status !== 'paid') {
            return response()->json([
                'ok'      => true,
                'valid'   => false,
                'message' => 'Ticket is not paid / not valid.',
                'buyer'   => ['name' => $order->buyer_name, 'email' => $order->buyer_email],
                'event'   => ['title' => $order->event->title],
            ]);
        }

        // Check how many times this ticket has already been used
        $used    = (int) $order->checkins()->sum('count');
        $allowed = max(0, (int) $order->quantity - $used);

        if ($allowed <= 0) {
            $last = optional($order->checkins()->latest('created_at')->first())->created_at;
            return response()->json([
                'ok'             => true,
                'valid'          => false,
                'already'        => true,
                'message'        => 'Already checked in.',
                'buyer'          => ['name' => $order->buyer_name, 'email' => $order->buyer_email],
                'event'          => ['title' => $order->event->title],
                'quantity'       => $order->quantity,
                'used'           => $used,
                'last_checkin_at'=> $last ? $last->toIso8601String() : null,
            ]);
        }

        // Record the check-in
        $now = now();
        OrderCheckin::create([
            'order_id'      => $order->id,
            'host_token_id' => null,
            'count'         => 1,
            'source'        => (string) $request->input('source', 'organizer_camera'),
        ]);

        $remaining = max(0, $allowed - 1);

        return response()->json([
            'ok'             => true,
            'valid'          => true,
            'buyer'          => ['name' => $order->buyer_name, 'email' => $order->buyer_email],
            'event'          => ['title' => $order->event->title],
            'quantity'       => $order->quantity,
            'remaining'      => $remaining,
            'last_checkin_at'=> $now->toIso8601String(),
        ]);
    }
}
