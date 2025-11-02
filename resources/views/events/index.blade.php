@extends('layouts.public')
@php
  $mood = request('mood');
  $hasQuery = filled(request('q'));
  $page = (int) request()->input('page', 1);
  $canonParams = [];
  if ($mood) { $canonParams['mood'] = $mood; }
  if ($page > 1) { $canonParams['page'] = $page; }
  $canonUrl = $canonParams ? route('events.index', $canonParams) : route('events.index');
@endphp
@section('title', 'All Events | ' . config('app.name', '2DAWN'))
@section('meta_description', 'Browse upcoming events and buy tickets easily.')
@section('canonical', $canonUrl)
@if($hasQuery)
  @section('robots', 'noindex, follow')
@endif
@section('head_links')
  @if($events->previousPageUrl())
    <link rel="prev" href="{{ $events->previousPageUrl() }}">
  @endif
  @if($events->nextPageUrl())
    <link rel="next" href="{{ $events->nextPageUrl() }}">
  @endif
@endsection

@section('jsonld')
@php
  $items = [];
  foreach ($events as $i => $e) {
    $items[] = [
      '@type' => 'ListItem',
      'position' => $i + 1 + (($events->currentPage() - 1) * $events->perPage()),
'url' => $e->public_url,
      'name' => $e->title,
    ];
  }
  $itemList = [
    '@context' => 'https://schema.org',
    '@type' => 'ItemList',
    'itemListElement' => $items,
  ];
  $breadcrumbs = [
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
      [ '@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home') ],
      [ '@type' => 'ListItem', 'position' => 2, 'name' => 'Events', 'item' => route('events.index') ],
    ],
  ];
