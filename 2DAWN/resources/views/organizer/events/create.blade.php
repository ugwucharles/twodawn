<x-organizer-layout>
    <div class="max-w-4xl mx-auto py-8 px-6">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('organizer.dashboard') }}" class="inline-flex items-center text-sm font-bold text-gray-500 hover:text-gray-900 mb-4">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Dashboard
            </a>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Create Event</h1>
            <p class="text-gray-500 mt-1 font-medium">Fill in the details to create your new event</p>
        </div>

        <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100/50 p-8">
            <form method="POST" action="{{ route('organizer.events.store') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                
                @if ($errors->any())
                    <div class="bg-red-50 border border-red-200 rounded-2xl p-4">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <div class="text-sm font-bold text-red-800">
                                {{ $errors->first() }}
                            </div>
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Event Title *</label>
                        <input type="text" 
                               name="title" 
                               value="{{ old('title') }}" 
                               class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
                               placeholder="Enter event title"
                               required autofocus>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Description</label>
                        <textarea name="description" 
                                  rows="4"
                                  class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
                                  placeholder="Describe your event">{{ old('description') }}</textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-2">MUST KNOW! (Important Info)</label>
                        <textarea name="must_know" 
                                  rows="3"
                                  class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
                                  placeholder="Important info for attendees (dress code, gate times, etc.)">{{ old('must_know') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Venue *</label>
                        <input type="text" 
                               name="venue" 
                               value="{{ old('venue') }}" 
                               class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
                               placeholder="Event venue"
                               required>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">State *</label>
                        <select name="state" 
                                required
                                class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                            <option value="" disabled {{ old('state') ? '' : 'selected' }}>Select state</option>
                            @php
                              $ngStates = [
                                'abia' => 'Abia', 'adamawa' => 'Adamawa', 'akwa-ibom' => 'Akwa Ibom', 'anambra' => 'Anambra',
                                'bauchi' => 'Bauchi', 'bayelsa' => 'Bayelsa', 'benue' => 'Benue', 'borno' => 'Borno',
                                'cross-river' => 'Cross River', 'delta' => 'Delta', 'ebonyi' => 'Ebonyi', 'edo' => 'Edo',
                                'ekiti' => 'Ekiti', 'enugu' => 'Enugu', 'gombe' => 'Gombe', 'imo' => 'Imo',
                                'jigawa' => 'Jigawa', 'kaduna' => 'Kaduna', 'kano' => 'Kano', 'katsina' => 'Katsina',
                                'kebbi' => 'Kebbi', 'kogi' => 'Kogi', 'kwara' => 'Kwara', 'lagos' => 'Lagos',
                                'nasarawa' => 'Nasarawa', 'niger' => 'Niger', 'ogun' => 'Ogun', 'ondo' => 'Ondo',
                                'osun' => 'Osun', 'oyo' => 'Oyo', 'plateau' => 'Plateau', 'rivers' => 'Rivers',
                                'sokoto' => 'Sokoto', 'taraba' => 'Taraba', 'yobe' => 'Yobe', 'zamfara' => 'Zamfara',
                                'abuja' => 'Abuja (FCT)',
                              ];
                            @endphp
                            @foreach($ngStates as $code => $label)
                                <option value="{{ $code }}" @selected(old('state') === $code)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Start Date & Time *</label>
                        <input type="datetime-local" 
                               name="starts_at" 
                               value="{{ old('starts_at') }}" 
                               class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
                               required>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">End Date & Time</label>
                        <input type="datetime-local" 
                               name="ends_at" 
                               value="{{ old('ends_at') }}" 
                               class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Price (₦)</label>
                        <input type="number" 
                               name="price" 
                               step="0.01" 
                               min="0" 
                               value="{{ old('price', 0) }}" 
                               class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
                               placeholder="0 for free event">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Capacity</label>
                        <input type="number" 
                               name="capacity" 
                               step="1" 
                               min="1" 
                               value="{{ old('capacity') }}" 
                               class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
                               placeholder="Maximum attendees">
                    </div>

                    <div class="md:col-span-2">
                        <label class="inline-flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" 
                                   name="pass_fees_to_buyer" 
                                   value="1" 
                                   @checked(old('pass_fees_to_buyer'))
                                   class="mt-1 w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                            <div>
                                <span class="block text-sm font-bold text-gray-700">Pass ticket fee to buyers</span>
                                <span class="block text-xs text-gray-500 mt-1">If enabled, the 10% platform service fee will be added to the ticket price paid by the customer. Otherwise, the fee will be deducted from your payout.</span>
                            </div>
                        </label>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Ticket Types (Optional)</label>
                        <p class="text-xs text-gray-500 mb-3">Add multiple ticket tiers (e.g. Regular, VIP). If added, these override the base price above.</p>
                        
                        <div id="ticket-types-container" class="space-y-3">
                            <!-- Ticket types will be added here dynamically -->
                        </div>
                        
                        <button type="button" 
                                id="add-ticket-type-btn" 
                                class="mt-3 px-4 py-2 bg-gray-100 text-gray-700 rounded-xl text-sm font-bold hover:bg-gray-200 transition-colors">
                            + Add Ticket Type
                        </button>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Event Flyer</label>
                        <input type="file" 
                               name="image" 
                               accept="image/*"
                               class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                        <p class="text-xs text-gray-500 mt-1">Upload an attractive flyer for your event</p>
                    </div>
                </div>

                <div class="flex items-center gap-4 pt-6 border-t border-gray-100">
                    <button type="submit" 
                            class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl shadow-lg shadow-blue-200 transition-all">
                        Create Event
                    </button>
                    <a href="{{ route('organizer.dashboard') }}" 
                       class="px-8 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-2xl transition-colors">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('ticket-types-container');
            const addBtn = document.getElementById('add-ticket-type-btn');
            let counter = 0;
            
            addBtn.addEventListener('click', function() {
                const row = document.createElement('div');
                row.className = 'flex items-center gap-3 ticket-type-row';
                row.innerHTML = `
                    <div class="flex-1">
                        <input type="text" name="ticket_types[${counter}][name]" placeholder="Ticket Name (e.g. VIP)" class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all" required>
                    </div>
                    <div class="flex-1">
                        <input type="number" name="ticket_types[${counter}][price]" step="0.01" min="0" placeholder="Price (₦)" class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all" required>
                    </div>
                    <button type="button" class="p-2 text-red-500 hover:text-red-700 remove-ticket-type-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </button>
                `;
                container.appendChild(row);
                counter++;
            });
            
            container.addEventListener('click', function(e) {
                const btn = e.target.closest('.remove-ticket-type-btn');
                if (btn) {
                    btn.closest('.ticket-type-row').remove();
                }
            });
        });
    </script>
</x-organizer-layout>
