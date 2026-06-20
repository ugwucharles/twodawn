<x-organizer-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-black text-gray-900 tracking-tight">Events</h1>
                <p class="text-gray-500 mt-1 font-medium">Manage your events</p>
            </div>
            <a href="{{ route('organizer.events.create') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl shadow-lg shadow-blue-200 transition-all">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Create Event
            </a>
        </div>

        <!-- Events List -->
        <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100/50 overflow-hidden">
            @forelse($events as $event)
                <div class="p-6 border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                    <div class="flex items-center gap-6 overflow-x-auto custom-scrollbar pb-2">
                        <!-- Event Image -->
                        @if($event->image_url)
                            <img src="{{ $event->image_url }}" alt="{{ $event->title }}" class="w-20 h-20 rounded-2xl object-cover shrink-0">
                        @else
                            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-indigo-100 to-indigo-200 shrink-0"></div>
                        @endif
                        
                        <!-- Event Details -->
                        <div class="flex-1 min-w-[200px] shrink-0">
                            <h3 class="text-lg font-bold text-gray-900 truncate">{{ $event->title }}</h3>
                            <p class="text-sm text-gray-500 mt-1">
                                @if($event->starts_at)
                                    {{ $event->starts_at->format('M j, Y - g:i A') }}
                                @endif
                            </p>
                            <p class="text-sm text-gray-500 mt-1">{{ $event->venue }}</p>
                        </div>
                        
                        <!-- Stats -->
                        <div class="flex items-center gap-6 shrink-0">
                            <div class="text-center">
                                <p class="text-2xl font-black text-gray-900">{{ $event->orders()->where('status', 'paid')->sum('quantity') }}</p>
                                <p class="text-xs text-gray-500 font-medium">Tickets Sold</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-black text-gray-900">₦{{ number_format($event->orders()->where('status', 'paid')->sum('amount'), 0) }}</p>
                                <p class="text-xs text-gray-500 font-medium">Revenue</p>
                            </div>
                        </div>
                        
                        <!-- Actions -->
                        <div class="flex items-center gap-2 shrink-0">
                            <a href="{{ route('organizer.events.show', $event) }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition-colors whitespace-nowrap">
                                View
                            </a>
                            <a href="{{ route('organizer.events.edit', $event) }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition-colors whitespace-nowrap">
                                Edit
                            </a>
                            <form action="{{ route('organizer.events.destroy', $event) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this event? This action cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl transition-colors whitespace-nowrap">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-20 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto mb-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="text-xl font-bold text-gray-900 mb-2">No events yet</p>
                    <p class="text-gray-500 mb-6">Create your first event to get started</p>
                    <a href="{{ route('organizer.events.create') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl shadow-lg shadow-blue-200 transition-all">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Create Event
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</x-organizer-layout>
