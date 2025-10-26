<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use App\Services\LoggerService;

class EventController extends Controller
{
    public function togglePublish(Event $event)
    {
        $oldStatus = $event->is_published;
        $event->update(['is_published' => ! $event->is_published]);
        
        LoggerService::logAdminAction('Event publish status toggled', auth()->id(), [
            'event_id' => $event->id,
            'event_title' => $event->title,
            'old_status' => $oldStatus,
            'new_status' => $event->is_published,
        ]);
        
        return redirect()->route('admin.events.index')->with('status', 'Event '.($event->is_published ? 'published' : 'unpublished'));
    }

    public function togglePublishJson(Request $request, Event $event)
    {
        $event->update(['is_published' => ! $event->is_published]);
        return response()->json(['id' => $event->id, 'is_published' => $event->is_published]);
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
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

        // Sanitize string inputs
        $data['title'] = trim(strip_tags($data['title']));
        $data['description'] = $data['description'] ? trim($data['description']) : null;
        $data['venue'] = $data['venue'] ? trim(strip_tags($data['venue'])) : null;

        return $data;
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
        try {
            $data = $this->validated($request);
            $data['is_published'] = $request->boolean('is_published');

            if ($request->hasFile('image')) {
                try {
                    // Try Cloudinary first (production preferred)
                    $upload = Cloudinary::uploadFile($request->file('image')->getRealPath(), ['folder' => '2dawn/events']);
                    $data['image_path'] = $upload->getSecurePath();
                    
                    LoggerService::logImageUpload('Event image uploaded to Cloudinary', [
                        'event_id' => $event->id,
                        'file_name' => $request->file('image')->getClientOriginalName(),
                        'file_size' => $request->file('image')->getSize(),
                        'cloudinary_url' => $upload->getSecurePath(),
                    ]);
                } catch (\Throwable $e) {
                    // Log the error for debugging
                    LoggerService::logImageUpload('Cloudinary upload failed, falling back to local storage', [
                        'event_id' => $event->id,
                        'file_name' => $request->file('image')->getClientOriginalName(),
                        'error' => $e->getMessage(),
                    ]);
                    
                    // Fallback to configured storage disk
                    $data['image_path'] = $request->file('image')->storePublicly('events');
                    
                    LoggerService::logImageUpload('Event image uploaded to local storage', [
                        'event_id' => $event->id,
                        'file_name' => $request->file('image')->getClientOriginalName(),
                        'local_path' => $data['image_path'],
                    ]);
                }
            }

            Event::create($data);
            return redirect()->route('admin.events.index')->with('status', 'Event created successfully!');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Re-throw validation exceptions to show form errors
            throw $e;
        } catch (\Exception $e) {
            // Log unexpected errors
            \Log::error('Failed to create event', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['general' => 'Failed to create event. Please try again.'])->withInput();
        }
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
        try {
            $data = $this->validated($request);
            $data['is_published'] = $request->boolean('is_published');

            if ($request->hasFile('image')) {
                // Optionally delete old image (local)
                // if ($event->image_path && !str_starts_with($event->image_path, 'http')) Storage::delete($event->image_path);
                try {
                    // Try Cloudinary first (production preferred)
                    $upload = Cloudinary::uploadFile($request->file('image')->getRealPath(), ['folder' => '2dawn/events']);
                    $data['image_path'] = $upload->getSecurePath();
                } catch (\Throwable $e) {
                    // Log the error for debugging
                    \Log::warning('Cloudinary upload failed, falling back to local storage', [
                        'error' => $e->getMessage(),
                        'file' => $request->file('image')->getClientOriginalName(),
                        'event_id' => $event->id
                    ]);
                    
                    // Fallback to configured storage disk
                    $data['image_path'] = $request->file('image')->storePublicly('events');
                }
            }

            $event->update($data);
            return redirect()->route('admin.events.index')->with('status', 'Event updated successfully!');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Re-throw validation exceptions to show form errors
            throw $e;
        } catch (\Exception $e) {
            // Log unexpected errors
            \Log::error('Failed to update event', [
                'error' => $e->getMessage(),
                'event_id' => $event->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['general' => 'Failed to update event. Please try again.'])->withInput();
        }
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
