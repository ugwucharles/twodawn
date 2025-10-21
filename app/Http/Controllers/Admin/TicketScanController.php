<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\Order;

class TicketScanController extends Controller
{
    public function index()
    {
        return view('admin.tickets.scanner');
    }

    // New: verify only (no mutation)
    public function verify(Request $request)
    {
        $text = (string) $request->input('text', '');
        $text = trim($text);
        $code = null;
        if (preg_match('/(T\-[A-Z0-9]{6,})/i', $text, $m)) { $code = strtoupper($m[1]); }
        elseif (preg_match('/(PA_[A-Za-z0-9]{6,})/', $text, $m)) { $code = $m[1]; }
        else { $code = $text; }

        if (str_starts_with($code, 'T-')) {
            $ticket = Ticket::with(['event','order'])->where('code', $code)->first();
            if (! $ticket) {
                return response()->json(['ok'=>false,'valid'=>false,'type'=>'ticket','message'=>'Ticket not found'], 404);
            }
            $order = $ticket->order;
            $valid = $order && $order->status === 'paid';
            return response()->json([
                'ok' => true,
                'valid' => $valid,
                'type' => 'ticket',
                'code' => $ticket->code,
                'redeemed_at' => optional($ticket->redeemed_at)->toIso8601String(),
                'buyer' => [ 'name' => $order?->buyer_name, 'email' => $order?->buyer_email ],
                'event' => [ 'id' => $ticket->event?->id, 'title' => $ticket->event?->title ],
                'status' => $order?->status,
            ]);
        }

        $order = Order::with('event')->where('paystack_reference', $code)->first();
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

    public function redeem(Request $request)
    {
        $data = $request->validate([
            'code' => ['required','string','max:64'],
        ]);
        $code = trim($data['code']);

        // Handle ticket code (T-...) or order reference (PA_...)
        if (str_starts_with($code, 'T-')) {
            $ticket = Ticket::with(['event','order'])->where('code', $code)->first();
            if (! $ticket) {
                return response()->json([
                    'ok' => false,
                    'status' => 'not_found',
                    'message' => 'Ticket not found',
                ], 404);
            }
            $already = !is_null($ticket->redeemed_at);
            if (! $already) { $ticket->redeemed_at = now(); $ticket->save(); }
            return response()->json([
                'ok' => true,
                'kind' => 'ticket',
                'status' => $already ? 'already_redeemed' : 'redeemed',
                'code' => $ticket->code,
                'redeemed_at' => optional($ticket->redeemed_at)->toIso8601String(),
                'event' => [ 'id' => $ticket->event?->id, 'title' => $ticket->event?->title ],
                'buyer' => [ 'name' => $ticket->order?->buyer_name, 'email' => $ticket->order?->buyer_email ],
            ]);
        }

        // Otherwise treat as order reference
        $order = Order::with('event')->where('paystack_reference', $code)->first();
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