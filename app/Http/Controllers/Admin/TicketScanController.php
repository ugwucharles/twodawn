<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;

class TicketScanController extends Controller
{
    public function index()
    {
        return view('admin.tickets.scanner');
    }

    public function redeem(Request $request)
    {
        $data = $request->validate([
            'code' => ['required','string','max:64'],
        ]);
        $code = trim($data['code']);

        $ticket = Ticket::with(['event','order'])->where('code', $code)->first();
        if (! $ticket) {
            return response()->json([
                'ok' => false,
                'status' => 'not_found',
                'message' => 'Ticket not found',
            ], 404);
        }

        $already = !is_null($ticket->redeemed_at);
        if (! $already) {
            $ticket->redeemed_at = now();
            $ticket->save();
        }

        return response()->json([
            'ok' => true,
            'status' => $already ? 'already_redeemed' : 'redeemed',
            'code' => $ticket->code,
            'redeemed_at' => optional($ticket->redeemed_at)->toIso8601String(),
            'event' => [
                'id' => $ticket->event?->id,
                'title' => $ticket->event?->title,
                'starts_at' => optional($ticket->event?->starts_at)->toIso8601String(),
            ],
            'order' => [
                'id' => $ticket->order?->id,
                'buyer_name' => $ticket->order?->buyer_name,
                'buyer_email' => $ticket->order?->buyer_email,
            ],
        ]);
    }
}