@endphp
<script type="application/ld+json">{!! json_encode($itemList, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($breadcrumbs, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
@endsection

@section('content')
<section class="relative py-12 sm:py-16">
  <style>
    /* Responsive card aspect with extra height bump */
    .card-aspect{position:relative;padding-top:calc(62.5% + 60px);} /* base: 16:10 + 60px */
    @media (min-width:640px){.card-aspect{padding-top:calc(62.5% + 80px);} }
    @media (min-width:1024px){.card-aspect{padding-top:calc(62.5% + 120px);} }
    .card-aspect > *{position:absolute;inset:0;}
  </style>
  <style>
    @keyframes gradientShift {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }
    .animated-gradient {
      position: absolute; inset: -20%; z-index: -10; pointer-events: none;
      background: 
        radial-gradient(circle at 20% 20%, rgba(99,102,241,0.22), transparent 40%),
        radial-gradient(circle at 80% 30%, rgba(217,70,239,0.18), transparent 45%),
        radial-gradient(circle at 30% 80%, rgba(14,165,233,0.18), transparent 40%),
        linear-gradient(120deg, rgba(99,102,241,0.18), rgba(34,197,94,0.14), rgba(244,63,94,0.18));
      background-size: 200% 200%;
      animation: gradientShift 28s ease-in-out infinite;
      filter: blur(48px);
      transform: translateZ(0);
    }
  </style>
  <div class="animated-gradient"></div>
  <div class="max-w-7xl mx-auto px-6">
    <div class="mb-6 flex items-end justify-between gap-4 flex-wrap">
      <div>
        <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight">Discover events</h1>
        <p class="mt-2 text-zinc-400">Find trending, free, and this weekend’s vibes.</p>
      </div>
      <form method="GET" action="{{ route('events.index') }}" class="w-full sm:w-auto flex items-center gap-2">
        @if(request('mood'))
          <input type="hidden" name="mood" value="{{ request('mood') }}" />
        @endif
        <input name="q" value="{{ request('q') }}" placeholder="Search events..." class="flex-1 sm:w-64 rounded-full bg-white/5 ring-1 ring-white/10 px-4 py-2 text-sm focus:outline-none focus:ring-white/30" />
        <button class="inline-flex items-center px-4 py-2 rounded-full bg-white text-black text-sm font-semibold hover:bg-zinc-100">Search</button>
      </form>
    </div>

    @if(!empty($showCurated))
      <div class="mb-6">
        <h2 class="text-center text-4xl sm:text-5xl md:text-6xl font-extrabold mb-8 sm:mb-10">What are you looking for?</h2>
        <div class="fade-x overflow-x-auto no-scrollbar">
          <ul class="flex items-center justify-center gap-3 sm:gap-4 min-w-max text-zinc-300 text-xl sm:text-2xl tracking-wider">
            @php $items = collect(config('moods.list', ['Rave','Romantic','Amapiano','Afrobeats','Hip‑Hop','House','Live Band','Jazz','Techno','Gospel','Comedy','Networking'])); @endphp
            @foreach($items as $i => $m)
              <li class="flex items-center">
                <a href="{{ route('events.index', ['mood' => $m]) }}" class="px-2 sm:px-3 py-1 uppercase hover:text-white whitespace-nowrap">{{ $m }}</a>
                @if($i < (($items instanceof \Illuminate\Support\Collection ? $items->count() : count($items)) - 1))
                  <span aria-hidden class="mx-1 sm:mx-2 opacity-40">|</span>
                @endif
              </li>
            @endforeach
          </ul>
        </div>
        <p class="mt-8 sm:mt-10 text-center text-zinc-400 text-base sm:text-lg">We've got you</p>
      </div>

      @if(($trendingEvents ?? collect())->count())
        <div class="mb-10">
          <div class="flex items-center justify-between mb-3">
            <h2 class="text-2xl font-bold">Trending</h2>
            <a href="{{ route('events.index') }}" class="text-sm text-zinc-300 hover:text-white">View all →</a>
          </div>
          <div class="overflow-x-auto no-scrollbar">
            <div class="min-w-max flex gap-4">
              @foreach(($trendingEvents ?? []) as $event)
                <a href="{{ $event->public_url }}" class="w-64 shrink-0 group relative rounded-2xl overflow-hidden ring-1 ring-white/10 hover:ring-white/20">
                  <div class="relative" style="padding-top: 56%">
                    @if($event->image_url)
                      <img src="{{ $event->image_url }}" alt="{{ $event->title }}" class="absolute inset-0 h-full w-full object-cover group-hover:scale-105 duration-500" />
                    @else
                      <div class="absolute inset-0 h-full w-full bg-gradient-to-br from-indigo-500 via-fuchsia-500 to-rose-500"></div>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                    <div class="absolute inset-x-0 bottom-0 p-3">
                      <div class="text-white font-semibold truncate">{{ $event->title }}</div>
                      <div class="text-xs text-zinc-300 truncate">{{ optional($event->starts_at)->format('M j, g:i A') }} @if($event->venue) • {{ $event->venue }} @endif</div>
                    </div>
                  </div>
                </a>
              @endforeach
            </div>
          </div>
        </div>
      @endif

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        @if(($weekendEvents ?? collect())->count())
          <div class="rounded-2xl bg-white/5 ring-1 ring-white/10 p-4">
            <div class="flex items-center justify-between mb-3"><h3 class="text-xl font-bold">This weekend</h3><a href="{{ route('events.index') }}" class="text-sm text-zinc-300 hover:text-white">All events</a></div>
            <ul class="space-y-3">
              @foreach(($weekendEvents ?? []) as $e)
                <li class="flex items-center gap-3">
                  <div class="h-10 w-14 rounded overflow-hidden bg-white/10 ring-1 ring-white/10">
                    @if($e->image_url) <img src="{{ $e->image_url }}" class="h-full w-full object-cover"/> @endif
                  </div>
                  <a href="{{ $e->public_url }}" class="flex-1 hover:underline">{{ $e->title }}</a>
                  <div class="text-xs text-zinc-400">{{ optional($e->starts_at)->format('D, g:i A') }}</div>
                </li>
              @endforeach
            </ul>
          </div>
        @endif
        @if(($newWeekEvents ?? collect())->count())
          <div class="rounded-2xl bg-white/5 ring-1 ring-white/10 p-4">
            <div class="flex items-center justify-between mb-3"><h3 class="text-xl font-bold">New this week</h3><a href="{{ route('events.index') }}" class="text-sm text-zinc-300 hover:text-white">All events</a></div>
            <ul class="space-y-3">
              @foreach(($newWeekEvents ?? []) as $e)
                <li class="flex items-center gap-3">
                  <div class="h-10 w-14 rounded overflow-hidden bg-white/10 ring-1 ring-white/10">
                    @if($e->image_url) <img src="{{ $e->image_url }}" class="h-full w-full object-cover"/> @endif
                  </div>
                  <a href="{{ $e->public_url }}" class="flex-1 hover:underline">{{ $e->title }}</a>
                  <div class="text-xs text-zinc-400">{{ optional($e->starts_at)->format('M j') }}</div>
                </li>
              @endforeach
            </ul>
          </div>
        @endif
        @if(($freeEvents ?? collect())->count())
          <div class="rounded-2xl bg-white/5 ring-1 ring-white/10 p-4">
            <div class="flex items-center justify-between mb-3"><h3 class="text-xl font-bold">Free & budget‑friendly</h3><a href="{{ route('events.index') }}" class="text-sm text-zinc-300 hover:text-white">All events</a></div>
            <ul class="space-y-3">
              @foreach(($freeEvents ?? []) as $e)
                <li class="flex items-center gap-3">
                  <div class="h-10 w-14 rounded overflow-hidden bg-white/10 ring-1 ring-white/10">
                    @if($e->image_url) <img src="{{ $e->image_url }}" class="h-full w-full object-cover"/> @endif
                  </div>
                  <a href="{{ $e->public_url }}" class="flex-1 hover:underline">{{ $e->title }}</a>
                  <span class="text-xs text-emerald-300">Free</span>
                </li>
              @endforeach
            </ul>
          </div>
        @endif
      </div>
    @endif

    @if(!empty($showCurated) && !empty($moodSections))
      <div class="mb-10">
        <div class="flex items-center justify-between mb-3">
          <h2 class="text-2xl font-bold">By mood</h2>
          <a href="{{ route('events.index') }}" class="text-sm text-zinc-300 hover:text-white">All events →</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          @foreach($moodSections as $mm => $list)
            <div class="rounded-2xl bg-white/5 ring-1 ring-white/10 p-4">
              <div class="flex items-center justify-between mb-2">
                <h3 class="text-lg font-semibold">{{ $mm }}</h3>
                <a href="{{ route('events.index', ['mood' => $mm]) }}" class="text-xs text-zinc-300 hover:text-white">View all</a>
              </div>
              <ul class="space-y-3">
                @foreach($list as $e)
                  <li class="flex items-center gap-3">
                    <div class="h-10 w-14 rounded overflow-hidden bg-white/10 ring-1 ring-white/10">
                      @if($e->image_url) <img src="{{ $e->image_url }}" class="h-full w-full object-cover" loading="lazy"/> @endif
                    </div>
                    <a href="{{ $e->public_url }}" class="flex-1 hover:underline">{{ $e->title }}</a>
                    <div class="text-xs text-zinc-400">{{ optional($e->starts_at)->format('M j') }}</div>
                  </li>
                @endforeach
              </ul>
            </div>
          @endforeach
        </div>
      </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
      @forelse ($events as $event)
        @php
          $priceToShow = $event->price;
          if (!is_null($event->early_bird_price) && !is_null($event->early_bird_ends_at) && now()->lte($event->early_bird_ends_at)) {
            $priceToShow = $event->early_bird_price;
          }
          $year = optional($event->starts_at)?->format('Y');
          $minutes = ($event->starts_at && $event->ends_at) ? $event->starts_at->diffInMinutes($event->ends_at) : null;
          $duration = $minutes ? (int) floor($minutes/60).'h '.($minutes%60).'m' : null;
        @endphp
        <div class="group relative rounded-3xl overflow-hidden ring-1 ring-white/10 hover:ring-white/20 transition ticket-notch">
<a href="{{ $event->public_url }}" class="absolute inset-0 z-10">
            <span class="sr-only">Open {{ $event->title }}</span>
          </a>
<div class="relative" style="padding-top: calc(62.5% + 120px);">
            @php $gal = $event->gallery_urls ?? []; @endphp
            @if(!empty($gal) && count($gal) >= 2)
              <style>
                @keyframes fadeSwapA { 0%{opacity:1} 49%{opacity:1} 50%{opacity:0} 100%{opacity:0} }
                @keyframes fadeSwapB { 0%{opacity:0} 49%{opacity:0} 50%{opacity:1} 100%{opacity:1} }
              </style>
              <img src="{{ $gal[0] }}" alt="{{ $event->title }}" class="absolute inset-0 h-full w-full object-cover" style="animation: fadeSwapA 14s ease-in-out infinite;"/>
              <img src="{{ $gal[1] }}" alt="{{ $event->title }}" class="absolute inset-0 h-full w-full object-cover" style="animation: fadeSwapB 14s ease-in-out infinite;"/>
            @elseif($event->image_url)
              <img src="{{ $event->image_url }}" alt="{{ $event->title }}" class="absolute inset-0 h-full w-full object-cover"/>
            @else
              <div class="absolute inset-0 h-full w-full bg-gradient-to-br from-indigo-500 via-fuchsia-500 to-rose-500"></div>
            @endif
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
            <div class="absolute inset-x-0 bottom-0 p-4 sm:p-5">
              <h3 class="text-white font-semibold truncate">{{ $event->title }}</h3>
              <div class="mt-1 text-zinc-300 text-xs sm:text-sm truncate">
                {{ $year ?? '—' }}@if($event->venue) • {{ $event->venue }}@endif @if($duration) • {{ $duration }}@endif
              </div>
              <div class="mt-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2">
                  <span class="slots-badge hidden px-2 py-0.5 rounded-full text-[10px] sm:text-xs bg-white/10 ring-1 ring-white/15 whitespace-nowrap" data-remaining-url="{{ route('events.remaining', $event) }}">—</span>
                  <a id="buy-{{ $event->id }}" data-buy class="inline-flex items-center px-4 py-2 rounded-full bg-pink-500 text-white text-xs sm:text-sm font-medium hover:bg-pink-400 transition" href="{{ route('events.buy', $event) }}">Buy Tickets</a>
                </div>
                <div class="flex items-center gap-1 text-sm text-white">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 text-amber-400">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.802 2.036a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118L10.5 13.347a1 1 0 00-1.175 0l-2.885 2.136c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.806 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                  </svg>
                  @if(!is_null($priceToShow))
                    <span>₦{{ number_format($priceToShow, 0) }}</span>
                  @else
                    <span>Free</span>
                  @endif
                </div>
              </div>
            </div>
          </div>
        </div>
      @empty
        <div class="col-span-full text-center text-zinc-400 py-20">No events available yet.</div>
      @endforelse
    </div>

    <div class="mt-10">{{ $events->links() }}</div>
  </div>

<script>
  // Live update remaining slots on upcoming cards
  document.addEventListener('DOMContentLoaded', () => {
    const badges = Array.from(document.querySelectorAll('section [data-remaining-url]'));
    if (!badges.length) return;
    const updateOne = async (badge) => {
      const url = badge.getAttribute('data-remaining-url');
      if (!url) return;
      try {
        const res = await fetch(url, { headers: { 'Accept':'application/json' } });
        if (!res.ok) return;
        const data = await res.json();
        const buy = badge.parentElement?.querySelector('[data-buy]');
        if (data.status === 'past') {
          badge.textContent = 'Sales closed';
          badge.classList.remove('hidden');
          if (buy) { buy.setAttribute('aria-disabled','true'); buy.className = buy.className.replace('bg-pink-500','bg-zinc-700/50').replace('text-white','text-zinc-300'); buy.removeAttribute('href'); buy.style.pointerEvents = 'none'; buy.textContent = 'Sales closed'; }
          return;
        }
        if (data.remaining === null) {
          badge.textContent = 'Unlimited';
        } else if (parseInt(data.remaining,10) <= 0) {
          badge.textContent = 'Sold out';
          if (buy) { buy.setAttribute('aria-disabled','true'); buy.className = buy.className.replace('bg-pink-500','bg-zinc-700/50').replace('text-white','text-zinc-300'); buy.removeAttribute('href'); buy.style.pointerEvents = 'none'; buy.textContent = 'Sold out'; }
        } else {
          badge.textContent = data.remaining + ' left';
        }
        badge.classList.remove('hidden');
      } catch(e) { /* ignore */ }
    };
    const tick = () => badges.forEach(b => updateOne(b));
    tick();
    setInterval(tick, 15000);
  });
</script>
</section>
@endsection
