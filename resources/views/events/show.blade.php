@extends('layouts.public')

@section('title', $event->title . ' | ' . config('app.name', '2DAWN'))
@section('meta_description', $event->description ? \Illuminate\Support\Str::limit(strip_tags($event->description), 160, '') : 'Buy tickets for ' . $event->title)
@section('canonical', $event->public_url)
@section('meta_image', $event->image_url ?? asset('favicon.ico'))
@section('og:type', 'article')

@php
  $json = [
    '@context' => 'https://schema.org',
    '@type' => 'Event',
    'name' => $event->title,
    'description' => $event->description ? strip_tags($event->description) : null,
    'image' => $event->image_url ?: null,
'url' => $event->public_url,
    'startDate' => optional($event->starts_at)?->toAtomString(),
    'endDate' => optional($event->ends_at)?->toAtomString(),
    'location' => $event->venue ? [
      '@type' => 'Place',
      'name' => $event->venue,
      'address' => $event->venue,
    ] : null,
    'eventStatus' => ($event->ends_at && $event->ends_at->isPast()) ? 'https://schema.org/EventCompleted' : 'https://schema.org/EventScheduled',
    'offers' => [
      '@type' => 'Offer',
      'price' => (string) ($event->price ?? 0),
      'priceCurrency' => 'NGN',
      'availability' => (is_null($event->capacity) || (int)$event->capacity > 0) ? 'https://schema.org/InStock' : 'https://schema.org/SoldOut',
      'url' => route('events.buy', $event),
    ],
  ];
