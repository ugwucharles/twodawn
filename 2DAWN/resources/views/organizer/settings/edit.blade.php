<x-organizer-layout>
    <div class="max-w-4xl mx-auto py-8 px-6">
        <!-- Header -->
        <div class="mb-10">
            <h1 class="text-3xl font-black text-gray-900">Settings</h1>
            <p class="text-gray-500 text-sm mt-1">Manage your social media links</p>
        </div>

        @if (session('status'))
            <div class="mb-8 p-5 bg-green-50 text-green-700 rounded-2xl text-sm border border-green-200 font-medium">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-8 p-5 bg-red-50 text-red-700 rounded-2xl text-sm border border-red-200">
                <ul class="list-disc list-inside space-y-2">
                    @foreach ($errors->all() as $error)
                        <li class="font-medium">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('organizer.settings.update') }}" class="space-y-8">
            @csrf
            @method('PATCH')

            <div class="bg-white rounded-2xl p-8 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-gray-100 space-y-6">
                <h2 class="text-base font-bold text-gray-900 pb-2 border-b border-gray-100">Social Media Links</h2>
                
                <p class="text-sm text-gray-600">
                    These links will be displayed on your event pages so attendees can contact you.
                </p>

                <div>
                    <label for="instagram_handle" class="block text-sm font-semibold text-gray-700 mb-2">
                        <svg class="w-5 h-5 inline-block mr-2" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                        </svg>
                        Instagram Handle
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-gray-500 text-sm">@</span>
                        <input id="instagram_handle" type="text" name="instagram_handle" 
                               value="{{ old('instagram_handle', Auth::user()->instagram_handle) }}"
                               class="block w-full rounded-xl border border-gray-200 bg-white text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 pl-10 pr-5 py-3.5 text-sm shadow-sm transition-all"
                               placeholder="username">
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Enter your Instagram username without the @ symbol</p>
                </div>

                <div>
                    <label for="whatsapp_number" class="block text-sm font-semibold text-gray-700 mb-2">
                        <svg class="w-5 h-5 inline-block mr-2" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.414 0 0 5.414 0 12.05c0 2.123.551 4.197 1.595 6.02L0 24l6.135-1.61a11.751 11.751 0 005.91 1.611h.005c6.637 0 12.05-5.414 12.05-12.05a11.815 11.815 0 00-3.487-8.522z"/>
                        </svg>
                        WhatsApp Number
                    </label>
                    <input id="whatsapp_number" type="text" name="whatsapp_number" 
                           value="{{ old('whatsapp_number', Auth::user()->whatsapp_number) }}"
                           class="block w-full rounded-xl border border-gray-200 bg-white text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 px-5 py-3.5 text-sm shadow-sm transition-all"
                           placeholder="e.g. 2348012345678">
                    <p class="text-xs text-gray-500 mt-2">Enter with country code (e.g., 234 for Nigeria)</p>
                </div>

                <div>
                    <label for="twitter_handle" class="block text-sm font-semibold text-gray-700 mb-2">
                        <svg class="w-5 h-5 inline-block mr-2" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.84 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                        </svg>
                        Twitter/X Handle
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-gray-500 text-sm">@</span>
                        <input id="twitter_handle" type="text" name="twitter_handle" 
                               value="{{ old('twitter_handle', Auth::user()->twitter_handle) }}"
                               class="block w-full rounded-xl border border-gray-200 bg-white text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 pl-10 pr-5 py-3.5 text-sm shadow-sm transition-all"
                               placeholder="username">
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Enter your Twitter/X username without the @ symbol</p>
                </div>
            </div>

            <div class="flex gap-5 pt-6">
                <a href="{{ route('organizer.dashboard') }}" 
                   class="flex-1 flex justify-center items-center py-4 px-6 border-2 border-gray-200 bg-white rounded-xl text-sm font-bold text-gray-700 hover:bg-gray-50 hover:border-gray-300 shadow-sm transition-all">
                    Cancel
                </a>
                <button type="submit" 
                        class="flex-1 flex justify-center items-center py-4 px-6 border-2 border-blue-500 bg-blue-500 rounded-xl shadow-sm text-sm font-bold text-white hover:bg-blue-600 hover:border-blue-600 focus:outline-none transition-all">
                    Save Settings
                </button>
            </div>
        </form>
    </div>
</x-organizer-layout>
