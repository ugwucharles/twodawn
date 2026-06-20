<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Support\Facades\Storage;

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
            'pass_fees_to_buyer' => 'nullable|boolean',
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

        // Handle event flyer upload (local storage only)
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('events', 'public');
        }

        $createData = [
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
            'pass_fees_to_buyer' => $request->boolean('pass_fees_to_buyer'),
        ];
        if ($imagePath) {
            $createData['image_path'] = $imagePath;
        }

        $event = Auth::user()->events()->create($createData);

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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'ticket_types' => 'nullable|array',
            'ticket_types.*.name' => 'required_with:ticket_types|string|max:50',
            'ticket_types.*.price' => 'required_with:ticket_types|numeric|min:0',
            'pass_fees_to_buyer' => 'nullable|boolean',
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

        $updateData = [
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
            'pass_fees_to_buyer' => $request->boolean('pass_fees_to_buyer'),
        ];

        // Handle flyer image replacement (local storage only)
        if ($request->hasFile('image')) {
            $updateData['image_path'] = $request->file('image')->store('events', 'public');
        }

                $event->update($updateData);
        return redirect()->route('organizer.events.show', $event)->with('status', 'Event updated successfully!');
    }

    public function destroy(Event $event)
    {
        if ($event->user_id !== Auth::id()) {
            abort(403);
        }

        // Delete the event image if it exists
        if ($event->image_path) {
            try {
                Storage::disk('public')->delete($event->image_path);
            } catch (\Exception $e) {
                // Continue even if image deletion fails
            }
        }

        $event->delete();

        return redirect()->route('organizer.events.index')->with('status', 'Event deleted successfully!');
    }
}
