@extends('layouts.public')
@section('title', 'Feature coming soon | ' . config('app.name', '2DAWN'))

@section('content')
<section class="min-h-[60vh] flex items-center justify-center px-4 py-20 bg-white">
    <div class="max-w-xl w-full text-center">
        {{-- Icon/Illustration Placeholder --}}
        <div class="mb-8 flex justify-center">
            <div class="h-24 w-24 bg-tix-orange/10 rounded-full flex items-center justify-center text-tix-orange animate-pulse">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
            </div>
        </div>

        <h1 class="text-3xl md:text-4xl font-black text-eventbrite-dark mb-4">
            We're building this!
        </h1>
        
        <p class="text-lg text-eventbrite-gray-600 mb-10 leading-relaxed">
            We are currently refining the event creation experience to ensure it's as seamless as possible. Check back soon or follow us for updates!
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ route('home') }}" class="w-full sm:w-auto px-8 py-3 bg-eventbrite-dark text-white font-bold rounded-full hover:bg-black transition shadow-lg">
                Back to home
            </a>
            <a href="{{ route('events.index') }}" class="w-full sm:w-auto px-8 py-3 border-2 border-eventbrite-dark text-eventbrite-dark font-bold rounded-full hover:bg-eventbrite-gray-50 transition">
                Discover events
            </a>
        </div>
        
        <div class="mt-16 pt-8 border-t border-eventbrite-gray-100">
            <p class="text-sm text-eventbrite-gray-400">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </p>
        </div>
    </div>
</section>
@endsection
