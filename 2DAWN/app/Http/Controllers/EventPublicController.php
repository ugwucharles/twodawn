<?php

namespace App\Http\Controllers;

use App\Models\Event;

use Illuminate\Http\Request;

class EventPublicController extends Controller
{
    public function landing()
    {
        $featuredEvents = Event::query()->where('is_published', true)
            ->where(function($q){
                $q->where('ends_at', '>=', now())
                  ->orWhere(function($q2){
                      $q2->whereNull('ends_at')->where('starts_at', '>=', now());
                  });
            })
            ->orderBy('starts_at')
            ->take(6)
            ->get();

        // Recent past events (ended in the last 30 days)
        $recentEvents = Event::query()->where('is_published', true)
            ->where(function($q){
                $q->where(function($q2){
                    $q2->whereNotNull('ends_at')
                       ->whereBetween('ends_at', [now()->subDays(30), now()]);
                })->orWhere(function($q3){
                    $q3->whereNull('ends_at')
                       ->whereBetween('starts_at', [now()->subDays(30), now()]);
                });
            })
            ->orderByDesc('starts_at')
            ->take(6)
            ->get();

        // Other upcoming events (not in featured)
        $otherEvents = Event::query()->where('is_published', true)
            ->where(function($q){
                $q->where('ends_at', '>=', now())
                  ->orWhere(function($q2){
                      $q2->whereNull('ends_at')->where('starts_at', '>=', now());
                  });
            })
            ->whereNotIn('id', $featuredEvents->pluck('id'))
            ->orderBy('starts_at')
            ->take(6)
            ->get();

        $stats = [
            'events_count' => Event::where('is_published', true)->count(),
            'upcoming_events_count' => Event::where('is_published', true)
                ->where(function($q){
                    $q->where('ends_at', '>=', now())
                      ->orWhere(function($q2){
                          $q2->whereNull('ends_at')->where('starts_at', '>=', now());
                      });
                })->count(),
            'tickets_sold' => \App\Models\Order::where('status','paid')->sum('quantity'),
        ];

        return view('landing', compact('featuredEvents','recentEvents','otherEvents','stats'));
    }

    public function index(Request $request)
    {
        $allowed = config('moods.list', []);
        $mood = $request->query('mood');
        $term = trim((string) $request->query('q', ''));

        $base = Event::query()->where('is_published', true)
            ->where(function($q){ $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()); });

        $events = (clone $base)
            ->when($mood && (empty($allowed) || in_array($mood, $allowed, true)), fn ($q) => $q->where('mood', $mood))
            ->when($term !== '', function ($q) use ($term) {
                $q->where(function ($qq) use ($term) {
                    $like = '%'.str_replace(['%','_'], ['\\%','\\_'], $term).'%';
                    $qq->where('title', 'like', $like)
                       ->orWhere('venue', 'like', $like)
                       ->orWhere('description', 'like', $like)
                       ->orWhere('mood', 'like', $like);
                });
            })
            ->orderBy('starts_at')
            ->paginate(12)
            ->appends($request->only(['q','mood','state','price','date','state_label']));

        $showCurated = ($term === '' && !$mood && $events->currentPage() === 1);
        $trendingEvents = collect(); $weekendEvents = collect(); $freeEvents = collect(); $newWeekEvents = collect();
        if ($showCurated) {
            try {
                $since = now()->subDays(30);
                $topIds = \App\Models\Order::select('event_id', \DB::raw('SUM(quantity) as qty'))
                    ->where('status','paid')->where('created_at','>=',$since)
                    ->groupBy('event_id')->orderByDesc('qty')->limit(12)->pluck('event_id')->toArray();
                if (!empty($topIds)) {
                    $trendingEvents = (clone $base)->whereIn('id', $topIds)->take(6)->get();
                } else {
                    $trendingEvents = (clone $base)->orderBy('starts_at')->take(6)->get();
                }
            } catch (\Throwable $e) {
                $trendingEvents = (clone $base)->orderBy('starts_at')->take(6)->get();
            }
            // This weekend: Fri 00:00 to Sun 23:59 of the upcoming/current week
            $now = now();
            $fri = (clone $now)->startOfWeek(\Carbon\Carbon::MONDAY)->addDays(4); // Friday
            if ($now->gt($fri)) { $fri = (clone $fri)->addWeek(); }
            $sunEnd = (clone $fri)->addDays(2)->endOfDay();
            $weekendEvents = (clone $base)->whereBetween('starts_at', [$fri, $sunEnd])->take(6)->get();
            $freeEvents = (clone $base)->where(function($q){ $q->whereNull('price')->orWhere('price', '<=', 0); })->orderBy('starts_at')->take(6)->get();
            // New this week (created in last 7 days; fallback to starts in next 7 days)
            $newWeekEvents = (clone $base)->where('created_at','>=', now()->subDays(7))->orderBy('starts_at')->take(6)->get();
            if ($newWeekEvents->isEmpty()) {
                $newWeekEvents = (clone $base)->whereBetween('starts_at', [now(), now()->addDays(7)])->orderBy('starts_at')->take(6)->get();
            }
        }

