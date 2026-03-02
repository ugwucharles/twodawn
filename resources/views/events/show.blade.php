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
<section class="bg-white min-h-screen pt-4 pb-16">
  <div class="max-w-6xl mx-auto px-6">
    {{-- Breadcrumbs --}}
    <nav class="flex mb-8 text-[13px] font-medium text-gray-400">
        <a href="{{ route('events.index') }}" class="hover:text-tix-orange">Discover</a>
        <span class="mx-2">/</span>
        <span class="text-gray-900 truncate uppercase tracking-wider">{{ $event->title }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
      {{-- Left Column: Visuals & Main CTA --}}
      <div class="lg:col-span-4 flex flex-col gap-6">
        <div class="relative w-full aspect-square rounded-[32px] overflow-hidden bg-gray-50 shadow-sm border border-gray-100">
          @if($event->image_url)
            <img src="{{ $event->image_url }}" alt="{{ $event->title }}" class="absolute inset-0 w-full h-full object-cover"/>
          @else
            <div class="absolute inset-0 h-full w-full bg-gradient-to-br from-indigo-100 to-white flex items-center justify-center p-12 opacity-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-full h-full text-indigo-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
            </div>
          @endif
        </div>

        @php
            $now = now();
            $isPast = ($event->ends_at && $event->ends_at->lt($now)) || (!$event->ends_at && $event->starts_at && $event->starts_at->lt($now));
            $remaining = is_null($event->capacity) ? null : max(0, (int)$event->capacity);
            $priceToShow = $event->price;
            if (!is_null($event->early_bird_price) && !is_null($event->early_bird_ends_at) && $now->lte($event->early_bird_ends_at)) {
              $priceToShow = $event->early_bird_price;
            }
        @endphp

        <div class="flex flex-col gap-4">
            @if($isPast)
                <button disabled class="w-full py-4 px-8 rounded-2xl bg-gray-100 text-gray-500 font-bold text-center cursor-not-allowed">
                    Sold Out / Closed
                </button>
            @else
                <a href="{{ route('events.buy', $event) }}" 
                   class="w-full py-4 px-8 rounded-2xl bg-tix-orange text-white font-bold text-lg text-center shadow-[0_12px_24px_-10px_rgba(240,85,55,0.4)] hover:shadow-tix-orange/30 hover:-translate-y-0.5 transition-all duration-300">
                    {{ $priceToShow > 0 ? 'Get a Ticket' : 'Get a Ticket (Free)' }}
                </a>
            @endif

            <div class="flex flex-wrap items-center justify-center gap-4 py-2 border-t border-gray-100 mt-2">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">Share this event</span>
                <div class="flex items-center gap-3">
                    <a href="https://wa.me/?text={{ urlencode(($event->title ?? 'Event').' — '.$event->public_url) }}" target="_blank" class="text-gray-400 hover:text-green-500 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.414 0 0 5.414 0 12.05c0 2.123.551 4.197 1.595 6.02L0 24l6.135-1.61a11.751 11.751 0 005.91 1.611h.005c6.637 0 12.05-5.414 12.05-12.05a11.815 11.815 0 00-3.487-8.522z"/></svg>
                    </a>
                    <a href="https://twitter.com/intent/tweet?text={{ urlencode($event->title ?? 'Event') }}&url={{ urlencode($event->public_url) }}" target="_blank" class="text-gray-400 hover:text-blue-400 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.84 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                    </a>
                </div>
            </div>
        </div>
      </div>

      {{-- Right Column: Information --}}
      <div class="lg:col-span-8 flex flex-col gap-10">
        {{-- Title and Badges --}}
        <div>
           <div class="flex items-center gap-3 mb-4">
               @if($event->price > 0)
                    <span class="inline-flex items-center px-4 py-1 rounded-full text-xs font-bold bg-tix-orange/10 text-tix-orange uppercase tracking-wider">Paid Event</span>
               @else
                    <span class="inline-flex items-center px-4 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700 uppercase tracking-wider">Free Event</span>
               @endif
           </div>
           <h1 class="text-4xl sm:text-5xl font-black text-gray-900 leading-[1.1] mb-8">{{ $event->title }}</h1>
           
           {{-- Quick Stats Stack --}}
           <div class="space-y-6">
               @if($event->starts_at)
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center shrink-0 border border-gray-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 00-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-gray-900 font-bold text-lg">{{ $event->starts_at->format('l, F jS Y') }}</p>
                            <p class="text-gray-500 font-medium">{{ $event->starts_at->format('g:i A') }} @if($event->ends_at) - {{ $event->ends_at->format('g:i A') }} @endif</p>
                        </div>
                    </div>
               @endif

               @if($event->venue)
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center shrink-0 border border-gray-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-gray-900 font-bold text-lg">{{ $event->venue }}</p>
                            <div class="mt-2 inline-block px-3 py-1.5 rounded-lg bg-red-50 text-red-700 text-xs font-bold uppercase tracking-wide">
                                Detailed Location on Ticket
                            </div>
                        </div>
                    </div>
               @endif
           </div>
        </div>

        {{-- About Section --}}
        <div>
            <h2 class="text-sm font-black text-gray-400 uppercase tracking-[0.2em] mb-4">About this event</h2>
            <div class="prose prose-zinc prose-lg max-w-none text-gray-700 leading-relaxed font-medium">
                @if ($event->description)
                    {!! nl2br(e($event->description)) !!}
                @else
                    <p class="text-gray-400 italic">No further details provided.</p>
                @endif
            </div>
        </div>

        {{-- Secondary CTA (Sticky on Mobile) --}}
        <div class="mt-8 pt-8 border-t border-gray-100">
             <div class="flex items-center justify-between gap-6">
                 <div>
                     <p class="text-gray-500 text-sm font-bold uppercase tracking-wider mb-1">Ticket Price</p>
                     <p class="text-3xl font-black text-gray-900">
                        @if($priceToShow > 0)
                            ₦{{ number_format($priceToShow, 2) }}
                        @else
                            Free
                        @endif
                     </p>
                 </div>
                 @if(!$isPast)
                    <a href="{{ route('events.buy', $event) }}" class="px-8 py-3 rounded-xl bg-gray-900 text-white font-bold hover:bg-black transition-colors">
                        Buy Now
                    </a>
                 @endif
             </div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- Comments Section --}}
