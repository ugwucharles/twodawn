<?php

namespace App\Http\Controllers;

use App\Models\Event;

use Illuminate\Http\Request;

class EventPublicController extends Controller
{
    public function landing()
    {
        $featuredEvents = Event::query()->where('is_published', true)
            ->where(function($q){ $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()); })
            ->orderBy('starts_at')
            ->take(5)
            ->get();

        $recentEvents = Event::query()->where('is_published', true)
            ->where(function($q){
                $q->where(function($q2){
                    $q2->whereNotNull('ends_at')
                       ->whereBetween('ends_at', [now()->subMonth(), now()]);
                })->orWhere(function($q3){
                    $q3->whereNull('ends_at')
                       ->whereBetween('starts_at', [now()->subMonth(), now()]);
                });
            })
            ->orderByDesc('starts_at')
            ->take(5)
            ->get();

        $stats = [
            'events_count' => Event::where('is_published', true)->count(),
            'tickets_sold' => \App\Models\Order::where('status','paid')->sum('quantity'),
        ];

        return view('landing', compact('featuredEvents','recentEvents','stats'));
    }

    public function index()
    {
        $events = Event::query()->where('is_published', true)->where(function($q){
            $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
        })->orderBy('starts_at')->paginate(12);

        $recentEvents = Event::query()->where('is_published', true)
            ->where(function($q){
                $q->where(function($q2){
                    $q2->whereNotNull('ends_at')
                       ->whereBetween('ends_at', [now()->subMonth(), now()]);
                })->orWhere(function($q3){
                    $q3->whereNull('ends_at')
                       ->whereBetween('starts_at', [now()->subMonth(), now()]);
                });
            })
            ->orderByDesc('starts_at')
            ->take(9)
            ->get();

        return view('events.index', compact('events','recentEvents'));
}

    public function recent()
    {
        $recentEvents = Event::query()->where('is_published', true)
            ->where(function($q){
                $q->where(function($q2){
                    $q2->whereNotNull('ends_at')
                       ->whereBetween('ends_at', [now()->subMonth(), now()]);
                })->orWhere(function($q3){
                    $q3->whereNull('ends_at')
                       ->whereBetween('starts_at', [now()->subMonth(), now()]);
                });
            })
            ->orderByDesc('starts_at')
            ->paginate(12);

        return view('events.recent', compact('recentEvents'));
    }

    public function show(Event $event)
    {
        abort_unless($event->is_published, 404);
        $event->load(['comments' => function($q){
            $q->where('approved', true)->latest();
        }]);
        return view('events.show', compact('event'));
    }

    public function remaining(Event $event)
    {
        abort_unless($event->is_published, 404);
        $now = now();
        $past = ($event->ends_at && $event->ends_at->lt($now)) || (!$event->ends_at && $event->starts_at && $event->starts_at->lt($now));
        $remaining = is_null($event->capacity) ? null : max(0, (int)$event->capacity);
        return response()->json([
            'id' => $event->id,
            'remaining' => $remaining,
            'status' => $past ? 'past' : 'upcoming',
        ]);
    }
}
