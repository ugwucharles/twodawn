<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class PublicApiController extends Controller
{
    // GET /api/v1/events
    public function events(Request $request)
    {
        $q = Event::query()
            ->where('is_published', true)
            ->when($request->query('upcoming', '1') === '1', function($q){
                $q->where(function($qq){
                    $now = now();
                    $qq->whereNull('ends_at')->where('starts_at', '>=', $now)
                       ->orWhere(function($ww) use ($now){
                           $ww->whereNotNull('ends_at')->where('ends_at', '>=', $now);
                       });
                });
            })
            ->orderBy('starts_at')
            ->limit(min(100, (int) $request->query('limit', 50)));

        $items = $q->with('user:id,name,username')->get(['id','title','venue','starts_at','ends_at','price','slug','use_custom_slug','user_id','description','capacity','ticket_types','image_path','state'])
            ->map(function($e){
                return [
                    'id' => $e->id,
                    'title' => $e->title,
                    'venue' => $e->venue,
                    'starts_at' => optional($e->starts_at)->toIso8601String(),
                    'ends_at' => optional($e->ends_at)->toIso8601String(),
                    'price' => (float) $e->price,
                    'url' => $e->public_url,
                    'organizer_name' => $e->user ? $e->user->name : null,
                    'organizer_username' => $e->user ? $e->user->username : null,
                    'description' => $e->description,
                    'capacity' => $e->capacity,
                    'ticket_types' => $e->ticket_types,
                    'image_path' => $e->image_path,
                    'state' => $e->state,
                ];
            });

        return response()->json(['ok' => true, 'events' => $items]);
    }

    // GET /api/v1/events/{id}
    public function show(Request $request, $id)
    {
        $event = Event::where('is_published', true)
            ->with('user:id,name,username,profile_picture')
            ->findOrFail($id);

        return response()->json([
            'ok' => true,
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'venue' => $event->venue,
                'starts_at' => optional($event->starts_at)->toIso8601String(),
                'ends_at' => optional($event->ends_at)->toIso8601String(),
                'price' => (float) $event->price,
                'url' => $event->public_url,
                'organizer_name' => $event->user ? $event->user->name : null,
                'organizer_username' => $event->user ? $event->user->username : null,
                'organizer_profile_picture' => $event->user ? $event->user->profile_picture : null,
                'description' => $event->description,
                'capacity' => $event->capacity,
                'ticket_types' => $event->ticket_types,
                'image_path' => $event->image_path,
                'state' => $event->state,
                'early_bird_price' => (float) ($event->early_bird_price ?? 0),
                'early_bird_ends_at' => optional($event->early_bird_ends_at)->toIso8601String(),
                'pass_fees_to_buyer' => $event->pass_fees_to_buyer,
            ]
        ]);
    }

    // GET /api/v1/events/top-selling
    public function topSelling(Request $request)
    {
        $limit = min(10, (int) $request->query('limit', 6));
        $since = now()->subDays(30);

        $topIds = \App\Models\Order::select('event_id', \DB::raw('SUM(quantity) as qty'))
            ->where('status', 'paid')
            ->where('created_at', '>=', $since)
            ->groupBy('event_id')
            ->orderByDesc('qty')
            ->limit($limit)
            ->pluck('event_id')
            ->toArray();

        if (empty($topIds)) {
            $events = Event::where('is_published', true)
                ->where(function($q){
                    $now = now();
                    $q->whereNull('ends_at')->where('starts_at', '>=', $now)
                       ->orWhere(function($ww) use ($now){
                           $ww->whereNotNull('ends_at')->where('ends_at', '>=', $now);
                       });
                })
                ->orderBy('starts_at')
                ->limit($limit)
                ->get();
        } else {
            $events = Event::where('is_published', true)
                ->whereIn('id', $topIds)
                ->limit($limit)
                ->get();
        }

        $items = $events->load('user:id,name,username')->map(function($e){
            return [
                'id' => $e->id,
                'title' => $e->title,
                'venue' => $e->venue,
                'starts_at' => optional($e->starts_at)->toIso8601String(),
                'ends_at' => optional($e->ends_at)->toIso8601String(),
                'price' => (float) $e->price,
                'url' => $e->public_url,
                'organizer_name' => $e->user ? $e->user->name : null,
                'organizer_username' => $e->user ? $e->user->username : null,
                'description' => $e->description,
                'capacity' => $e->capacity,
                'ticket_types' => $e->ticket_types,
                'image_path' => $e->image_path,
                'state' => $e->state,
            ];
        });

        return response()->json(['ok' => true, 'events' => $items]);
    }
}