<section class="bg-gray-50 py-16">
    <div class="max-w-4xl mx-auto px-6">
        <h2 id="comments" class="text-2xl font-black text-gray-900 mb-8 flex items-center gap-3">
            Comments
            <span class="text-sm font-medium text-gray-400 bg-white px-3 py-1 rounded-full border border-gray-200">{{ $event->comments->count() }}</span>
        </h2>

        @if (session('status'))
            <div class="mb-8 p-4 rounded-2xl bg-green-50 text-green-700 border border-green-100 text-sm font-bold">{{ session('status') }}</div>
        @endif

        <div class="space-y-6">
            @forelse($event->comments as $comment)
                <div class="p-6 rounded-3xl bg-white border border-gray-100 shadow-sm relative overflow-hidden">
                    <div class="flex items-center justify-between mb-4">
                      <div class="font-black text-gray-900">{{ $comment->name }}</div>
                      <div class="text-xs font-bold text-gray-400">{{ $comment->created_at->diffForHumans() }}</div>
                    </div>
                    <div class="text-gray-600 font-medium leading-relaxed">{{ $comment->content }}</div>
                </div>
            @empty
                <div class="text-center py-12 bg-white rounded-3xl border border-dashed border-gray-300">
                    <p class="text-gray-400 font-bold italic">No comments yet. Share your thoughts!</p>
                </div>
            @endforelse
        </div>

        <div class="mt-12 bg-white rounded-[32px] p-8 sm:p-10 shadow-sm border border-gray-100">
            <h3 class="text-lg font-black text-gray-900 mb-6">Ask a question or leave a comment</h3>
            <form method="POST" action="{{ route('events.comments.store', $event) }}" class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1.5" for="c_name">Name</label>
                    <input id="c_name" name="name" type="text" required maxlength="80" 
                           class="w-full rounded-2xl bg-gray-50 border-gray-100 focus:border-tix-orange focus:ring-tix-orange px-4 py-3 text-gray-900 font-bold" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1.5" for="c_email">Email (Visible only to you)</label>
                    <input id="c_email" name="email" type="email" maxlength="120" 
                           class="w-full rounded-2xl bg-gray-50 border-gray-100 focus:border-tix-orange focus:ring-tix-orange px-4 py-3 text-gray-900 font-bold" />
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1.5" for="c_content">Message</label>
                    <textarea id="c_content" name="content" rows="4" required maxlength="2000" 
                              class="w-full rounded-2xl bg-gray-50 border-gray-100 focus:border-tix-orange focus:ring-tix-orange px-4 py-3 text-gray-900 font-medium"></textarea>
                </div>
                <div class="sm:col-span-2">
                    <button class="w-full sm:w-auto px-10 py-4 rounded-2xl bg-tix-orange text-white font-black hover:scale-105 transition-transform">Post Comment</button>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
