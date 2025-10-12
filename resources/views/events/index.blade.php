@extends('layouts.public')

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
    <div class="mb-8 flex items-end justify-between">
      <div>
        <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight">All Events</h1>
        <p class="mt-2 text-zinc-400">Browse upcoming shows and parties</p>
      </div>
      <div>
        <a href="{{ url('/') }}" class="inline-flex items-center px-4 py-2 rounded-full bg-white text-black text-sm font-semibold hover:bg-zinc-100 transition">Go to Home</a>
      </div>
    </div>

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
        <div class="group relative rounded-3xl overflow-hidden ring-1 ring-white/10 hover:ring-white/20 transition ticket-notch" data-tilt data-tilt-max="6">
          <a href="{{ route('events.show', $event) }}" class="absolute inset-0 z-10">
            <span class="sr-only">Open {{ $event->title }}</span>
          </a>
<div class="relative" style="padding-top: calc(62.5% + 120px);">
            @if($event->image_path)
              <img src="{{ Storage::url($event->image_path) }}" alt="{{ $event->title }}" class="absolute inset-0 h-full w-full object-cover group-hover:scale-105 duration-500 ease-out"/>
            @else
              <div class="absolute inset-0 h-full w-full bg-gradient-to-br from-indigo-500 via-fuchsia-500 to-rose-500"></div>
            @endif
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
            <div class="absolute inset-x-0 bottom-0 p-4 sm:p-5">
              <h3 class="text-white font-semibold truncate">{{ $event->title }}</h3>
              <div class="mt-1 text-zinc-300 text-xs sm:text-sm truncate">
                {{ $year ?? '—' }}@if($event->venue) • {{ $event->venue }}@endif @if($duration) • {{ $duration }}@endif
              </div>
              <div class="mt-3 flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <a id="buy-{{ $event->id }}" data-buy class="inline-flex items-center px-4 py-2 rounded-full bg-pink-500 text-white text-xs sm:text-sm font-medium hover:bg-pink-400 transition" href="{{ route('events.buy', $event) }}">Buy Tickets</a>
                  <span class="slots-badge hidden px-2 py-0.5 rounded-full text-[10px] sm:text-xs bg-white/10 ring-1 ring-white/15" data-remaining-url="{{ route('events.remaining', $event) }}">—</span>
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

    <!-- Recent events (past 30 days) -->
    <section id="recent" class="mt-16">
      <div class="mb-6 w-full flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <h2 class="text-2xl sm:text-3xl font-bold">Recent Events</h2>
        <div class="flex items-center justify-between sm:justify-start sm:gap-4 text-xs sm:text-sm text-zinc-300">
          <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-white/5 ring-1 ring-white/10">Past 30 days</span>
          <a href="{{ route('events.recent') }}" class="hover:text-white">View all recent →</a>
        </div>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse ($recentEvents as $recent)
          @php
            $year = optional($recent->starts_at)?->format('Y');
            $minutes = ($recent->starts_at && $recent->ends_at) ? $recent->starts_at->diffInMinutes($recent->ends_at) : null;
            $duration = $minutes ? (int) floor($minutes/60).'h '.($minutes%60).'m' : null;
          @endphp
          <div class="group relative rounded-3xl overflow-hidden ring-1 ring-white/10 hover:ring-white/20 transition ticket-notch" data-tilt data-tilt-max="6">
            <a href="{{ route('events.show', $recent) }}" class="absolute inset-0 z-10">
              <span class="sr-only">Open {{ $recent->title }}</span>
            </a>
          <div class="card-aspect">
              @if($recent->image_path)
                <img src="{{ Storage::url($recent->image_path) }}" alt="{{ $recent->title }}" class="absolute inset-0 h-full w-full object-cover group-hover:scale-105 duration-500 ease-out"/>
              @else
                <div class="absolute inset-0 h-full w-full bg-gradient-to-br from-indigo-500 via-fuchsia-500 to-rose-500"></div>
              @endif
              <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
              <div class="absolute inset-x-0 bottom-0 p-4 sm:p-5">
                <div class="flex items-center justify-between">
                  <h3 class="text-white font-semibold truncate">{{ $recent->title }}</h3>
                  <span class="px-2 py-0.5 rounded-full text-xs bg-white/10 ring-1 ring-white/15">Ended</span>
                </div>
                <div class="mt-1 text-zinc-300 text-xs sm:text-sm truncate">
                  {{ $year ?? '—' }}@if($recent->venue) • {{ $recent->venue }}@endif @if($duration) • {{ $duration }}@endif
                </div>
                <div class="mt-3 flex items-center justify-between">
                  <a href="{{ route('events.show', $recent) }}" class="inline-flex items-center px-4 py-2 rounded-full bg-white text-black text-xs sm:text-sm font-medium hover:bg-zinc-100 transition">View</a>
                </div>
              </div>
            </div>
          </div>
        @empty
          <div class="col-span-full text-center text-zinc-400 py-10">No recent events.</div>
        @endforelse
      </div>
    </section>
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
