<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $events = $user->events()->latest()->get();
        $eventIds = $events->pluck('id')->toArray();

        $totalEvents = count($events);
        $totalTicketsSold = 0;
        $totalRevenue = 0;
        $upcomingEvents = 0;

        if ($totalEvents > 0) {
            $totalTicketsSold = Order::whereIn('event_id', $eventIds)->where('status', 'paid')->sum('quantity');
            $totalRevenue = Order::whereIn('event_id', $eventIds)->where('status', 'paid')->sum('amount');
            $upcomingEvents = $user->events()->where('starts_at', '>', now())->count();
        }

        // Revenue Statistics (Monthly for the current year)
        $revenueStats = [];
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        for ($i = 1; $i <= 12; $i++) {
            $val = 0;
            if ($totalEvents > 0) {
                $val = Order::whereIn('event_id', $eventIds)
                    ->where('status', 'paid')
                    ->whereYear('created_at', date('Y'))
                    ->whereMonth('created_at', $i)
                    ->sum('amount');
            }
            $revenueStats[] = $val / 100;
        }

        // Sales Statistics
        $totalCapacity = $user->events()->sum('capacity');
        // If capacity is 0/null across all events, show 0
        if (!$totalCapacity) {
            $totalCapacity = 0;
        }
        $leftTickets = max(0, $totalCapacity - $totalTicketsSold);

        $recentOrders = collect();
        if ($totalEvents > 0) {
            $recentOrders = Order::whereIn('event_id', $eventIds)
                ->where('status', 'paid')
                ->with('event')
                ->latest()
                ->take(5)
                ->get();
        }

        return view('organizer.dashboard.index', compact(
            'events',
            'totalEvents',
            'totalTicketsSold',
            'totalRevenue',
            'upcomingEvents',
            'revenueStats',
            'months',
            'totalCapacity',
            'leftTickets',
            'recentOrders'
        ));
    }
}
