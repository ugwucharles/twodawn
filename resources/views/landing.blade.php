@extends('layouts.public')
@section('title', 'Discover events | ' . config('app.name', '2DAWN'))
@section('meta_description', 'Discover events and buy tickets in seconds.')
@section('canonical', route('home'))

@section('content')
<section
    class="relative bg-white pt-8 md:pt-10 pb-16"
    x-data="{
        showType:false,
        showPrice:false,
        showDate:false,
        showLocation:false,
        locationLabel: 'Lagos',
        typeLabel: 'All events'
    }"
>
    <div class="max-w-6xl md:max-w-7xl mx-auto px-4 md:px-6 lg:px-10">

        {{-- Top: location selector (Tix-style) --}}
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6 mb-8 relative">
            <div class="relative">
                <p class="text-xs font-semibold tracking-[0.18em] text-eventbrite-gray-400 uppercase">
                    Find an event in
                </p>
                <button type="button"
                        @click="showLocation = !showLocation; showType=false; showPrice=false; showDate=false"
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
                            @click="locationLabel='{{ $label }}'; showLocation=false; window.location='{{ route('events.index', ['state' => $code, 'state_label' => $label]) }}'">
                      {{ $label }}
                    </button>
                  @endforeach
                </div>
            </div>
        </div>

        @php $featured = $featuredEvents ?? collect(); @endphp

        {{-- Highlighted events horizontal scroller --}}
        @if($featured->count())
            <div class="mb-10">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg sm:text-xl font-semibold text-eventbrite-dark">
                        Highlighted events
                    </h2>
                    <a href="{{ route('events.index') }}"
                       class="text-sm font-medium text-tix-orange hover:text-eventbrite-orange transition-colors">
                        View all
                    </a>
                </div>
                <div class="overflow-x-auto pb-2 -mx-1">
                    <div class="flex gap-4 min-w-max px-1">
                        @foreach($featured as $event)
                            <a href="{{ $event->public_url }}"
                               class="relative w-[240px] sm:w-[260px] md:w-[280px] rounded-[24px] bg-black text-white overflow-hidden shrink-0 group">
                                <div class="relative h-[320px] sm:h-[340px]">
                                    @if($event->image_url)
                                        <img src="{{ $event->image_url }}" alt="{{ $event->title }}"
                                             class="absolute inset-0 h-full w-full object-cover group-hover:scale-[1.03] transition-transform duration-500 ease-out"/>
                                    @else
                                        <div class="absolute inset-0 h-full w-full bg-gradient-to-br from-indigo-500 via-fuchsia-500 to-rose-500"></div>
                                    @endif
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent"></div>
                                    <div class="absolute inset-x-0 bottom-0 p-4">
                                        <div class="text-[11px] font-semibold uppercase tracking-[0.16em] text-white/70 mb-1">
                                            Highlighted event
                                        </div>
                                        <h3 class="text-sm sm:text-base font-semibold line-clamp-2">
                                            {{ $event->title }}
                                        </h3>
                                        <div class="mt-2 text-xs text-white/80 flex flex-wrap gap-x-3 gap-y-1">
                                            @php
                                                $start = $event->starts_at;
                                                $startDate = $start ? $start->format('D, M j') : null;
                                                $startTime = $start ? $start->format('g:i A') : null;
                                            @endphp
                                            @if($startDate)
                                                <span class="inline-flex items-center gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5"
                                                         viewBox="0 0 20 20" fill="currentColor">
                                                        <path d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1z"/>
                                                        <path d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9z"/>
                                                    </svg>
                                                    <span>{{ $startDate }}</span>
                                                </span>
                                            @endif
                                            @if($startTime)
                                                <span class="inline-flex items-center gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5"
                                                         viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd"
                                                              d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-11a.75.75 0 00-1.5 0v4.25c0 .414.336.75.75.75H13a.75.75 0 000-1.5h-2.25V7z"
                                                              clip-rule="evenodd"/>
                                                    </svg>
                                                    <span>{{ $startTime }}</span>
                                                </span>
                                            @endif
                                            @if($event->venue)
                                                <span class="inline-flex items-center gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5"
                                                         viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd"
                                                              d="M10 2a6 6 0 00-6 6c0 4.418 6 10 6 10s6-5.582 6-10a6 6 0 00-6-6zm0 8a2 2 0 110-4 2 2 0 010 4z"
                                                              clip-rule="evenodd"/>
                                                    </svg>
                                                    <span class="truncate max-w-[120px]">{{ $event->venue }}</span>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- Filters row + Popular events heading --}}
        <div class="flex flex-col gap-4 mb-6">
            <div class="flex flex-wrap items-center gap-3 relative">
                <div class="relative">
                    <button type="button"
                            @click="showType = !showType; showPrice=false; showDate=false"
                            class="inline-flex items-center px-4 py-2 rounded-full border border-eventbrite-gray-100 bg-white text-sm font-medium text-eventbrite-dark hover:border-eventbrite-gray-400 transition">
                        <span x-text="typeLabel">All events</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2 text-eventbrite-gray-400" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-cloak x-show="showType"
                         x-transition
                         @click.outside="showType=false"
                         class="absolute z-20 mt-2 w-44 rounded-xl border border-eventbrite-gray-100 bg-white shadow-lg py-2 text-sm text-eventbrite-dark">
                        <button type="button"
                                class="w-full text-left px-4 py-1.5 hover:bg-eventbrite-gray-50"
                                @click="typeLabel='All events'; showType=false; window.location='{{ route('events.index') }}'">
                            All events
                        </button>
                        <button type="button"
                                class="w-full text-left px-4 py-1.5 hover:bg-eventbrite-gray-50"
                                @click="typeLabel='Online events'; showType=false; window.location='{{ route('events.index', ['online' => 1]) }}'">
                            Online events
                        </button>
                        <button type="button"
                                class="w-full text-left px-4 py-1.5 hover:bg-eventbrite-gray-50"
                                @click="typeLabel='In-person events'; showType=false; window.location='{{ route('events.index', ['online' => 0]) }}'">
                            In‑person events
                        </button>
                    </div>
                </div>
                <div class="relative">
                    <button type="button"
                            @click="showPrice = !showPrice; showDate = false"
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
                                @click="showPrice=false; window.location='{{ route('events.index') }}'">
                            Any price
                        </button>
                        <button type="button"
                                class="w-full text-left px-4 py-1.5 hover:bg-eventbrite-gray-50"
                                @click="showPrice=false; window.location='{{ route('events.index', ['price' => 'free']) }}'">
                            Free
                        </button>
                        <button type="button"
                                class="w-full text-left px-4 py-1.5 hover:bg-eventbrite-gray-50"
                                @click="showPrice=false; window.location='{{ route('events.index', ['price' => 'paid']) }}'">
                            Paid
                        </button>
                    </div>
                </div>
                <div class="relative">
                    <button type="button"
                            @click="showDate = !showDate; showPrice = false"
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
                                @click="showDate=false; window.location='{{ route('events.index') }}'">
                            Any date
                        </button>
                        <button type="button"
                                class="w-full text-left px-4 py-1.5 hover:bg-eventbrite-gray-50"
                                @click="showDate=false; window.location='{{ route('events.index', ['date' => 'today']) }}'">
                            Today
                        </button>
                        <button type="button"
                                class="w-full text-left px-4 py-1.5 hover:bg-eventbrite-gray-50"
                                @click="showDate=false; window.location='{{ route('events.index', ['date' => 'weekend']) }}'">
                            This weekend
                        </button>
                        <button type="button"
                                class="w-full text-left px-4 py-1.5 hover:bg-eventbrite-gray-50"
                                @click="showDate=false; window.location='{{ route('events.index', ['date' => 'next-week']) }}'">
                            Next week
                        </button>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-between">
                <h2 class="text-2xl sm:text-3xl font-bold text-eventbrite-dark">
                    Popular events
                </h2>
                <a href="{{ route('events.index') }}"
                   class="text-sm font-medium text-tix-orange hover:text-eventbrite-orange transition-colors">
                    See all
                </a>
            </div>
        </div>

        {{-- Popular events list (Compact Cards) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @forelse($featured as $event)
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
                <div class="py-20 text-center text-gray-400 col-span-full">
                    No matching events found.
                </div>
            @endforelse
        </div>

        {{-- Recent events (past events) --}}
        @if(isset($recentEvents) && $recentEvents->count())
            <div class="mt-12 border-t border-eventbrite-gray-100 pt-8">
                <h2 class="text-xl sm:text-2xl font-bold text-eventbrite-dark mb-4">
                    Recent events
                </h2>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($recentEvents as $event)
                        @php
                            $start = $event->starts_at;
                            $startDate = $start ? $start->format('D, M j') : null;
                        @endphp
                        <a href="{{ $event->public_url }}"
                           class="flex rounded-2xl border border-eventbrite-gray-100 bg-white hover:border-eventbrite-gray-400 hover:shadow-md transition overflow-hidden group">
                            <div class="w-20 sm:w-24 relative bg-eventbrite-gray-50">
                                @if($event->image_url)
                                    <img src="{{ $event->image_url }}" alt="{{ $event->title }}"
                                         class="absolute inset-0 h-full w-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out"/>
                                @else
                                    <div class="absolute inset-0 h-full w-full bg-gradient-to-br from-gray-400 via-gray-500 to-gray-600"></div>
                                @endif
                            </div>
                            <div class="flex-1 p-3 sm:p-4">
                                <h3 class="text-sm font-semibold text-eventbrite-dark group-hover:text-eventbrite-orange line-clamp-2">
                                    {{ $event->title }}
                                </h3>
                                @if($startDate)
                                    <div class="mt-1 text-xs text-eventbrite-gray-600">
                                        {{ $startDate }}
                                    </div>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Other events you may like (upcoming events) --}}
        @if(isset($otherEvents) && $otherEvents->count())
            <div class="mt-12 border-t border-eventbrite-gray-100 pt-8">
                <h2 class="text-xl sm:text-2xl font-bold text-eventbrite-dark mb-4">
                    Other events you may like
                </h2>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($otherEvents as $event)
                        @php
                            $start = $event->starts_at;
                            $startDate = $start ? $start->format('D, M j') : null;
                        @endphp
                        <a href="{{ $event->public_url }}"
                           class="flex rounded-2xl border border-eventbrite-gray-100 bg-white hover:border-eventbrite-gray-400 hover:shadow-md transition overflow-hidden group">
                            <div class="w-20 sm:w-24 relative bg-eventbrite-gray-50">
                                @if($event->image_url)
                                    <img src="{{ $event->image_url }}" alt="{{ $event->title }}"
                                         class="absolute inset-0 h-full w-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out"/>
                                @else
                                    <div class="absolute inset-0 h-full w-full bg-gradient-to-br from-indigo-500 via-fuchsia-500 to-rose-500"></div>
                                @endif
                            </div>
                            <div class="flex-1 p-3 sm:p-4">
                                <h3 class="text-sm font-semibold text-eventbrite-dark group-hover:text-eventbrite-orange line-clamp-2">
                                    {{ $event->title }}
                                </h3>
                                @if($startDate)
                                    <div class="mt-1 text-xs text-eventbrite-gray-600">
                                        {{ $startDate }}
                                    </div>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</section>
@endsection

