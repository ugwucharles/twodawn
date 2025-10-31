<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderCheckin;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;

class TicketScanController extends Controller
{
    public function index()
    {
        return view('admin.scanner');
    }

    // Verify order QR or reference (no mutation)
    public function verify(Request $request)
    {
        $text = trim((string) $request->input('text', ''));
        $ref = null;
        if (preg_match('/(PA_[A-Za-z0-9]{6,})/', $text, $m)) { $ref = $m[1]; } else { $ref = $text; }

        // Local/test override: accept a fixed reference for easy manual testing without DB
        $adminTestRef = strtoupper((string) env('ADMIN_TEST_REFERENCE', 'PA_1234567'));
        if (app()->environment(['local','testing']) && strtoupper($ref) === $adminTestRef) {
            return response()->json([
                'ok' => true,
                'valid' => true,
                'type' => 'order',
                'reference' => $adminTestRef,
                'buyer' => [ 'name' => 'Test User', 'email' => 'test@example.com' ],
                'event' => [ 'id' => null, 'title' => 'Test Event' ],
                'status' => 'paid',
                'quantity' => 1,
                'last_checkin_at' => null,
            ]);
        }

        $order = Order::with('event','checkins')->where('paystack_reference', $ref)->first();
        if (! $order) {
            return response()->json(['ok'=>false,'valid'=>false,'type'=>'order','message'=>'Order not found'], 404);
        }
        $valid = $order->status === 'paid';
        $last = optional($order->checkins()->latest('created_at')->first())->created_at;
        return response()->json([
            'ok' => true,
            'valid' => $valid,
            'type' => 'order',
            'reference' => $order->paystack_reference,
            'buyer' => [ 'name' => $order->buyer_name, 'email' => $order->buyer_email ],
            'event' => [ 'id' => $order->event?->id, 'title' => $order->event?->title ],
            'status' => $order->status,
            'quantity' => $order->quantity,
            'last_checkin_at' => $last ? $last->toIso8601String() : null,
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

    public function export(Request $request): StreamedResponse
    {
        $eventId = $request->integer('event_id');
        $from = $request->date('from');
        $to = $request->date('to');
        $checkins = OrderCheckin::with([
                'order' => function($q){ $q->select('id','buyer_name','buyer_email','paystack_reference','event_id'); },
                'order.event' => function($q){ $q->select('id','title'); }
            ])
            ->when($eventId, function($q) use ($eventId){ $q->whereHas('order', fn($qq)=>$qq->where('event_id',$eventId)); })
            ->when($from, fn($q) => $q->where('created_at', '>=', Carbon::parse($from)->startOfDay()))
            ->when($to, fn($q) => $q->where('created_at', '<=', Carbon::parse($to)->endOfDay()))
            ->orderByDesc('created_at')
            ->get();

        $filename = 'checkins'.($eventId ? ('_event_'.$eventId) : '').'_'.now()->format('Ymd_His').'.csv';
        $headers = [ 'Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"$filename\"" ];
        return response()->streamDownload(function () use ($checkins) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Time','Event','Buyer Name','Buyer Email','Reference','Count','Source']);
            foreach ($checkins as $c) {
                fputcsv($out, [
                    optional($c->created_at)->format('Y-m-d H:i'),
                    optional($c->order?->event)->title,
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
}