        $moods = collect(config('moods.list', ['Rave','Romantic','Amapiano','Afrobeats','Hip‑Hop','House','Live Band','Jazz','Techno','Gospel','Comedy','Networking']));

        $moodSections = [];
        if ($showCurated) {
            foreach ($moods as $mm) {
                $list = (clone $base)->where('mood', $mm)->orderBy('starts_at')->take(3)->get();
                if ($list->count()) { $moodSections[$mm] = $list; }
                if (count($moodSections) >= 6) break; // keep it light
            }
        }

        return view('events.index', compact('events','showCurated','trendingEvents','weekendEvents','freeEvents','newWeekEvents','moods','mood','moodSections'));
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
            $q->latest();
        }]);
        return view('events.show', compact('event'));
    }

    public function showBySlug(string $slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();
        abort_unless($event->is_published, 404);
        $event->load(['comments' => function($q){ $q->latest(); }]);
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

    public function ics(Event $event)
    {
        abort_unless($event->is_published, 404);
        $title = addcslashes($event->title ?? 'Event', ",;\\");
        $desc = addcslashes(strip_tags((string) $event->description), ",;\\");
        $loc = addcslashes((string) $event->venue, ",;\\");
        $start = optional($event->starts_at)->copy()->utc();
        $end = optional($event->ends_at)->copy()->utc() ?: optional($event->starts_at)->copy()->addHours(2)->utc();
        $dtStart = $start ? $start->format('Ymd\THis\Z') : now()->utc()->format('Ymd\THis\Z');
        $dtEnd = $end ? $end->format('Ymd\THis\Z') : now()->addHours(2)->utc()->format('Ymd\THis\Z');
        $uid = 'event-'.$event->id.'@'.parse_url(config('app.url', url('/')), PHP_URL_HOST);
        $url = $event->public_url;
        // Optional alarm (minutes before start), default 60
        $alarmMin = (int) request()->query('alarm', 60);
        if (!in_array($alarmMin, [5,10,15,30,60,120,1440])) { $alarmMin = 60; }
        $alarmLines = [];
        if ($alarmMin > 0) {
            $alarmLines = [
                'BEGIN:VALARM',
                'ACTION:DISPLAY',
                'DESCRIPTION:Reminder',
                'TRIGGER:-PT' . $alarmMin . 'M',
                'END:VALARM',
            ];
        }
        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//2DAWN//Event//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:'.$uid,
            'DTSTAMP:'.now()->utc()->format('Ymd\THis\Z'),
            'DTSTART:'.$dtStart,
            'DTEND:'.$dtEnd,
            'SUMMARY:'.$title,
            $desc ? ('DESCRIPTION:'.$desc) : null,
            $loc ? ('LOCATION:'.$loc) : null,
            'URL:'.$url,
            ...$alarmLines,
            'END:VEVENT',
            'END:VCALENDAR',
        ];
        $ics = implode("\r\n", array_filter($lines, fn($l) => !is_null($l))) . "\r\n";
        return response($ics, 200, [
            'Content-Type' => 'text/calendar; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="event_'.$event->id.'.ics"',
        ]);
    }
}
