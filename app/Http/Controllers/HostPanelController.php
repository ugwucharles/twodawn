<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Order;
use App\Models\HostToken;
use App\Models\OrderCheckin;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
        $sold = \App\Models\Order::where('event_id', $event->id)->where('status','paid')->sum('quantity');
        $checked = OrderCheckin::whereHas('order', function($q) use($event){ $q->where('event_id',$event->id)->where('status','paid'); })->sum('count');
        $remaining = max(0, (int) $sold - (int) $checked);

        $checkins = OrderCheckin::with(['order' => function($q){ $q->select('id','buyer_name','buyer_email','paystack_reference','event_id'); }])
            ->whereHas('order', function($q) use ($event){ $q->where('event_id', $event->id)->where('status','paid'); })
            ->orderByDesc('created_at')
            ->paginate(25);
        return view('host.people', compact('host','event','checkins','sold','checked','remaining'));
    }

    public function exportCheckins(string $token): StreamedResponse
    {
        $host = \App\Models\HostToken::with('event')->where('token', $token)->firstOrFail();
        $from = request()->date('from') ?: now()->subMonth();
        $to = request()->date('to') ?: now();
        if (! $host->active || ($host->expires_at && now()->gt($host->expires_at))) {
            abort(410, 'This link has expired.');
        }
        $event = $host->event;
        $checkins = \App\Models\OrderCheckin::with(['order' => function($q){ $q->select('id','buyer_name','buyer_email','paystack_reference','event_id'); }])
            ->whereHas('order', function($q) use ($event){ $q->where('event_id',$event->id)->where('status','paid'); })
            ->when($from, fn($q) => $q->where('created_at', '>=', \Carbon\Carbon::parse($from)->startOfDay()))
            ->when($to, fn($q) => $q->where('created_at', '<=', \Carbon\Carbon::parse($to)->endOfDay()))
            ->orderByDesc('created_at')
            ->get();
        $filename = 'checkins_event_'.$event->id.'_'.now()->format('Ymd_His').'.csv';
        $headers = [ 'Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"$filename\"" ];
        return response()->streamDownload(function () use ($checkins, $event) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Event','Time','Buyer Name','Buyer Email','Reference','Count','Source']);
            foreach ($checkins as $c) {
                fputcsv($out, [
                    $event->title,
                    optional($c->created_at)->format('Y-m-d H:i'),
                    $c->order?->buyer_name,
                    $c->order?->buyer_email,
                    $c->order?->paystack_reference,
                    (int) $c->count,
                    $c->source,
                ]);
            }
            fclose($out);
        }, $filename, $headers);
    }

    public function exportSales(string $token): StreamedResponse
    {
        $host = \App\Models\HostToken::with('event')->where('token', $token)->firstOrFail();
        $from = request()->date('from');
        $to = request()->date('to');
        if (! $host->active || ($host->expires_at && now()->gt($host->expires_at))) {
            abort(410, 'This link has expired.');
        }
        $event = $host->event;
        $orders = \App\Models\Order::where('event_id', $event->id)->where('status','paid')
            ->when($from, fn($q) => $q->where('created_at', '>=', \Carbon\Carbon::parse($from)->startOfDay()))
            ->when($to, fn($q) => $q->where('created_at', '<=', \Carbon\Carbon::parse($to)->endOfDay()))
            ->get(['quantity','amount','created_at']);
        $tickets = (int) $orders->sum('quantity');
        $gross = (int) $orders->sum('amount');
        $filename = 'sales_summary_event_'.$event->id.'_'.now()->format('Ymd_His').'.csv';
        $headers = [ 'Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"$filename\"" ];
        return response()->streamDownload(function () use ($event, $tickets, $gross) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Event Title','Tickets Sold','Gross (NGN)','Avg Price (NGN)']);
            $avg = $tickets > 0 ? number_format(($gross/100)/$tickets, 2, '.', '') : '0.00';
            fputcsv($out, [$event->title, $tickets, number_format($gross/100, 2, '.', ''), $avg]);
            fclose($out);
        }, $filename, $headers);
    }

    public function exportSalesDaily(string $token): StreamedResponse
    {
        $host = \App\Models\HostToken::with('event')->where('token', $token)->firstOrFail();
        $from = request()->date('from');
        $to = request()->date('to');
        if (! $host->active || ($host->expires_at && now()->gt($host->expires_at))) {
            abort(410, 'This link has expired.');
        }
        $event = $host->event;
        $rows = \App\Models\Order::where('event_id', $event->id)->where('status','paid')
            ->when($from, fn($q) => $q->where('created_at', '>=', \Carbon\Carbon::parse($from)->startOfDay()))
            ->when($to, fn($q) => $q->where('created_at', '<=', \Carbon\Carbon::parse($to)->endOfDay()))
            ->selectRaw('DATE(created_at) as day, COUNT(*) as orders, SUM(quantity) as tickets, SUM(amount) as gross')
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $filename = 'sales_daily_event_'.$event->id.'_'.now()->format('Ymd_His').'.csv';
        $headers = [ 'Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"$filename\"" ];
        return response()->streamDownload(function () use ($event, $rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date','Event Title','Paid Orders','Tickets Sold','Gross (NGN)','Avg Price (NGN)']);
            foreach ($rows as $r) {
                $tickets = (int) ($r->tickets ?? 0);
                $gross = (int) ($r->gross ?? 0);
                $avg = $tickets > 0 ? number_format(($gross/100)/$tickets, 2, '.', '') : '0.00';
                fputcsv($out, [
                    $r->day,
                    $event->title,
                    (int) ($r->orders ?? 0),
                    $tickets,
                    number_format($gross/100, 2, '.', ''),
                    $avg,
                ]);
            }
            fclose($out);
        }, $filename, $headers);
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

        // Temporary test reference support (local/testing only)
        $refNorm = strtoupper($ref);
        $testRef1 = strtoupper((string) env('HOST_TEST_REFERENCE', 'PA_ab12cd34ef56'));
        $testRef2 = strtoupper((string) env('HOST_TEST_REFERENCE_2', 'PA_1234567'));
        if (app()->environment(['local','testing']) && ($refNorm === $testRef1 || $refNorm === $testRef2)) {
            // Create or fetch a paid stub order for this event
            $order = Order::firstOrCreate(
                ['paystack_reference' => $refNorm],
                [
                    'event_id' => $host->event_id,
                    'buyer_name' => 'Test User',
                    'buyer_email' => 'test@example.com',
                    'quantity' => 1,
                    'amount' => 0,
                    'status' => 'paid',
                ]
            );
            // Ensure status paid even if existed
            if ($order->status !== 'paid') { $order->update(['status' => 'paid']); }
        } else {
            $order = Order::with('event','checkins')->where('paystack_reference', $ref)->first();
        }
        if (! $order || $order->event_id !== $host->event_id || $order->status !== 'paid') {
            return response()->json(['ok'=>true,'valid'=>false,'message'=>'Invalid ticket'], 200);
        }
        // how many already used
        $used = (int) $order->checkins()->sum('count');
        $allowed = max(0, (int) $order->quantity - $used);
        if ($allowed <= 0) {
            $last = optional($order->checkins()->latest('created_at')->first())->created_at;
            return response()->json([
                'ok'=>true,
                'valid'=>false,
                'already'=>true,
                'buyer' => ['name'=>$order->buyer_name,'email'=>$order->buyer_email],
                'event' => ['title'=>$order->event?->title],
                'remaining'=>0,
                'last_checkin_at' => $last ? $last->toIso8601String() : null,
            ], 200);
        }
        // record one check-in by default
        $now = now();
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
            'last_checkin_at' => $now->toIso8601String(),
        ]);
    }
}
