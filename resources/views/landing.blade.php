@extends('layouts.public')
@section('title', 'Find the Event | ' . config('app.name', '2DAWN'))
@section('meta_description', 'Curated events. Seamless tickets. No accounts, just vibes.')
@section('canonical', route('home'))

@section('content')
    <!-- Hero: full screen, minimal -->
    <section class="relative min-h-[92vh] lg:min-h-[96vh] flex items-center justify-center overflow-hidden">
        <!-- Header inside hero -->
        @include('partials.public-header')
        <div class="absolute inset-0 -z-20">
            <img src="{{ asset('images/party.jpg') }}" alt="" class="h-full w-full object-cover opacity-30 pointer-events-none select-none"/>
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>
        </div>
        <div class="absolute inset-0 -z-10">
            <div class="absolute -top-48 -left-32 h-[40rem] w-[40rem] rounded-full blur-3xl opacity-30 bg-gradient-to-br from-indigo-500 via-fuchsia-500 to-rose-500"></div>
            <div class="absolute -bottom-48 -right-32 h-[40rem] w-[40rem] rounded-full blur-3xl opacity-20 bg-gradient-to-tr from-sky-500 to-emerald-400"></div>
        </div>
        <div class="w-full mx-auto text-center">
            <h1 class="fluid-title font-extrabold tracking-tight text-white drop-shadow-[0_2px_12px_rgba(0,0,0,0.6)]">Find the vibe. Book in seconds.</h1>
            <p class="mt-5 text-zinc-200 fluid-subtitle">Curated nights across the city — no accounts, instant tickets, pure vibes.</p>
            <div class="mt-10 flex items-center justify-center text-zinc-300 text-sm">
                @php $uc = (int) ($stats['upcoming_events_count'] ?? 0); @endphp
                <div><span class="text-white font-semibold text-xl">{{ number_format($uc) }}</span> upcoming {{ $uc === 1 ? 'event' : 'events' }}</div>
            </div>
        </div>
    </section>

    <!-- Mood scroller: what are you looking for? -->
    <section class="relative py-10">
      <div class="max-w-7xl mx-auto px-6">
        <h2 class="text-center text-4xl sm:text-5xl md:text-6xl font-extrabold mb-8 sm:mb-10">What are you looking for?</h2>
        <div class="fade-x overflow-x-auto no-scrollbar">
          <ul class="flex items-center justify-center gap-3 sm:gap-4 min-w-max text-zinc-300 text-xl sm:text-2xl tracking-wider">
            @php $items = collect(config('moods.list', ['Rave','Romantic','Amapiano','Afrobeats','Hip‑Hop','House','Live Band','Jazz','Techno','Gospel','Comedy','Networking'])); @endphp
            @foreach($items as $i => $mood)
              <li class="flex items-center">
                <a href="{{ route('events.index', ['mood' => $mood]) }}" class="px-2 sm:px-3 py-1 uppercase hover:text-white whitespace-nowrap">{{ $mood }}</a>
                @if($i < (($items instanceof \Illuminate\Support\Collection ? $items->count() : count($items)) - 1))
                  <span aria-hidden class="mx-1 sm:mx-2 opacity-40">|</span>
                @endif
              </li>
            @endforeach
          </ul>
        </div>
        <p class="mt-8 sm:mt-10 text-center text-zinc-400 text-base sm:text-lg">We've got you</p>
      </div>
    </section>

    <!-- Upcoming Events: sleek glass cards -->
    <section id="upcoming" class="relative py-16 sm:py-20">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex items-end justify-between mb-8">
                <h2 class="text-3xl sm:text-4xl font-bold">Upcoming Events</h2>
                <a href="{{ route('events.index') }}" class="text-sm text-zinc-300 hover:text-white">View all →</a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse ($featuredEvents as $event)
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
<a href="{{ $event->public_url }}" class="absolute inset-0 z-10">
                            <span class="sr-only">Open {{ $event->title }}</span>
                        </a>
                        <div class="relative aspect-[10/13]">
                            @php
                                $imgSrc = $event->image_url;
                            @endphp
                            @if($imgSrc)
                                <img src="{{ $imgSrc }}" alt="{{ $event->title }}" class="absolute inset-0 h-full w-full object-cover group-hover:scale-105 duration-500 ease-out"/>
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
                                        <span class="slots-badge hidden px-2 py-0.5 rounded-full text-[10px] sm:text-xs bg-white/10 ring-1 ring-white/15"
                                              data-remaining-url="{{ route('events.remaining', $event) }}">—</span>
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
        </div>
    </section>

    <script>
      // Live update remaining slots on upcoming cards
      document.addEventListener('DOMContentLoaded', () => {
        const badges = Array.from(document.querySelectorAll('#upcoming .slots-badge'));
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

    <!-- How to buy tickets -->
    <section id="how-to-buy" class="relative py-16 sm:py-20">
        <div class="max-w-7xl mx-auto px-6">
            <div class="mb-8 w-full flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <h2 class="text-3xl sm:text-4xl font-bold">How to buy tickets</h2>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs sm:text-sm text-zinc-300 sm:bg-white/5 sm:ring-1 sm:ring-white/10">3 easy steps</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <!-- Step 1 -->
                <div class="group rounded-3xl bg-white/5 backdrop-blur-md ring-1 ring-white/10 hover:ring-white/20 transition p-8">
                    <div class="flex items-center gap-4">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6 text-zinc-200"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
                        <div>
                            <h3 class="text-lg font-semibold">Pick an event</h3>
                            <div class="text-[10px] uppercase tracking-widest text-zinc-400">Step 1</div>
                        </div>
                    </div>
                    <p class="mt-4 text-sm text-zinc-300">Browse Upcoming or All Events, open a card to see details, then click <span class="font-semibold text-white">Buy Tickets</span>.</p>
                    <div class="mt-4 text-xs text-zinc-400">No account required.</div>
                </div>
                <!-- Step 2 -->
                <div class="group rounded-3xl bg-white/5 backdrop-blur-md ring-1 ring-white/10 hover:ring-white/20 transition p-8">
                    <div class="flex items-center gap-4">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6 text-zinc-200"><rect x="3" y="6" width="18" height="12" rx="2"/><path d="M3 10h18"/></svg>
                        <div>
                            <h3 class="text-lg font-semibold">Checkout securely</h3>
                            <div class="text-[10px] uppercase tracking-widest text-zinc-400">Step 2</div>
                        </div>
                    </div>
                    <p class="mt-4 text-sm text-zinc-300">Enter your name and email, apply coupons if you have one, and pay with <span class="font-semibold text-white">Paystack</span>.</p>
                    <ul class="mt-4 space-y-1 text-xs text-zinc-400 list-disc list-inside">
                        <li>Instant confirmation</li>
                        <li>Mobile friendly</li>
                    </ul>
                </div>
                <!-- Step 3 -->
                <div class="group rounded-3xl bg-white/5 backdrop-blur-md ring-1 ring-white/10 hover:ring-white/20 transition p-8">
                    <div class="flex items-center gap-4">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6 text-zinc-200"><rect x="5" y="5" width="4" height="4"/><rect x="15" y="5" width="4" height="4"/><rect x="5" y="15" width="4" height="4"/><rect x="15" y="15" width="4" height="4"/></svg>
                        <div>
                            <h3 class="text-lg font-semibold">Get your ticket</h3>
                            <div class="text-[10px] uppercase tracking-widest text-zinc-400">Step 3</div>
                        </div>
                    </div>
                    <p class="mt-4 text-sm text-zinc-300">You’ll receive a QR ticket instantly. Download as <span class="font-semibold text-white">PNG</span> or print, and present it at the gate.</p>
                    <div class="mt-4 text-xs text-zinc-400">You’ll also get a receipt PDF if needed.</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Recent Events: past 30 days -->
    <section id="recent" class="relative py-16 sm:py-20">
        <div class="max-w-7xl mx-auto px-6">
            <div class="mb-8 w-full flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <h2 class="text-3xl sm:text-4xl font-bold">Recent Events</h2>
                <div class="flex items-center justify-between sm:justify-start sm:gap-4 text-xs sm:text-sm text-zinc-300">
                    <a href="{{ route('events.recent') }}" class="hover:text-white">View all recent →</a>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse ($recentEvents as $event)
                    @php
                        $year = optional($event->starts_at)?->format('Y');
                        $minutes = ($event->starts_at && $event->ends_at) ? $event->starts_at->diffInMinutes($event->ends_at) : null;
                        $duration = $minutes ? (int) floor($minutes/60).'h '.($minutes%60).'m' : null;
                    @endphp
                    <div class="group relative rounded-3xl overflow-hidden ring-1 ring-white/10 hover:ring-white/20 transition ticket-notch" data-tilt data-tilt-max="6">
<a href="{{ $event->public_url }}" class="absolute inset-0 z-10">
                            <span class="sr-only">Open {{ $event->title }}</span>
                        </a>
                        <div class="relative aspect-[10/13]">
                            @if($event->image_url)
                                <img src="{{ $event->image_url }}" alt="{{ $event->title }}" class="absolute inset-0 h-full w-full object-cover group-hover:scale-105 duration-500 ease-out"/>
                            @else
                                <div class="absolute inset-0 h-full w-full bg-gradient-to-br from-indigo-500 via-fuchsia-500 to-rose-500"></div>
                            @endif
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                            <div class="absolute inset-x-0 bottom-0 p-4 sm:p-5">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-white font-semibold truncate">{{ $event->title }}</h3>
                                    <span class="px-2 py-0.5 rounded-full text-xs bg-white/10 ring-1 ring-white/15">Ended</span>
                                </div>
                                <div class="mt-1 text-zinc-300 text-xs sm:text-sm truncate">
                                    {{ $year ?? '—' }}@if($event->venue) • {{ $event->venue }}@endif @if($duration) • {{ $duration }}@endif
                                </div>
                                <div class="mt-3 flex items-center justify-between">
                                    <a href="{{ route('events.show', $event) }}" class="inline-flex items-center px-4 py-2 rounded-full bg-white text-black text-xs sm:text-sm font-medium hover:bg-zinc-100 transition">View</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center text-zinc-400 py-20">No recent events yet.</div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- Host with us -->
    <section id="host" class="relative py-16 sm:py-20 overflow-visible">
      <div class="absolute inset-0 pointer-events-none -z-10">
        <div class="absolute -top-32 -left-24 h-80 w-80 rounded-full blur-3xl opacity-20 bg-gradient-to-br from-indigo-500 via-fuchsia-500 to-rose-500"></div>
        <div class="absolute -bottom-32 -right-24 h-80 w-80 rounded-full blur-3xl opacity-10 bg-gradient-to-tr from-sky-500 to-emerald-400"></div>
      </div>
      <div class="max-w-7xl mx-auto px-6">
        <div class="mb-6 sm:mb-8">
          <h2 class="text-3xl sm:text-4xl font-bold">Host with us</h2>
        </div>
        <div class="relative rounded-[28px] ring-1 ring-white/10 p-4 sm:p-6 lg:p-10 overflow-hidden">
          <div class="absolute inset-0 -z-10">
            <div class="absolute inset-0" style="background-image:radial-gradient(80rem_40rem_at_-10%_-10%,rgba(59,130,246,0.15),transparent),radial-gradient(70rem_35rem_at_110%_110%,rgba(236,72,153,0.12),transparent),radial-gradient(60rem_30rem_at_50%_120%,rgba(16,185,129,0.10),transparent);"></div>
            <div class="absolute inset-0" style="background-image:url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22%3E%3Cfilter id=%22n%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.8%22 numOctaves=%224%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23n)%22/%3E%3C/svg%3E');opacity:.06;mix-blend:overlay;"></div>
          </div>
            <div class="relative grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-stretch">
              <!-- Left: offer list + headline -->
              <div class="flex flex-col justify-between h-full min-h-[460px]">
                <h3 class="mt-4 text-4xl sm:text-5xl font-extrabold">Get in touch with us!</h3>
                <p class="mt-1 sm:mt-1 md:mt-2 text-zinc-300">We’ll handle ticketing, payments, and check‑ins so you can focus on the vibe.</p>
                <ul class="mt-2 sm:mt-3 md:mt-4 space-y-1 sm:space-y-2 text-sm text-zinc-300">
                  <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>Instant payouts with Paystack</li>
                  <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>QR tickets & capacity protection</li>
                  <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>Analytics, coupons, exports</li>
                </ul>
                <div class="mt-auto pt-8 hidden lg:flex justify-start self-start">
                  <button type="submit" form="host-form-el" class="inline-flex items-center justify-center rounded-full text-white border border-white/70 hover:bg-white/10 text-base font-medium focus:outline-none focus:ring-0" style="padding:7px 15px; min-width:180px; text-align:center">Submit</button>
                </div>
              </div>

              <!-- Right: dark inner card with minimal form -->
              <div>
                <div id="host-form" class="rounded-2xl bg-black ring-1 ring-white/10 p-6 sm:p-8 lg:p-10">
                  <h4 class="text-xl font-semibold mb-6">Contact Us</h4>
                  <form id="host-form-el" method="POST" action="{{ route('host.request.store') }}" class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    @csrf
                    <div>
                      <label class="block text-xs uppercase tracking-widest text-zinc-400" for="name">Full name</label>
                      <input id="name" name="name" type="text" required value="{{ old('name') }}" class="mt-2 block w-full bg-transparent border-0 border-b border-white/10 px-0 py-3 focus:border-white/30 focus:ring-0" />
                    </div>
                    <div>
                      <label class="block text-xs uppercase tracking-widest text-zinc-400" for="email">Email</label>
                      <input id="email" name="email" type="email" required value="{{ old('email') }}" class="mt-2 block w-full bg-transparent border-0 border-b border-white/10 px-0 py-3 focus:border-white/30 focus:ring-0" />
                    </div>
                    <div class="sm:col-span-2">
                      <label class="block text-xs uppercase tracking-widest text-zinc-400" for="event_title">Event idea / title</label>
                      <input id="event_title" name="event_title" type="text" required value="{{ old('event_title') }}" class="mt-2 block w-full bg-transparent border-0 border-b border-white/10 px-0 py-3 focus:border-white/30 focus:ring-0" />
                    </div>
                    <div class="sm:col-span-2">
                      <label class="block text-xs uppercase tracking-widest text-zinc-400" for="message">Message (optional)</label>
                      <textarea id="message" name="message" rows="4" class="mt-2 block w-full bg-transparent border-0 border-b border-white/10 px-0 py-3 focus:border-white/30 focus:ring-0">{{ old('message') }}</textarea>
                    </div>
                    <!-- Mobile submit button under the form -->
                    <div class="sm:col-span-2 lg:hidden">
                      <button type="submit" class="mt-2 inline-flex items-center justify-center w-full rounded-full text-white border border-white/70 hover:bg-white/10 text-base font-medium focus:outline-none focus:ring-0" style="padding:10px 18px; min-width:180px; text-align:center">Submit</button>
                    </div>
                  </form>

                  @if (session('status'))
                    <div class="mt-6 rounded-lg bg-emerald-500/10 ring-1 ring-emerald-500/20 text-emerald-300 px-3 py-2">{{ session('status') }}</div>
                  @endif
                  @if ($errors->any())
                    <div class="mt-4 rounded-lg bg-red-500/10 ring-1 ring-red-500/20 text-red-300 px-3 py-2">
                      <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                          <li>{{ $error }}</li>
                        @endforeach
                      </ul>
                    </div>
                  @endif
                </div>
              </div>
            </div>
        </div>
      </div>
    </section>

    <!-- Flatpickr (modern date/time picker) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
      document.addEventListener('DOMContentLoaded', () => {
        const el = document.getElementById('event_date');
        if (el && window.flatpickr) {
          flatpickr(el, {
            enableTime: true,
            dateFormat: 'Y-m-d H:i',
            altInput: true,
            altFormat: 'D, M j, Y h:i K',
            minDate: 'today'
          });
        }
      });
    </script>

    <!-- CTA: one simple bar -->
    <section class="py-20">
        <div class="max-w-6xl mx-auto px-6">
            <div class="rounded-[2rem] bg-white/5 ring-1 ring-white/10 p-10 text-center">
                <h3 class="text-3xl sm:text-4xl font-bold">Ready for your next event ?</h3>
                <p class="mt-2 text-zinc-300">Browse all events happening soon.</p>
                <div class="mt-6">
                    <a href="{{ route('events.index') }}" class="inline-flex items-center px-8 py-3 rounded-full bg-white text-black font-semibold hover:bg-zinc-100 transition">Explore all events</a>
                </div>
            </div>
        </div>
    </section>


    @endsection
