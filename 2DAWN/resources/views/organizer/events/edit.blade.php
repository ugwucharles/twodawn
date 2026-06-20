<x-organizer-layout>
    <div class="max-w-4xl mx-auto py-8 px-6">
        <!-- Header -->
        <div class="mb-10 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-black text-gray-900">Edit Event</h1>
                <p class="text-gray-500 text-sm mt-1">Update the details for "{{ $event->title }}"</p>
            </div>
            <a href="{{ route('organizer.events.show', $event) }}" class="text-sm font-bold text-gray-500 hover:text-gray-700">
                Back to Details
            </a>
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

        <form method="POST" action="{{ route('organizer.events.update', $event) }}" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @method('PATCH')

            <div class="bg-white rounded-2xl p-8 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-gray-100 space-y-6">
                <h2 class="text-base font-bold text-gray-900 pb-2 border-b border-gray-100">Event Details</h2>

                <div>
                    <label for="title" class="block text-sm font-semibold text-gray-700 mb-2">Event Title *</label>
                    <input id="title" type="text" name="title" value="{{ old('title', $event->title) }}" required
                           class="block w-full rounded-xl border border-gray-200 bg-white text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 px-5 py-3.5 text-sm shadow-sm transition-all"
                           placeholder="e.g. Lagos Tech Fest 2026">
                </div>

                <div>
                    <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                    <textarea id="description" name="description" rows="5"
                              class="block w-full rounded-xl border border-gray-200 bg-white text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 px-5 py-3.5 text-sm shadow-sm transition-all resize-none"
                              placeholder="Tell people what this event is about...">{{ old('description', $event->description) }}</textarea>
                </div>

                <div>
                    <label for="must_know" class="block text-sm font-semibold text-gray-700 mb-2">
                        MUST KNOW!
                        <span class="text-xs font-normal text-gray-400 ml-1">Important info for attendees</span>
                    </label>
                    <textarea id="must_know" name="must_know" rows="4"
                              class="block w-full rounded-xl border border-purple-200 bg-purple-50/30 text-gray-900 focus:border-purple-400 focus:ring-2 focus:ring-purple-100 px-5 py-3.5 text-sm shadow-sm transition-all resize-none"
                              placeholder="e.g. Dress code, gate closing time, items not allowed...">{{ old('must_know', $event->must_know) }}</textarea>
                </div>

                <div>
                    <label for="state" class="block text-sm font-semibold text-gray-700 mb-2">State *</label>
                    <select id="state" name="state" required
                            class="block w-full rounded-xl border border-gray-200 bg-white text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-100 px-5 py-3.5 text-sm shadow-sm transition-all">
                        <option value="">Select State</option>
                        @php
                            $states = [
                                'abia' => 'Abia', 'adamawa' => 'Adamawa', 'akwa-ibom' => 'Akwa Ibom', 'anambra' => 'Anambra', 
                                'bauchi' => 'Bauchi', 'bayelsa' => 'Bayelsa', 'benue' => 'Benue', 'borno' => 'Borno', 
                                'cross-river' => 'Cross River', 'delta' => 'Delta', 'ebonyi' => 'Ebonyi', 'edo' => 'Edo', 
                                'ekiti' => 'Ekiti', 'enugu' => 'Enugu', 'gombe' => 'Gombe', 'imo' => 'Imo', 
                                'jigawa' => 'Jigawa', 'kaduna' => 'Kaduna', 'kano' => 'Kano', 'katsina' => 'Katsina', 
                                'kebbi' => 'Kebbi', 'kogi' => 'Kogi', 'kwara' => 'Kwara', 'lagos' => 'Lagos', 
                                'nasarawa' => 'Nasarawa', 'niger' => 'Niger', 'ogun' => 'Ogun', 'ondo' => 'Ondo', 
                                'osun' => 'Osun', 'oyo' => 'Oyo', 'plateau' => 'Plateau', 'rivers' => 'Rivers', 
                                'sokoto' => 'Sokoto', 'taraba' => 'Taraba', 'yobe' => 'Yobe', 'zamfara' => 'Zamfara', 
                                'abuja' => 'Abuja (FCT)'
                            ];
                        @endphp
                        @foreach($states as $val => $label)
                            <option value="{{ $val }}" {{ old('state', $event->state) == $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="venue" class="block text-sm font-semibold text-gray-700 mb-2">Venue / Location *</label>
                    <input id="venue" type="text" name="venue" value="{{ old('venue', $event->venue) }}" required
                           class="block w-full rounded-xl border border-gray-200 bg-white text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 px-5 py-3.5 text-sm shadow-sm transition-all"
                           placeholder="e.g. Eko Hotel, Victoria Island">
                </div>
            </div>
<div class="bg-white rounded-2xl p-8 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-gray-100 space-y-6">
    <h2 class="text-base font-bold text-gray-900 pb-2 border-b border-gray-100">Event Flyer</h2>
    @if ($event->image_url)
        <div class="mb-4">
            <img src="{{ $event->image_url }}" alt="Current flyer" class="max-w-full h-auto rounded-lg"/>
        </div>
    @endif
    <input type="file" name="image" accept="image/*" class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"/>
    <p class="text-xs text-gray-500 mt-1">Upload a new flyer to replace the existing one</p>
</div>

            <div class="bg-white rounded-2xl p-8 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-gray-100 space-y-6">
                <h2 class="text-base font-bold text-gray-900 pb-2 border-b border-gray-100">Event Date & Media</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="starts_at" class="block text-sm font-semibold text-gray-700 mb-2">Event Date & Time *</label>
                        <input id="starts_at" type="datetime-local" name="starts_at" value="{{ old('starts_at', $event->starts_at?->format('Y-m-d\TH:i')) }}" required
                               class="block w-full rounded-xl border border-gray-200 bg-white text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 px-5 py-3.5 text-sm shadow-sm transition-all">
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
                            <input id="price" type="number" name="price" value="{{ old('price', $event->price) }}" min="0" step="100"
                                   class="block w-full rounded-xl border border-gray-200 bg-white text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 pl-10 pr-5 py-3.5 text-sm shadow-sm transition-all"
                                   placeholder="0">
                        </div>
                    </div>
                    <div>
                        <label for="capacity" class="block text-sm font-semibold text-gray-700 mb-2">Capacity</label>
                        <input id="capacity" type="number" name="capacity" value="{{ old('capacity', $event->capacity) }}" min="1"
                               class="block w-full rounded-xl border border-gray-200 bg-white text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 px-5 py-3.5 text-sm shadow-sm transition-all"
                               placeholder="500">
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-100">
                    <label class="inline-flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" 
                               name="pass_fees_to_buyer" 
                               value="1" 
                               @checked(old('pass_fees_to_buyer', $event->pass_fees_to_buyer))
                               class="mt-1 w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                        <div>
                            <span class="block text-sm font-semibold text-gray-700">Pass ticket fee to buyers</span>
                            <span class="block text-xs text-gray-500 mt-1">If enabled, the 10% platform service fee will be added to the ticket price paid by the customer. Otherwise, the fee will be deducted from your payout.</span>
                        </div>
                    </label>
                </div>
            </div>

            <div class="flex gap-5 pt-6">
                <a href="{{ route('organizer.events.show', $event) }}" class="flex-1 flex justify-center items-center py-4 px-6 border-2 border-gray-200 bg-white rounded-xl text-sm font-bold text-gray-700 hover:bg-gray-50 hover:border-gray-300 shadow-sm transition-all">
                    Cancel
                </a>
                <button type="submit" class="flex-1 flex justify-center items-center py-4 px-6 border-2 border-gray-900 rounded-xl shadow-sm text-sm font-bold text-gray-900 bg-white hover:bg-gray-50 focus:outline-none transition-all">
                    Update Event
                </button>
            </div>
        </form>
    </div>
</x-organizer-layout>