@endphp
@section('jsonld')
  <script type="application/ld+json">{!! json_encode(array_filter($json, fn($v) => $v !== null), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
  @php
    $breadcrumbs = [
      '@context' => 'https://schema.org',
      '@type' => 'BreadcrumbList',
      'itemListElement' => [
        [ '@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home') ],
        [ '@type' => 'ListItem', 'position' => 2, 'name' => 'Events', 'item' => route('events.index') ],
[ '@type' => 'ListItem', 'position' => 3, 'name' => $event->title, 'item' => $event->public_url ],
      ],
    ];
  @endphp
  <script type="application/ld+json">{!! json_encode($breadcrumbs, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
@endsection

@section('content')
<section class="py-10">
  <div class="max-w-7xl mx-auto px-6 mb-4 flex justify-between">
    <a href="{{ url('/') }}" class="text-sm text-zinc-300 hover:text-white hover:underline underline-offset-4">Home</a>
    <a href="{{ route('events.index') }}" class="text-sm text-zinc-300 hover:text-white hover:underline underline-offset-4">All Events</a>
  </div>
  <div class="max-w-7xl mx-auto px-6">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
      <!-- Left: Flyer -->
      <div>
        <div class="relative rounded-3xl overflow-hidden ring-1 ring-white/10 bg-white/5 mx-auto" style="width:min(380px,100%); aspect-ratio: 10/13;">
          @if($event->image_url)
            <img src="{{ $event->image_url }}" alt="{{ $event->title }}" class="absolute inset-0 w-full h-full object-cover"/>
          @else
            <div class="absolute inset-0 h-full w-full bg-gradient-to-br from-indigo-500 via-fuchsia-500 to-rose-500"></div>
          @endif
        </div>
      </div>

      <!-- Right: Details + Buy -->
      <div>
        <div class="rounded-3xl ring-1 ring-white/10 bg-white/5 p-6 lg:sticky lg:top-24">
          <h1 class="text-3xl font-extrabold leading-tight">{{ $event->title }}</h1>
          <div class="mt-2 text-zinc-300 text-sm">
            {{ optional($event->starts_at)->format('D, M j, Y g:i A') }} @if($event->ends_at) – {{ optional($event->ends_at)->format('g:i A') }} @endif
            @if ($event->venue)
              • {{ $event->venue }}
            @endif
          </div>

          <div class="mt-4 text-zinc-200 text-sm leading-relaxed whitespace-pre-line">
            @if ($event->description)
              {!! nl2br(e($event->description)) !!}
            @else
              <span class="text-zinc-400">No description provided.</span>
            @endif
          </div>

          @php
            $priceToShow = $event->price;
            if (!is_null($event->early_bird_price) && !is_null($event->early_bird_ends_at) && now()->lte($event->early_bird_ends_at)) {
              $priceToShow = $event->early_bird_price;
            }
          @endphp
          @php
            $now = now();
            $isPast = ($event->ends_at && $event->ends_at->lt($now)) || (!$event->ends_at && $event->starts_at && $event->starts_at->lt($now));
            $remaining = is_null($event->capacity) ? null : max(0, (int)$event->capacity);
          @endphp
          <div class="mt-6 pt-4 border-t border-white/10">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                @if(!is_null($priceToShow))
                  <div class="text-sm text-zinc-300">From <span class="text-white font-bold">₦{{ number_format($priceToShow, 2) }}</span></div>
                @else
                  <div class="text-sm text-zinc-300">Free</div>
                @endif
                <span id="slots-badge" class="hidden px-2 py-1 rounded-full text-xs bg-white/10 ring-1 ring-white/15">—</span>
              </div>
              @if($isPast || ($remaining !== null && $remaining <= 0))
                <button disabled class="inline-flex items-center px-4 py-2 rounded-xl bg-zinc-700/50 text-zinc-300 text-sm font-semibold cursor-not-allowed">Sales closed</button>
              @else
                <a href="{{ route('events.buy', $event) }}" class="inline-flex items-center px-4 py-2 rounded-xl bg-white text-black text-sm font-semibold hover:bg-zinc-100 transition">Buy ticket</a>
              @endif
            </div>
            <div class="mt-4 flex flex-wrap items-center gap-2 text-xs">
              <span class="text-zinc-400 uppercase tracking-widest">Share:</span>
              <a href="https://wa.me/?text={{ urlencode(($event->title ?? 'Event').' — '.$event->public_url) }}" target="_blank" class="px-2.5 py-1 rounded-full bg-white/10 ring-1 ring-white/10 hover:bg-white/20">WhatsApp</a>
              <a href="https://twitter.com/intent/tweet?text={{ urlencode($event->title ?? 'Event') }}&url={{ urlencode($event->public_url) }}" target="_blank" class="px-2.5 py-1 rounded-full bg-white/10 ring-1 ring-white/10 hover:bg-white/20">X</a>
              <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($event->public_url) }}" target="_blank" class="px-2.5 py-1 rounded-full bg-white/10 ring-1 ring-white/10 hover:bg-white/20">Facebook</a>
              <button id="copy-link-ev" class="px-2.5 py-1 rounded-full bg-white/10 ring-1 ring-white/10 hover:bg-white/20" data-url="{{ $event->public_url }}">Copy link</button>
            </div>
            @php
              $start = optional($event?->starts_at);
              $end = optional($event?->ends_at) ?: optional($event?->starts_at)?->copy()->addHours(2);
              $startUtc = $start ? $start->copy()->utc()->format('Ymd\THis\Z') : null;
              $endUtc = $end ? $end->copy()->utc()->format('Ymd\THis\Z') : null;
              $gc = $startUtc && $endUtc ? ('https://calendar.google.com/calendar/render?action=TEMPLATE&text=' . urlencode($event?->title ?? 'Event') . '&dates='.$startUtc.'/'.$endUtc.'&details=' . urlencode($event->public_url) . ($event?->venue ? ('&location='.urlencode($event->venue)) : '')) : null;
            @endphp
            <div class="mt-2">
              <button id="set-reminder-ev" class="px-2.5 py-1 rounded-full bg-white text-black hover:bg-zinc-100 text-xs">Set reminder</button>
            </div>
          </div>

          <script>
            document.getElementById('copy-link-ev')?.addEventListener('click', async (e) => {
              const url = e.currentTarget.getAttribute('data-url');
              try { await navigator.clipboard.writeText(url); e.currentTarget.textContent = 'Copied!'; setTimeout(()=>e.currentTarget.textContent='Copy link', 1500);} catch(_) { alert(url); }
            });
          </script>
          <script>
            (function(){
              var btn=document.getElementById('set-reminder-ev'); if(!btn) return;
              btn.addEventListener('click', function(){
                var ua = navigator.userAgent || '';
                var isiOS = /iP(hone|ad|od)/i.test(ua);
                var isMac = /Macintosh/.test(ua);
                var isAndroid = /Android/i.test(ua);
                var icsUrl = @json(route('events.ics', $event) . '?alarm=60');
                var gcal = @json($gc);
                if (isiOS || isMac) {
                  window.location.href = icsUrl;
                } else if (isAndroid && gcal) {
                  window.open(gcal, '_blank');
                } else {
                  if (gcal) { window.open(gcal, '_blank'); } else { window.location.href = icsUrl; }
                }
              });
            })();
          </script>

          @if ($errors->any())
            <div class="mt-6 p-3 bg-red-500/10 text-red-300 rounded ring-1 ring-red-500/30">
              <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          @if(!($isPast || ($remaining !== null && $remaining <= 0)))
          <div class="mt-6 pt-4 border-t border-white/10">
            <h2 class="text-sm uppercase tracking-widest text-zinc-400">Or buy for a friend</h2>
            <form method="POST" action="{{ route('orders.create', $event, false) }}" class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3">
              @csrf
              <div class="sm:col-span-1">
                <label class="block text-xs text-zinc-400" for="friend_name">Friend's name</label>
                <input id="friend_name" name="buyer_name" type="text" placeholder="Jane Doe" value="{{ old('buyer_name') }}" class="mt-1 block w-full rounded-lg bg-black/30 border border-white/10 focus:border-white/30 focus:ring-0 px-3 py-2" />
              </div>
              <div class="sm:col-span-1">
                <label class="block text-xs text-zinc-400" for="friend_email">Friend's email</label>
                <input id="friend_email" name="buyer_email" type="email" placeholder="friend@example.com" value="{{ old('buyer_email') }}" class="mt-1 block w-full rounded-lg bg-black/30 border border-white/10 focus:border-white/30 focus:ring-0 px-3 py-2" />
              </div>
              <div class="sm:col-span-1">
                <label class="block text-xs text-zinc-400" for="friend_quantity">Quantity</label>
                <input id="friend_quantity" name="quantity" type="number" min="1" step="1" value="{{ old('quantity', 1) }}" class="mt-1 block w-full rounded-lg bg-black/30 border border-white/10 focus:border-white/30 focus:ring-0 px-3 py-2" />
              </div>
              <div class="sm:col-span-1">
                <label class="block text-xs text-zinc-400" for="friend_coupon">Coupon (optional)</label>
                <input id="friend_coupon" name="coupon" type="text" value="{{ old('coupon') }}" class="mt-1 block w-full rounded-lg bg-black/30 border border-white/10 focus:border-white/30 focus:ring-0 px-3 py-2" />
              </div>
              <input type="hidden" name="buyer_phone" value="{{ old('buyer_phone') }}" />
              <div class="sm:col-span-2">
                <button class="w-full inline-flex items-center justify-center px-6 py-3 rounded-xl bg-white text-black font-semibold hover:bg-zinc-100 transition">Proceed to Paystack for friend</button>
              </div>
            </form>
          </div>
          @else
          <div class="mt-6 p-3 bg-amber-500/10 text-amber-300 rounded ring-1 ring-amber-500/30">Ticket sales are closed for this event.</div>
          @endif
        </div>
      </div>
    </div>
  </div>
  <section class="py-6">
    <div class="max-w-5xl mx-auto px-6">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
        <!-- Comments list -->
        <div class="lg:col-span-2">
          <h2 id="comments" class="text-xl font-bold">Comments</h2>
          @if (session('status'))
            <div class="mt-3 p-3 rounded-lg bg-emerald-500/10 ring-1 ring-emerald-500/30 text-emerald-300">{{ session('status') }}</div>
          @endif
          <div class="mt-4 space-y-4">
            @forelse($event->comments as $comment)
              <div class="rounded-2xl bg-white/5 ring-1 ring-white/10 p-4">
                <div class="flex items-center justify-between">
                  <div class="text-sm text-zinc-300 font-semibold">{{ $comment->name }}</div>
                  <div class="text-xs text-zinc-500">{{ $comment->created_at->diffForHumans() }}</div>
                </div>
                <div class="mt-2 text-sm text-zinc-200 whitespace-pre-line">{{ $comment->content }}</div>
              </div>
            @empty
              <div class="text-zinc-400">Be the first to comment.</div>
            @endforelse
          </div>
        </div>

        <!-- Comment form -->
        <div class="lg:col-span-2">
          <div class="mt-6 rounded-3xl bg-white/5 ring-1 ring-white/10 p-6">
            <h3 class="text-sm uppercase tracking-widest text-zinc-400">Add a comment</h3>
            <form method="POST" action="{{ route('events.comments.store', $event) }}" class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-4">
              @csrf
              <div>
                <label class="block text-xs text-zinc-400" for="c_name">Your name</label>
                <input id="c_name" name="name" type="text" required maxlength="80" class="mt-1 block w-full rounded-xl bg-black/30 border border-white/10 focus:border-white/30 focus:ring-2 focus:ring-white/20 outline-none px-3 py-2" />
              </div>
              <div>
                <label class="block text-xs text-zinc-400" for="c_email">Email (optional)</label>
                <input id="c_email" name="email" type="email" maxlength="120" class="mt-1 block w-full rounded-xl bg-black/30 border border-white/10 focus:border-white/30 focus:ring-2 focus:ring-white/20 outline-none px-3 py-2" />
              </div>
              <div class="sm:col-span-2">
                <label class="block text-xs text-zinc-400" for="c_content">Comment</label>
                <textarea id="c_content" name="content" rows="3" required maxlength="2000" class="mt-1 block w-full rounded-xl bg-black/30 border border-white/10 focus:border-white/30 focus:ring-2 focus:ring-white/20 outline-none px-3 py-2"></textarea>
              </div>
              <div class="sm:col-span-2">
                <button class="inline-flex items-center px-6 py-3 rounded-xl bg-white text-black font-semibold hover:bg-zinc-100 transition">Post comment</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
<script>
  // Live remaining slots updater
  document.addEventListener('DOMContentLoaded', () => {
    const badge = document.getElementById('slots-badge');
    if (!badge) return;
    const url = @json(route('events.remaining', $event));
    const update = async () => {
      try{
        const res = await fetch(url, { headers: { 'Accept':'application/json' } });
        if (!res.ok) return;
        const data = await res.json();
        if (data.status === 'past') {
          badge.textContent = 'Sales closed';
          badge.classList.remove('hidden');
          return;
        }
        if (data.remaining === null) {
          badge.textContent = 'Unlimited';
        } else {
          badge.textContent = data.remaining + ' left';
        }
        badge.classList.remove('hidden');
      } catch(e){ /* ignore */ }
    };
    update();
    setInterval(update, 15000);
  });
</script>
</section>
@endsection
