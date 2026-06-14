<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class EventController extends Controller
{
    public function index()
    {
        $events = Auth::user()->events()->latest()->get();
        return view('organizer.events.index', compact('events'));
    }

    public function create()
    {
        // Force return the correct view
        return view('organizer.events.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'starts_at' => 'required|date',
            'state' => 'required|string|max:50',
            'venue' => 'required|string|max:255',
            'price' => 'numeric|min:0',
            'ticket_types' => 'nullable|array',
            'ticket_types.*.name' => 'required_with:ticket_types|string|max:50',
            'ticket_types.*.price' => 'required_with:ticket_types|numeric|min:0',
        ]);

        $ticketTypes = null;
        if ($request->has('ticket_types') && is_array($request->ticket_types)) {
            $formattedTypes = [];
            foreach ($request->ticket_types as $type) {
                if (!empty($type['name'])) {
                    $formattedTypes[] = [
                        'name' => trim($type['name']),
                        'price' => (float) ($type['price'] ?? 0)
                    ];
                }
            }
            $ticketTypes = !empty($formattedTypes) ? $formattedTypes : null;
        }

        $event = Auth::user()->events()->create([
            'title' => $request->title,
            'description' => $request->description,
            'must_know' => $request->must_know,
            'state' => $request->state,
            'venue' => $request->venue,
            'starts_at' => $request->starts_at,
            'ends_at' => $request->ends_at,
            'price' => $request->input('price', 0),
            'capacity' => $request->capacity,
            'is_published' => true, // Auto publish for organizers
            'slug' => Str::slug($request->title) . '-' . Str::random(6),
            'ticket_types' => $ticketTypes,
        ]);

        return redirect()->route('organizer.dashboard')->with('status', 'Event created successfully and is now live!');
    }

    public function show(Event $event)
    {
        if ($event->user_id !== Auth::id()) {
            abort(403);
        }

        $orders = $event->orders()->where('status', 'paid')->latest()->paginate(15);
        $totalSold = $event->orders()->where('status', 'paid')->sum('quantity');
        $totalRevenue = $event->orders()->where('status', 'paid')->sum('amount');

        return view('organizer.events.show', compact('event', 'orders', 'totalSold', 'totalRevenue'));
    }

    public function edit(Event $event)
    {
        if ($event->user_id !== Auth::id()) {
            abort(403);
        }

        return view('organizer.events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        if ($event->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'starts_at' => 'required|date',
            'state' => 'required|string|max:50',
            'venue' => 'required|string|max:255',
            'price' => 'numeric|min:0',
            'ticket_types' => 'nullable|array',
            'ticket_types.*.name' => 'required_with:ticket_types|string|max:50',
            'ticket_types.*.price' => 'required_with:ticket_types|numeric|min:0',
        ]);

        $ticketTypes = null;
        if ($request->has('ticket_types') && is_array($request->ticket_types)) {
            $formattedTypes = [];
            foreach ($request->ticket_types as $type) {
                if (!empty($type['name'])) {
                    $formattedTypes[] = [
                        'name' => trim($type['name']),
                        'price' => (float) ($type['price'] ?? 0)
                    ];
                }
            }
            $ticketTypes = !empty($formattedTypes) ? $formattedTypes : null;
        }

        $event->update([
            'title' => $request->title,
            'description' => $request->description,
            'must_know' => $request->must_know,
            'state' => $request->state,
            'venue' => $request->venue,
            'starts_at' => $request->starts_at,
            'ends_at' => $request->ends_at,
            'price' => $request->input('price', 0),
            'capacity' => $request->capacity,
            'ticket_types' => $ticketTypes,
        ]);

        return redirect()->route('organizer.events.show', $event)->with('status', 'Event updated successfully!');
    }
}
