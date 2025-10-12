@extends('layouts.public')

@section('content')
    <!-- Hero: full screen, minimal -->
    <section class="relative min-h-[92vh] flex items-center justify-center overflow-hidden">
        <!-- Header inside hero -->
        </div>
        <div class="absolute inset-0 -z-10">
            <div class="absolute -top-48 -left-32 h-[40rem] w-[40rem] rounded-full blur-3xl opacity-30 bg-gradient-to-br from-indigo-500 via-fuchsia-500 to-rose-500"></div>
            <div class="absolute -bottom-48 -right-32 h-[40rem] w-[40rem] rounded-full blur-3xl opacity-20 bg-gradient-to-tr from-sky-500 to-emerald-400"></div>
        </div>
        <div class="max-w-5xl mx-auto px-6 text-center">
            <h1 class="text-5xl sm:text-6xl md:text-7xl font-extrabold tracking-tight">
                Find the Night.
            </h1>
            <p class="mt-5 text-zinc-300 text-lg sm:text-xl">
                Curated events. Seamless tickets. No accounts, just vibes.
            </p>
            <div class="mt-8 flex items-center justify-center gap-3">
                <a href="{{ route('events.index') }}" class="inline-flex items-center px-7 py-3 rounded-full bg-white text-black font-semibold hover:bg-zinc-100 transition">Browse Events</a>
                <a href="#upcoming" class="inline-flex items-center px-7 py-3 rounded-full bg-white/10 ring-1 ring-white/15 hover:bg-white/15 transition">Upcoming ▼</a>
            </div>
            <div class="mt-10 flex items-center justify-center gap-8 text-zinc-400 text-sm">
                <div><span class="text-white font-semibold text-xl">{{ number_format($stats['events_count']) }}</span> events</div>
                <div><span class="text-white font-semibold text-xl">{{ number_format($stats['tickets_sold']) }}</span> tickets sold</div>
            </div>
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
                        <a href="{{ route('events.show', $event) }}" class="absolute inset-0 z-10">
                            <span class="sr-only">Open {{ $event->title }}</span>
                        </a>
                        <div class="relative aspect-[10/13]">
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
                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-white/5 ring-1 ring-white/10 text-xs sm:text-sm text-zinc-300">3 easy steps</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <!-- Step 1 -->
                <div class="rounded-3xl bg-white/5 ring-1 ring-white/10 p-6">
                    <div class="flex items-center gap-3">
                        <div class="h-9 w-9 rounded-full bg-gradient-to-br from-indigo-500 to-fuchsia-500 text-white flex items-center justify-center font-extrabold">1</div>
                        <h3 class="text-lg font-semibold">Pick an event</h3>
                    </div>
                    <p class="mt-3 text-sm text-zinc-300">Browse Upcoming or All Events, open a card to see details, then click <span class="font-semibold text-white">Buy Tickets</span>.</p>
                    <div class="mt-4 text-zinc-400 text-xs">No account required.</div>
                </div>
                <!-- Step 2 -->
                <div class="rounded-3xl bg-white/5 ring-1 ring-white/10 p-6">
                    <div class="flex items-center gap-3">
                        <div class="h-9 w-9 rounded-full bg-gradient-to-br from-emerald-500 to-cyan-500 text-white flex items-center justify-center font-extrabold">2</div>
                        <h3 class="text-lg font-semibold">Checkout securely</h3>
                    </div>
                    <p class="mt-3 text-sm text-zinc-300">Enter your name and email, apply coupons if you have one, and pay with <span class="font-semibold text-white">Paystack</span>.</p>
                    <ul class="mt-3 space-y-1 text-xs text-zinc-400 list-disc list-inside">
                        <li>Instant confirmation</li>
                        <li>Mobile friendly</li>
                    </ul>
                </div>
                <!-- Step 3 -->
                <div class="rounded-3xl bg-white/5 ring-1 ring-white/10 p-6">
                    <div class="flex items-center gap-3">
                        <div class="h-9 w-9 rounded-full bg-gradient-to-br from-rose-500 to-orange-500 text-white flex items-center justify-center font-extrabold">3</div>
                        <h3 class="text-lg font-semibold">Get your ticket</h3>
                    </div>
                    <p class="mt-3 text-sm text-zinc-300">You’ll receive a QR ticket instantly. Download as <span class="font-semibold text-white">PNG</span> or print, and present it at the gate.</p>
                    <div class="mt-4 text-zinc-400 text-xs">You’ll also get a receipt PDF if needed.</div>
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
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-white/5 ring-1 ring-white/10">Past 30 days</span>
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
                        <a href="{{ route('events.show', $event) }}" class="absolute inset-0 z-10">
                            <span class="sr-only">Open {{ $event->title }}</span>
                        </a>
                        <div class="relative aspect-[10/13]">
                            @if($event->image_path)
                                <img src="{{ Storage::url($event->image_path) }}" alt="{{ $event->title }}" class="absolute inset-0 h-full w-full object-cover group-hover:scale-105 duration-500 ease-out"/>
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
    <section id="host" class="py-20">
        <div class="max-w-7xl mx-auto px-6">
