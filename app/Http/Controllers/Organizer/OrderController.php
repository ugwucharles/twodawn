<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $events = $user->events()->latest()->get();
        $eventIds = $events->pluck('id')->toArray();
        
        $orders = collect();
        if (count($eventIds) > 0) {
            $orders = Order::whereIn('event_id', $eventIds)
                ->where('status', 'paid')
                ->with('event')
                ->latest()
                ->paginate(20);
        }
        
        return view('organizer.orders.index', compact('orders', 'events'));
    }
}
