<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Event;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Mail;
use App\Mail\TicketMail;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $eventId = $request->integer('event_id');
        $from = $request->date('from');
        $to = $request->date('to');
        $orders = Order::with('event')
            ->when($eventId, fn($q) => $q->where('event_id', $eventId))
            ->when($from, fn($q) => $q->where('created_at', '>=', \Carbon\Carbon::parse($from)->startOfDay()))
            ->when($to, fn($q) => $q->where('created_at', '<=', \Carbon\Carbon::parse($to)->endOfDay()))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $events = Event::orderBy('title')->get(['id','title']);
        return view('admin.orders.index', compact('orders','events','eventId','from','to'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        $order->load('event');
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        //
    }

    public function export(Request $request): StreamedResponse
    {
        $eventId = $request->integer('event_id');
        $from = $request->date('from');
        $to = $request->date('to');
        $orders = Order::with('event')
            ->when($eventId, fn($q) => $q->where('event_id', $eventId))
            ->when($from, fn($q) => $q->where('created_at', '>=', \Carbon\Carbon::parse($from)->startOfDay()))
            ->when($to, fn($q) => $q->where('created_at', '<=', \Carbon\Carbon::parse($to)->endOfDay()))
            ->latest()
            ->get();

        $filename = 'orders'.($eventId ? ('_event_'.$eventId) : '').'_'.now()->format('Ymd_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        return response()->streamDownload(function () use ($orders) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date','Event','Buyer Name','Buyer Email','Buyer Phone','Qty','Amount (NGN)','Status','Reference']);
            foreach ($orders as $o) {
                fputcsv($out, [
                    optional($o->created_at)->format('Y-m-d H:i'),
                    optional($o->event)->title,
                    $o->buyer_name,
                    $o->buyer_email,
                    $o->buyer_phone,
                    $o->quantity,
                    number_format($o->amount/100, 2, '.', ''),
                    $o->status,
                    $o->paystack_reference,
                ]);
            }
            fclose($out);
        }, $filename, $headers);
    }

    public function exportSummary(Request $request): StreamedResponse
    {
        $eventId = $request->integer('event_id');
        $from = $request->date('from');
        $to = $request->date('to');
        $query = Order::query()->where('status','paid')
            ->when($eventId, fn($q) => $q->where('event_id', $eventId))
            ->when($from, fn($q) => $q->where('created_at', '>=', \Carbon\Carbon::parse($from)->startOfDay()))
            ->when($to, fn($q) => $q->where('created_at', '<=', \Carbon\Carbon::parse($to)->endOfDay()));

        // Aggregate per event
        $rows = $query->selectRaw('event_id, COUNT(*) as orders, SUM(quantity) as tickets, SUM(amount) as gross')
            ->groupBy('event_id')
            ->with('event:id,title')
            ->get();

        $filename = 'sales_summary'.($eventId ? ('_event_'.$eventId) : '').'_'.now()->format('Ymd_His').'.csv';
        $headers = [ 'Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"$filename\"" ];
        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Event ID','Event Title','Paid Orders','Tickets Sold','Gross (NGN)','Avg Price (NGN)']);
            foreach ($rows as $r) {
                $tickets = (int) ($r->tickets ?? 0);
                $gross = (int) ($r->gross ?? 0);
                $avg = $tickets > 0 ? number_format(($gross/100)/$tickets, 2, '.', '') : '0.00';
                fputcsv($out, [
                    $r->event_id,
                    optional($r->event)->title,
                    (int) ($r->orders ?? 0),
                    $tickets,
                    number_format($gross/100, 2, '.', ''),
                    $avg,
                ]);
            }
            fclose($out);
        }, $filename, $headers);
    }

    public function exportSummaryDaily(Request $request): StreamedResponse
    {
        $eventId = $request->integer('event_id');
        $from = $request->date('from');
        $to = $request->date('to');
        $query = Order::query()->where('status','paid')
            ->when($eventId, fn($q) => $q->where('event_id', $eventId))
            ->when($from, fn($q) => $q->where('created_at', '>=', \Carbon\Carbon::parse($from)->startOfDay()))
            ->when($to, fn($q) => $q->where('created_at', '<=', \Carbon\Carbon::parse($to)->endOfDay()));

        $rows = $query->selectRaw('event_id, DATE(created_at) as day, COUNT(*) as orders, SUM(quantity) as tickets, SUM(amount) as gross')
            ->groupBy('event_id', 'day')
            ->orderBy('day')
            ->with('event:id,title')
            ->get();

        $filename = 'sales_daily'.($eventId ? ('_event_'.$eventId) : '').'_'.now()->format('Ymd_His').'.csv';
        $headers = [ 'Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"$filename\"" ];
        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date','Event ID','Event Title','Paid Orders','Tickets Sold','Gross (NGN)','Avg Price (NGN)']);
            foreach ($rows as $r) {
                $tickets = (int) ($r->tickets ?? 0);
                $gross = (int) ($r->gross ?? 0);
                $avg = $tickets > 0 ? number_format(($gross/100)/$tickets, 2, '.', '') : '0.00';
                fputcsv($out, [
                    $r->day,
                    $r->event_id,
                    optional($r->event)->title,
                    (int) ($r->orders ?? 0),
                    $tickets,
                    number_format($gross/100, 2, '.', ''),
                    $avg,
                ]);
            }
            fclose($out);
        }, $filename, $headers);
    }

    public function resend(Order $order)
    {
        try {
            Mail::to($order->buyer_email)->send(new TicketMail($order));
            return back()->with('status', 'Ticket email resent to '.$order->buyer_email);
        } catch (\Throwable $e) {
            return back()->withErrors(['mail' => 'Resend failed: '.$e->getMessage()]);
        }
    }
}
