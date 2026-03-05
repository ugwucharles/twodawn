<x-organizer-layout>
    <div class="max-w-4xl mx-auto py-8 px-6">
        <section class="bg-white min-h-[60vh] flex items-center justify-center">
            <div class="text-center px-6 max-w-lg mx-auto">
                <div class="w-20 h-20 mx-auto mb-8 rounded-full bg-gray-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <h1 class="text-3xl sm:text-4xl font-black text-gray-900 mb-4">Coming Soon</h1>
                <p class="text-gray-500 text-lg font-medium mb-8">Event creation for organizers is launching soon. Stay tuned!</p>
                <a href="{{ route('organizer.dashboard') }}" class="inline-flex items-center px-8 py-3.5 rounded-xl bg-black text-white font-bold hover:bg-gray-800 transition-colors">
                    Back to Dashboard
                </a>
            </div>
        </section>
    </div>
</x-organizer-layout>
