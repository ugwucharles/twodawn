<x-organizer-layout>
    <div class="max-w-4xl mx-auto py-8 px-6">
        <!-- Header -->
        <div class="mb-10">
            <h1 class="text-3xl font-black text-gray-900">Create New Event</h1>
            <p class="text-gray-500 text-sm mt-1">Fill in the details below to publish your event</p>
        </div>

        @if ($errors->any())
            <div class="mb-8 p-5 bg-red-50 text-red-700 rounded-2xl text-sm border border-red-200">
                <ul class="list-disc list-inside space-y-2">
                    @foreach ($errors->all() as $error)
                        <li class="font-medium">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('organizer.events.store') }}" enctype="multipart/form-data" class="space-y-8">
            @csrf

            <div class="bg-white rounded-2xl p-8 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-gray-100 space-y-6">
                <h2 class="text-base font-bold text-gray-900 pb-2 border-b border-gray-100">Event Details</h2>

                <div>
                    <label for="title" class="block text-sm font-semibold text-gray-700 mb-2">Event Title *</label>
                    <input id="title" type="text" name="title" value="{{ old('title') }}" required
                           class="block w-full rounded-xl border border-gray-200 bg-white text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 px-5 py-3.5 text-sm shadow-sm transition-all"
                           placeholder="e.g. Lagos Tech Fest 2026">
                </div>

                <div>
                    <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                    <textarea id="description" name="description" rows="5"
                              class="block w-full rounded-xl border border-gray-200 bg-white text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 px-5 py-3.5 text-sm shadow-sm transition-all resize-none"
                              placeholder="Tell people what this event is about...">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label for="must_know" class="block text-sm font-semibold text-gray-700 mb-2">
                        MUST KNOW!
                        <span class="text-xs font-normal text-gray-400 ml-1">Important info for attendees</span>
                    </label>
                    <textarea id="must_know" name="must_know" rows="4"
                              class="block w-full rounded-xl border border-amber-200 bg-amber-50/30 text-gray-900 focus:border-amber-400 focus:ring-2 focus:ring-amber-100 px-5 py-3.5 text-sm shadow-sm transition-all resize-none"
                              placeholder="e.g. Dress code, gate closing time, items not allowed...">{{ old('must_know') }}</textarea>
                </div>

                <div>
                    <label for="state" class="block text-sm font-semibold text-gray-700 mb-2">State *</label>
                    <select id="state" name="state" required
                            class="block w-full rounded-xl border border-gray-200 bg-white text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 px-5 py-3.5 text-sm shadow-sm transition-all">
                        <option value="">Select State</option>
                        <option value="abia" {{ old('state') == 'abia' ? 'selected' : '' }}>Abia</option>
                        <option value="adamawa" {{ old('state') == 'adamawa' ? 'selected' : '' }}>Adamawa</option>
                        <option value="akwa-ibom" {{ old('state') == 'akwa-ibom' ? 'selected' : '' }}>Akwa Ibom</option>
                        <option value="anambra" {{ old('state') == 'anambra' ? 'selected' : '' }}>Anambra</option>
                        <option value="bauchi" {{ old('state') == 'bauchi' ? 'selected' : '' }}>Bauchi</option>
                        <option value="bayelsa" {{ old('state') == 'bayelsa' ? 'selected' : '' }}>Bayelsa</option>
                        <option value="benue" {{ old('state') == 'benue' ? 'selected' : '' }}>Benue</option>
                        <option value="borno" {{ old('state') == 'borno' ? 'selected' : '' }}>Borno</option>
                        <option value="cross-river" {{ old('state') == 'cross-river' ? 'selected' : '' }}>Cross River</option>
                        <option value="delta" {{ old('state') == 'delta' ? 'selected' : '' }}>Delta</option>
                        <option value="ebonyi" {{ old('state') == 'ebonyi' ? 'selected' : '' }}>Ebonyi</option>
                        <option value="edo" {{ old('state') == 'edo' ? 'selected' : '' }}>Edo</option>
                        <option value="ekiti" {{ old('state') == 'ekiti' ? 'selected' : '' }}>Ekiti</option>
                        <option value="enugu" {{ old('state') == 'enugu' ? 'selected' : '' }}>Enugu</option>
                        <option value="gombe" {{ old('state') == 'gombe' ? 'selected' : '' }}>Gombe</option>
                        <option value="imo" {{ old('state') == 'imo' ? 'selected' : '' }}>Imo</option>
                        <option value="jigawa" {{ old('state') == 'jigawa' ? 'selected' : '' }}>Jigawa</option>
                        <option value="kaduna" {{ old('state') == 'kaduna' ? 'selected' : '' }}>Kaduna</option>
                        <option value="kano" {{ old('state') == 'kano' ? 'selected' : '' }}>Kano</option>
                        <option value="katsina" {{ old('state') == 'katsina' ? 'selected' : '' }}>Katsina</option>
                        <option value="kebbi" {{ old('state') == 'kebbi' ? 'selected' : '' }}>Kebbi</option>
                        <option value="kogi" {{ old('state') == 'kogi' ? 'selected' : '' }}>Kogi</option>
                        <option value="kwara" {{ old('state') == 'kwara' ? 'selected' : '' }}>Kwara</option>
                        <option value="lagos" {{ old('state', 'lagos') == 'lagos' ? 'selected' : '' }}>Lagos</option>
                        <option value="nasarawa" {{ old('state') == 'nasarawa' ? 'selected' : '' }}>Nasarawa</option>
                        <option value="niger" {{ old('state') == 'niger' ? 'selected' : '' }}>Niger</option>
                        <option value="ogun" {{ old('state') == 'ogun' ? 'selected' : '' }}>Ogun</option>
                        <option value="ondo" {{ old('state') == 'ondo' ? 'selected' : '' }}>Ondo</option>
                        <option value="osun" {{ old('state') == 'osun' ? 'selected' : '' }}>Osun</option>
                        <option value="oyo" {{ old('state') == 'oyo' ? 'selected' : '' }}>Oyo</option>
                        <option value="plateau" {{ old('state') == 'plateau' ? 'selected' : '' }}>Plateau</option>
                        <option value="rivers" {{ old('state') == 'rivers' ? 'selected' : '' }}>Rivers</option>
                        <option value="sokoto" {{ old('state') == 'sokoto' ? 'selected' : '' }}>Sokoto</option>
                        <option value="taraba" {{ old('state') == 'taraba' ? 'selected' : '' }}>Taraba</option>
                        <option value="yobe" {{ old('state') == 'yobe' ? 'selected' : '' }}>Yobe</option>
                        <option value="zamfara" {{ old('state') == 'zamfara' ? 'selected' : '' }}>Zamfara</option>
                        <option value="abuja" {{ old('state') == 'abuja' ? 'selected' : '' }}>Abuja (FCT)</option>
                    </select>
                </div>

                <div>
                    <label for="venue" class="block text-sm font-semibold text-gray-700 mb-2">Venue / Location *</label>
                    <input id="venue" type="text" name="venue" value="{{ old('venue') }}" required
                           class="block w-full rounded-xl border border-gray-200 bg-white text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 px-5 py-3.5 text-sm shadow-sm transition-all"
                           placeholder="e.g. Eko Hotel, Victoria Island">
                    <div class="mt-3 inline-block px-3 py-1.5 rounded-lg bg-red-50 text-red-700 text-xs font-bold uppercase tracking-wide">
                        Detailed Location on Ticket
                    </div>
                    <p class="text-xs text-gray-500 mt-2">This exact location will be printed on tickets and visible to attendees</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-8 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-gray-100 space-y-6">
                <h2 class="text-base font-bold text-gray-900 pb-2 border-b border-gray-100">Event Date & Media</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="starts_at" class="block text-sm font-semibold text-gray-700 mb-2">Event Date & Time *</label>
                        <input id="starts_at" type="datetime-local" name="starts_at" value="{{ old('starts_at') }}" required
                               class="block w-full rounded-xl border border-gray-200 bg-white text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 px-5 py-3.5 text-sm shadow-sm transition-all">
                    </div>
                    <div>
                        <label for="flyer" class="block text-sm font-semibold text-gray-700 mb-2">Event Flyer</label>
                        <input id="flyer" type="file" name="flyer" accept="image/*"
                               class="block w-full rounded-xl border border-gray-200 bg-white text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 px-5 py-3 text-sm shadow-sm transition-all file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="text-xs text-gray-500 mt-2">JPG, PNG or GIF (Max 2MB)</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-8 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-gray-100 space-y-6">
                <h2 class="text-base font-bold text-gray-900 pb-2 border-b border-gray-100">Tickets & Pricing</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="price" class="block text-sm font-semibold text-gray-700 mb-2">Ticket Price (₦)</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                                <span class="text-gray-500 text-sm font-medium">₦</span>
                            </div>
                            <input id="price" type="number" name="price" value="{{ old('price', 0) }}" min="0" step="100"
                                   class="block w-full rounded-xl border border-gray-200 bg-white text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 pl-10 pr-5 py-3.5 text-sm shadow-sm transition-all"
                                   placeholder="0">
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Set to 0 for a free event</p>
                    </div>
                    <div>
                        <label for="capacity" class="block text-sm font-semibold text-gray-700 mb-2">Capacity</label>
                        <input id="capacity" type="number" name="capacity" value="{{ old('capacity') }}" min="1"
                               class="block w-full rounded-xl border border-gray-200 bg-white text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 px-5 py-3.5 text-sm shadow-sm transition-all"
                               placeholder="500">
                        <p class="text-xs text-gray-500 mt-2">Leave blank for unlimited</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="free_tickets" class="block text-sm font-semibold text-gray-700 mb-2">First Free Tickets</label>
                        <input id="free_tickets" type="number" name="free_tickets" value="{{ old('free_tickets', 0) }}" min="0"
                               class="block w-full rounded-xl border border-gray-200 bg-white text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 px-5 py-3.5 text-sm shadow-sm transition-all"
                               placeholder="0">
                        <p class="text-xs text-gray-500 mt-2">Number of free tickets for early buyers</p>
                    </div>
                    <div>
                        <div class="flex items-start">
                            <input id="pass_fees_to_buyer" name="pass_fees_to_buyer" type="checkbox" value="1" {{ old('pass_fees_to_buyer') ? 'checked' : '' }}
                                   class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2 mt-0.5">
                            <div class="ml-3">
                                <label for="pass_fees_to_buyer" class="block text-sm font-semibold text-gray-700">
                                    Pass processing fees to buyers
                                </label>
                                <p class="text-xs text-gray-500 mt-1">10% + ₦100 Paystack charges per ticket</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex gap-5 pt-6">
                <a href="{{ route('organizer.dashboard') }}" class="flex-1 flex justify-center items-center py-4 px-6 border-2 border-gray-200 bg-white rounded-xl text-sm font-bold text-gray-700 hover:bg-gray-50 hover:border-gray-300 shadow-sm transition-all">
                    Cancel
                </a>
                <button type="submit" class="flex-1 flex justify-center items-center py-4 px-6 border-2 border-gray-900 rounded-xl shadow-sm text-sm font-bold text-gray-900 bg-white hover:bg-gray-50 focus:outline-none transition-all">
                    Publish Event
                </button>
            </div>
        </form>
    </div>
</x-organizer-layout>
