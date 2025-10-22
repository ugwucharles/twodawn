<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    public function togglePublish(Event $event)
    {
        $event->update(['is_published' => ! $event->is_published]);
        return redirect()->route('admin.events.index')->with('status', 'Event '.($event->is_published ? 'published' : 'unpublished'));
    }

    public function togglePublishJson(Request $request, Event $event)
    {
        $event->update(['is_published' => ! $event->is_published]);
        return response()->json(['id' => $event->id, 'is_published' => $event->is_published]);
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'venue' => ['nullable','string','max:255'],
            'mood' => ['required','string', Rule::in(config('moods.list', ['Rave','Romantic','Amapiano','Afrobeats','Hip‑Hop','House','Live Band','Jazz','Techno','Gospel','Comedy','Networking']))],
            'starts_at' => ['required','date'],
            'ends_at' => ['nullable','date','after_or_equal:starts_at'],
            'price' => ['nullable','numeric','min:0'],
            'capacity' => ['nullable','integer','min:1'],
            'early_bird_price' => ['nullable','numeric','min:0'],
            'early_bird_ends_at' => ['nullable','date'],
            'is_published' => ['sometimes','boolean'],
            'image' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
        ]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $events = Event::query()->latest('starts_at')->paginate(10);
        return view('admin.events.index', compact('events'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $event = new Event();
        return view('admin.events.create', compact('event'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['is_published'] = $request->boolean('is_published');

if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->storePublicly('events');
        }

        Event::create($data);
        return redirect()->route('admin.events.index')->with('status', 'Event created');
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event)
    {
        return view('admin.events.edit', compact('event'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        $data = $this->validated($request);
        $data['is_published'] = $request->boolean('is_published');

if ($request->hasFile('image')) {
            // Optionally delete old image
            // if ($event->image_path) Storage::delete($event->image_path);
            $data['image_path'] = $request->file('image')->storePublicly('events');
        }

        $event->update($data);
        return redirect()->route('admin.events.index')->with('status', 'Event updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $event->delete();
        return redirect()->route('admin.events.index')->with('status', 'Event deleted');
    }
}
