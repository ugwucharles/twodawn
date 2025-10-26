@extends('layouts.public')

@section('content')
<section class="relative py-12 sm:py-16">
  <div class="max-w-7xl mx-auto px-6 mb-6 flex justify-between">
    <a href="{{ url('/') }}" class="inline-flex items-center px-4 py-2 rounded-full bg-white text-black text-sm font-semibold hover:bg-zinc-100 transition">Home</a>
    <a href="{{ route('events.index') }}" class="inline-flex items-center px-4 py-2 rounded-full bg-white/10 ring-1 ring-white/15 text-white text-sm hover:bg-white/15 transition">All Events</a>
  </div>
  <style>
    /* Responsive card aspect for recent page */
    .card-aspect{position:relative;padding-top:calc(62.5% + 60px);} /* 16:10 + 60px */
    @media (min-width:640px){.card-aspect{padding-top:calc(62.5% + 80px);} }
    @media (min-width:1024px){.card-aspect{padding-top:calc(62.5% + 120px);} }
    .card-aspect > *{position:absolute;inset:0;}
  </style>
  <div class="absolute inset-0 -z-10">
    <div class="absolute -top-48 -left-32 h-[40rem] w-[40rem] rounded-full blur-3xl opacity-30 bg-gradient-to-br from-indigo-500 via-fuchsia-500 to-rose-500"></div>
    <div class="absolute -bottom-48 -right-32 h-[40rem] w-[40rem] rounded-full blur-3xl opacity-20 bg-gradient-to-tr from-sky-500 to-emerald-400"></div>
  </div>
  <div class="max-w-7xl mx-auto px-6">
    <div class="mb-8 flex items-end justify-between">
      <div>
        <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight">Recent Events</h1>
        <p class="mt-2 text-zinc-400">Events from the past 30 days</p>
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
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
          <div class="card-aspect">
            @if($event->image_path)
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

    <div class="mt-10">{{ $recentEvents->links() }}</div>
  </div>
</section>
@endsection
