<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Order;
use App\Models\HostToken;
use App\Models\OrderCheckin;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HostPanelController extends Controller
{
    // Create a token (admin-only; for now expose via route callable within tinker or later add UI)
    public static function generateToken(Event $event, ?string $label = null): HostToken
    {
        $token = new HostToken([
            'event_id' => $event->id,
            'token' => 'H_'.Str::random(24),
            'label' => $label,
            'active' => true,
            'expires_at' => optional($event->ends_at)->copy()->addDay(),
        ]);
        $token->save();
        return $token;
    }

    public function show(string $token, Request $request)
    {
        $host = HostToken::with('event')->where('token', $token)->firstOrFail();
        $event = $host->event;
        if (! $host->active || ($host->expires_at && now()->gt($host->expires_at))) {
            abort(410, 'This link has expired.');
        }
        // stats
        $sold = Order::where('event_id', $event->id)->where('status','paid')->sum('quantity');
        $checked = OrderCheckin::whereHas('order', function($q) use($event){ $q->where('event_id',$event->id)->where('status','paid'); })->sum('count');
        $remaining = max(0, (int) $sold - (int) $checked);
        return view('host.panel', compact('host','event','sold','checked','remaining'));
    }

    public function people(string $token)
    {
        $host = HostToken::with('event')->where('token', $token)->firstOrFail();
        if (! $host->active || ($host->expires_at && now()->gt($host->expires_at))) {
            abort(410, 'This link has expired.');
        }
        $event = $host->event;
        $checkins = OrderCheckin::with(['order' => function($q){ $q->select('id','buyer_name','buyer_email','paystack_reference','event_id'); }])
            ->whereHas('order', function($q) use ($event){ $q->where('event_id', $event->id)->where('status','paid'); })
            ->orderByDesc('created_at')
            ->paginate(25);
        return view('host.people', compact('host','event','checkins'));
    }

    public function verify(string $token, Request $request)
    {
        $host = HostToken::with('event')->where('token', $token)->first();
        if (! $host || ! $host->active || ($host->expires_at && now()->gt($host->expires_at))) {
            return response()->json(['ok'=>false,'message'=>'Expired or invalid link'], 410);
        }
        $text = (string) $request->input('text','');
        $ref = null;
        if (preg_match('/(PA_[A-Za-z0-9]{6,})/', $text, $m)) { $ref = $m[1]; } else { $ref = $text; }
        $order = Order::with('event','checkins')->where('paystack_reference', $ref)->first();
        if (! $order || $order->event_id !== $host->event_id || $order->status !== 'paid') {
            return response()->json(['ok'=>true,'valid'=>false,'message'=>'Invalid ticket'], 200);
        }
        // how many already used
        $used = (int) $order->checkins()->sum('count');
        $allowed = max(0, (int) $order->quantity - $used);
        if ($allowed <= 0) {
            return response()->json([
                'ok'=>true,
                'valid'=>false,
                'already'=>true,
                'buyer' => ['name'=>$order->buyer_name,'email'=>$order->buyer_email],
                'event' => ['title'=>$order->event?->title],
                'remaining'=>0,
            ], 200);
        }
        // record one check-in by default
        OrderCheckin::create([
            'order_id' => $order->id,
            'host_token_id' => $host->id,
            'count' => 1,
            'source' => (string) $request->input('source','camera'),
        ]);
        $remaining = max(0, $allowed - 1);
        return response()->json([
            'ok'=>true,
            'valid'=>true,
            'buyer' => ['name'=>$order->buyer_name,'email'=>$order->buyer_email],
            'event' => ['title'=>$order->event?->title],
            'remaining'=>$remaining,
        ]);
    }
}
