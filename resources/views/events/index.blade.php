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
<section
  class="relative bg-white pt-8 md:pt-10 pb-16"
  x-data="{
    showPrice:false,
    showDate:false,
    showLocation:false,
    locationLabel: '{{ request('state_label', 'Lagos') }}'
  }"
>
  <div class="max-w-6xl md:max-w-7xl mx-auto px-4 md:px-6 lg:px-10">

    {{-- Top: title + location selector --}}
    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6 mb-8">
      <div class="flex-1 min-w-0">
        <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-eventbrite-dark mb-1">
          Discover events
        </h1>
        @if($hasQuery)
          <p class="text-sm text-eventbrite-dark">
            Showing results for <span class="font-semibold">“{{ request('q') }}”</span>
          </p>
        @else
          <p class="text-sm text-eventbrite-dark">
            Browse upcoming events and find your next experience.
          </p>
        @endif
      </div>
      <div class="relative">
        <p class="text-xs font-semibold tracking-[0.18em] text-eventbrite-gray-400 uppercase">
          Find an event in
        </p>
        <button type="button"
                @click="showLocation = !showLocation; showPrice=false; showDate=false"
                class="mt-2 inline-flex items-center px-4 py-2 rounded-full border border-eventbrite-gray-100 bg-white text-[15px] font-medium text-eventbrite-dark shadow-sm hover:border-eventbrite-gray-400 hover:shadow-md transition">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-eventbrite-gray-400" fill="none"
               viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
          <span class="ml-2 mr-1" x-text="locationLabel">Lagos</span>
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-eventbrite-gray-400 ml-1" fill="none"
               viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
          </svg>
        </button>
        @php
          $ngStates = [
            'abia' => 'Abia',
            'adamawa' => 'Adamawa',
            'akwa-ibom' => 'Akwa Ibom',
            'anambra' => 'Anambra',
            'bauchi' => 'Bauchi',
            'bayelsa' => 'Bayelsa',
            'benue' => 'Benue',
            'borno' => 'Borno',
            'cross-river' => 'Cross River',
            'delta' => 'Delta',
            'ebonyi' => 'Ebonyi',
            'edo' => 'Edo',
            'ekiti' => 'Ekiti',
            'enugu' => 'Enugu',
            'gombe' => 'Gombe',
            'imo' => 'Imo',
            'jigawa' => 'Jigawa',
            'kaduna' => 'Kaduna',
            'kano' => 'Kano',
            'katsina' => 'Katsina',
            'kebbi' => 'Kebbi',
            'kogi' => 'Kogi',
            'kwara' => 'Kwara',
            'lagos' => 'Lagos',
            'nasarawa' => 'Nasarawa',
            'niger' => 'Niger',
            'ogun' => 'Ogun',
            'ondo' => 'Ondo',
            'osun' => 'Osun',
            'oyo' => 'Oyo',
            'plateau' => 'Plateau',
            'rivers' => 'Rivers',
            'sokoto' => 'Sokoto',
            'taraba' => 'Taraba',
            'yobe' => 'Yobe',
            'zamfara' => 'Zamfara',
            'abuja' => 'Abuja (FCT)',
          ];
        @endphp
        <div
          x-cloak
          x-show="showLocation"
          x-transition
          @click.outside="showLocation=false"
          class="absolute z-20 mt-2 max-h-[320px] w-64 overflow-y-auto rounded-xl border border-eventbrite-gray-100 bg-white shadow-lg py-2 text-sm text-eventbrite-dark"
        >
          @foreach($ngStates as $code => $label)
            <button type="button"
                    class="w-full text-left px-4 py-1.5 hover:bg-eventbrite-gray-50"
                    @click="locationLabel='{{ $label }}'; showLocation=false; window.location='{{ route('events.index', array_merge(request()->except('page'), ['state' => $code, 'state_label' => $label])) }}'">
              {{ $label }}
            </button>
          @endforeach
        </div>
      </div>
    </div>

    {{-- Filters row --}}
    <div class="flex flex-col gap-4 mb-6">
      <div class="flex flex-wrap items-center gap-3 relative">
        <div class="relative">
          <button type="button"
                  @click="window.location='{{ route('events.index') }}'"
                  class="inline-flex items-center px-4 py-2 rounded-full border border-eventbrite-gray-100 bg-white text-sm font-medium text-eventbrite-dark hover:border-eventbrite-gray-400 transition">
            All events
          </button>
        </div>
        <div class="relative">
          <button type="button"
                  @click="showPrice = !showPrice; showDate=false; showLocation=false"
                  class="inline-flex items-center px-4 py-2 rounded-full border border-eventbrite-gray-100 bg-white text-sm font-medium text-eventbrite-dark hover:border-eventbrite-gray-400 transition">
            Price
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2 text-eventbrite-gray-400" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
          <div x-cloak x-show="showPrice"
               x-transition
               @click.outside="showPrice=false"
               class="absolute z-20 mt-2 w-44 rounded-xl border border-eventbrite-gray-100 bg-white shadow-lg py-2 text-sm text-eventbrite-dark">
            <button type="button"
                    class="w-full text-left px-4 py-1.5 hover:bg-eventbrite-gray-50"
                    @click="showPrice=false; window.location='{{ route('events.index', request()->except(['page','price'])) }}'">
              Any price
            </button>
            <button type="button"
                    class="w-full text-left px-4 py-1.5 hover:bg-eventbrite-gray-50"
                    @click="showPrice=false; window.location='{{ route('events.index', array_merge(request()->except('page'), ['price' => 'free'])) }}'">
              Free
            </button>
            <button type="button"
                    class="w-full text-left px-4 py-1.5 hover:bg-eventbrite-gray-50"
                    @click="showPrice=false; window.location='{{ route('events.index', array_merge(request()->except('page'), ['price' => 'paid'])) }}'">
              Paid
            </button>
          </div>
        </div>
        <div class="relative">
          <button type="button"
                  @click="showDate = !showDate; showPrice=false; showLocation=false"
                  class="inline-flex items-center px-4 py-2 rounded-full border border-eventbrite-gray-100 bg-white text-sm font-medium text-eventbrite-dark hover:border-eventbrite-gray-400 transition">
            Date
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2 text-eventbrite-gray-400" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
          <div x-cloak x-show="showDate"
               x-transition
               @click.outside="showDate=false"
               class="absolute z-20 mt-2 w-52 rounded-xl border border-eventbrite-gray-100 bg-white shadow-lg py-2 text-sm text-eventbrite-dark">
            <button type="button"
                    class="w-full text-left px-4 py-1.5 hover:bg-eventbrite-gray-50"
                    @click="showDate=false; window.location='{{ route('events.index', request()->except(['page','date'])) }}'">
              Any date
            </button>
            <button type="button"
                    class="w-full text-left px-4 py-1.5 hover:bg-eventbrite-gray-50"
                    @click="showDate=false; window.location='{{ route('events.index', array_merge(request()->except('page'), ['date' => 'today'])) }}'">
              Today
            </button>
            <button type="button"
                    class="w-full text-left px-4 py-1.5 hover:bg-eventbrite-gray-50"
                    @click="showDate=false; window.location='{{ route('events.index', array_merge(request()->except('page'), ['date' => 'weekend'])) }}'">
              This weekend
            </button>
            <button type="button"
                    class="w-full text-left px-4 py-1.5 hover:bg-eventbrite-gray-50"
                    @click="showDate=false; window.location='{{ route('events.index', array_merge(request()->except('page'), ['date' => 'next-week'])) }}'">
              Next week
            </button>
          </div>
        </div>
      </div>
      <div class="flex items-center justify-between">
        <h2 class="text-xl sm:text-2xl font-bold text-eventbrite-dark">
          Popular events
        </h2>
        <div class="text-xs text-eventbrite-gray-600">
          {{ $events->total() }} events
        </div>
      </div>
    </div>

    {{-- Event list: Compact cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
      @forelse ($events as $event)
        @php
          $priceToShow = $event->price;
          if (!is_null($event->early_bird_price) && !is_null($event->early_bird_ends_at) && now()->lte($event->early_bird_ends_at)) {
            $priceToShow = $event->early_bird_price;
          }
          $start = $event->starts_at;
          $startDate = $start ? $start->format('D, M j') : null;
        @endphp
        <a href="{{ $event->public_url }}"
           class="flex items-center rounded-2xl border border-gray-100 bg-white hover:shadow-xl hover:border-gray-200 transition-all duration-300 overflow-hidden group h-32">
          {{-- Left: Image --}}
          <div class="w-24 sm:w-28 h-full relative bg-gray-50 shrink-0">
              @if($event->image_url)
                  <img src="{{ $event->image_url }}" alt="{{ $event->title }}"
                       class="absolute inset-0 h-full w-full object-cover group-hover:scale-110 transition-transform duration-700"/>
              @else
                  <div class="absolute inset-0 h-full w-full bg-gradient-to-br from-indigo-100 to-indigo-200"></div>
              @endif
              <div class="absolute inset-0 bg-black/5"></div>
          </div>
          {{-- Right: Details --}}
          <div class="flex-1 p-3 sm:p-4 flex flex-col justify-between min-w-0 h-full">
              <div>
                  <h3 class="text-sm sm:text-[15px] font-bold text-gray-900 line-clamp-2 transition-colors group-hover:text-tix-orange leading-snug">
                      {{ $event->title }}
                  </h3>
                  <div class="mt-1 text-[11px] text-gray-500 font-medium">
                      @if($startDate)
                          <div class="flex items-center gap-1.5">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-tix-orange" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                              </svg>
                              <span class="truncate">{{ $startDate }}</span>
                          </div>
                      @endif
                  </div>
              </div>
              <div>
                  <span class="text-[15px] font-black text-tix-orange">
                      @if(!is_null($priceToShow) && $priceToShow > 0)
                          ₦{{ number_format($priceToShow, 0) }}
                      @else
                          Free
                      @endif
                  </span>
              </div>
          </div>
        </a>
      @empty
        <div class="py-20 text-center col-span-full">
          @php
            $stateLabel = request('state_label', 'this area');
          @endphp
          <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto mb-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          <p class="text-xl font-bold text-gray-900 mb-2">Sorry, no events found</p>
          <p class="text-gray-500">There are currently no events in {{ $stateLabel }}. Check back soon!</p>
        </div>
      @endforelse
    </div>

    <div class="mt-8">
      {{ $events->links() }}
    </div>
  </div>
</section>
@endsection
