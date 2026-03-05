@extends('layouts.public')

@section('title', 'Find My Tickets | ' . config('app.name', '2DAWN'))

@section('content')
<section class="bg-white min-h-[70vh] flex items-center justify-center">
  <div class="text-center px-6 max-w-lg mx-auto">
    <div class="w-20 h-20 mx-auto mb-8 rounded-full bg-gray-100 flex items-center justify-center">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
      </svg>
    </div>
    <h1 class="text-3xl sm:text-4xl font-black text-gray-900 mb-4">Coming Soon</h1>
    <p class="text-gray-500 text-lg font-medium mb-8">We're working on something exciting. This feature will be available soon.</p>
    <a href="{{ route('events.index') }}" class="inline-flex items-center px-8 py-3.5 rounded-xl bg-black text-white font-bold hover:bg-gray-800 transition-colors">
      Browse Events
    </a>
  </div>
</section>
@endsection
