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

        $items = $q->get(['id','title','venue','starts_at','ends_at','price','slug','use_custom_slug'])
            ->map(function($e){
                return [
                    'id' => $e->id,
                    'title' => $e->title,
                    'venue' => $e->venue,
                    'starts_at' => optional($e->starts_at)->toIso8601String(),
                    'ends_at' => optional($e->ends_at)->toIso8601String(),
                    'price' => (float) $e->price,
                    'url' => $e->public_url,
                ];
            });

        return response()->json(['ok' => true, 'events' => $items]);
    }
}