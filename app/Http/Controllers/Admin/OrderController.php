<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Event;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $eventId = $request->integer('event_id');
        $orders = Order::with('event')
            ->when($eventId, fn($q) => $q->where('event_id', $eventId))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $events = Event::orderBy('title')->get(['id','title']);
        return view('admin.orders.index', compact('orders','events','eventId'));
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
        $orders = Order::with('event')
            ->when($eventId, fn($q) => $q->where('event_id', $eventId))
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
}
