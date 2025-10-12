<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Order;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'events_total' => Event::count(),
            'events_published' => Event::where('is_published', true)->count(),
            'orders_today' => Order::whereDate('created_at', today())->count(),
            'tickets_today' => Order::where('status','paid')->whereDate('created_at', today())->sum('quantity'),
            'revenue_today' => Order::where('status','paid')->whereDate('created_at', today())->sum('amount'),
        ];

        // Chart data: last 14 days
        $labels = [];
        $ticketsSeries = [];
        $revenueSeries = [];
        for ($i = 13; $i >= 0; $i--) {
            $day = today()->subDays($i);
            $labels[] = $day->format('M j');
            $ticketsSeries[] = (int) Order::where('status','paid')->whereDate('created_at', $day)->sum('quantity');
            $revenueSeries[] = (int) Order::where('status','paid')->whereDate('created_at', $day)->sum('amount');
        }
        $chart = [
            'labels' => $labels,
            'tickets' => $ticketsSeries,
            'revenue' => $revenueSeries,
        ];

        $upcoming = Event::query()
            ->where('is_published', true)
            ->where(function($q){ $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()); })
            ->orderBy('starts_at')
            ->take(6)
            ->get(['id','title','starts_at','venue','is_published']);

        return view('admin.dashboard.index', compact('stats','upcoming','chart'));
    }
}