<div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-12 items-start">
                <!-- Left: Pitch card -->
<div class="relative">
<div class="absolute -inset-[10px] rounded-[1.7rem] bg-gradient-to-r from-purple-500/40 via-fuchsia-500/30 to-purple-400/40 blur-lg -z-10 pointer-events-none"></div>
    <div class="rounded-[1.6rem] p-[3px] bg-gradient-to-r from-purple-600 via-fuchsia-500 to-purple-400">
        <div class="rounded-[1.55rem] bg-white/5" style="padding: 15px 15px 0 15px;">
                    <h3 class="text-3xl sm:text-4xl font-extrabold">Host with us</h3>
                    <p class="mt-3 text-zinc-300">Plan the vibe, we’ll handle ticketing, payments, and guest experience.</p>
                    <ul class="mt-6 space-y-3 text-sm text-zinc-300">
                        <li class="flex items-start gap-3">
                            <svg class="h-5 w-5 text-emerald-300" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-7 9a.75.75 0 01-1.127.07l-3-3a.75.75 0 011.06-1.06l2.39 2.39 6.473-8.317a.75.75 0 011.06-.135z" clip-rule="evenodd"/></svg>
                            Instant payouts and Paystack checkout
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="h-5 w-5 text-emerald-300" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-7 9a.75.75 0 01-1.127.07l-3-3a.75.75 0 011.06-1.06l2.39 2.39 6.473-8.317a.75.75 0 011.06-.135z" clip-rule="evenodd"/></svg>
                            QR-code tickets and capacity protection
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="h-5 w-5 text-emerald-300" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-7 9a.75.75 0 01-1.127.07l-3-3a.75.75 0 011.06-1.06l2.39 2.39 6.473-8.317a.75.75 0 011.06-.135z" clip-rule="evenodd"/></svg>
                            Coupons, early-bird pricing, CSV exports
                        </li>
                    </ul>
                    @if (session('status'))
                        <div class="mt-6 p-3 rounded-lg bg-emerald-500/10 ring-1 ring-emerald-500/30 text-emerald-300">{{ session('status') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="mt-4 p-3 rounded-lg bg-red-500/10 ring-1 ring-red-500/30 text-red-300">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mt-6 flex justify-center">
<img src="/images/Group%202.png" alt="Host with us" loading="lazy" decoding="async" width="1200" height="800" class="w-full max-w-md h-auto max-h-80 object-contain rounded-2xl" />
                    </div>
                </div>
        </div>
</div>

                <!-- Right: Modern form card -->
                <div class="relative">
                    <div class="rounded-[1.6rem] border-2 border-white p-[2px] bg-transparent shadow-none">
<form method="POST" action="{{ route('host.request.store') }}" class="host-form rounded-[1.55rem] bg-white/5 border border-white/30 grid grid-cols-1 sm:grid-cols-2 gap-6 sm:gap-8 lg:gap-10 w-full md:max-w-2xl mx-auto" style="padding:15px;">
                        @csrf
                        <div class="sm:col-span-1">
                            <label class="block text-xs uppercase tracking-widest text-zinc-400" for="name">Your name</label>
<input id="name" name="name" type="text" required value="{{ old('name') }}" class="mt-2 block w-full px-0 py-3" />
                        </div>
                        <div class="sm:col-span-1">
                            <label class="block text-xs uppercase tracking-widest text-zinc-400" for="email">Email</label>
<input id="email" name="email" type="email" required value="{{ old('email') }}" class="mt-2 block w-full px-0 py-3" />
                        </div>
                        <div class="sm:col-span-1">
                            <label class="block text-xs uppercase tracking-widest text-zinc-400" for="phone">Phone</label>
<input id="phone" name="phone" type="text" value="{{ old('phone') }}" class="mt-2 block w-full px-0 py-3" />
                        </div>
                        <div class="sm:col-span-1">
                            <label class="block text-xs uppercase tracking-widest text-zinc-400" for="event_title">Event name</label>
<input id="event_title" name="event_title" type="text" required value="{{ old('event_title') }}" class="mt-2 block w-full px-0 py-3" />
                        </div>
                        <div class="sm:col-span-1">
                            <label class="block text-xs uppercase tracking-widest text-zinc-400" for="event_date">Event date</label>
<input id="event_date" name="event_date" type="datetime-local" min="{{ now()->format('Y-m-d\\TH:i') }}" value="{{ old('event_date') }}" class="mt-2 block w-full px-0 py-3" />
                        </div>
                        <div class="sm:col-span-1">
                            <label class="block text-xs uppercase tracking-widest text-zinc-400" for="venue">Venue / City</label>
<input id="venue" name="venue" type="text" value="{{ old('venue') }}" class="mt-2 block w-full px-0 py-3" />
                        </div>
                        <div class="sm:col-span-1">
                            <label class="block text-xs uppercase tracking-widest text-zinc-400" for="expected_attendees">Expected attendees</label>
<input id="expected_attendees" name="expected_attendees" type="number" min="1" value="{{ old('expected_attendees') }}" class="mt-2 block w-full px-0 py-3" />
                        </div>
                        <div class="sm:col-span-1">
                            <label class="block text-xs uppercase tracking-widest text-zinc-400" for="budget">Budget (₦)</label>
<input id="budget" name="budget" type="number" step="0.01" min="0" value="{{ old('budget') }}" class="mt-2 block w-full px-0 py-3" />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs uppercase tracking-widest text-zinc-400" for="message">Tell us about the event</label>
<textarea id="message" name="message" rows="5" class="mt-2 block w-full px-0 py-3">{{ old('message') }}</textarea>
                        </div>
                        <div class="sm:col-span-2">
<button class="mx-auto inline-flex items-center justify-center px-8 py-4 rounded-2xl bg-white text-black font-semibold hover:bg-zinc-100 transition">Submit request</button>
                        </div>
                    </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Flatpickr (modern date/time picker) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <style>
      /* Host with us form: line inputs */
      .host-form input[type="text"],
      .host-form input[type="email"],
      .host-form input[type="number"],
      .host-form input[type="datetime-local"],
      .host-form textarea {
        -webkit-tap-highlight-color: transparent;
        background: transparent !important;
        border: none !important;
        border-bottom: 1px solid #FFFFFF !important;
        opacity: 0.9;
        border-radius: 0 !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
        box-shadow: none !important;
      }
      .host-form input:focus,
      .host-form textarea:focus {
        outline: none !important;
        border-bottom-color: #FFFFFF !important;
        opacity: 1;
      }
      .host-form ::placeholder { color: rgba(255,255,255,0.5); }
    </style>
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
                <h3 class="text-3xl sm:text-4xl font-bold">Ready for your next night out?</h3>
                <p class="mt-2 text-zinc-300">Browse all events happening soon.</p>
                <div class="mt-6">
                    <a href="{{ route('events.index') }}" class="inline-flex items-center px-8 py-3 rounded-full bg-white text-black font-semibold hover:bg-zinc-100 transition">Explore all events</a>
                </div>
            </div>
        </div>
    </section>


    @endsection
