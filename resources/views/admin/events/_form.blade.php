<div class="space-y-4">
    @if ($errors->has('general'))
        <x-alert type="error">{{ $errors->first('general') }}</x-alert>
    @endif
    
    <div>
        <x-input-label for="title" :value="__('Title')" />
        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title', $event->title)" required autofocus />
        <x-input-error :messages="$errors->get('title')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="description" :value="__('Description')" />
        <textarea id="description" name="description" class="mt-1 block w-full rounded-md shadow-sm border-[#D1D5DB] bg-[#F9FAFB] text-[#111827] placeholder-[#9CA3AF] focus:border-[#6366F1] focus:ring-2 focus:ring-[rgba(99,102,241,0.2)]">{{ old('description', $event->description) }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="must_know" :value="__('MUST KNOW!')" />
        <textarea id="must_know" name="must_know" class="mt-1 block w-full rounded-md shadow-sm border-[#D1D5DB] bg-[#F9FAFB] text-[#111827] placeholder-[#9CA3AF] focus:border-[#6366F1] focus:ring-2 focus:ring-[rgba(99,102,241,0.2)]" rows="3" placeholder="Important info for attendees (dress code, gate times, etc.)">{{ old('must_know', $event->must_know) }}</textarea>
        <x-input-error :messages="$errors->get('must_know')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="venue" :value="__('Venue')" />
        <x-text-input id="venue" name="venue" type="text" class="mt-1 block w-full" :value="old('venue', $event->venue)" />
        <x-input-error :messages="$errors->get('venue')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="state" :value="__('State')" />
        <select id="state" name="state" required class="mt-1 block w-full rounded-md shadow-sm border-[#D1D5DB] bg-[#F9FAFB] text-[#111827] focus:border-[#6366F1] focus:ring-2 focus:ring-[rgba(99,102,241,0.2)]">
            <option value="" disabled {{ old('state', $event->state) ? '' : 'selected' }}>Select state</option>
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
                <option value="{{ $code }}" @selected(old('state', $event->state) === $code)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('state')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="mood" :value="__('Mood')" />
        <select id="mood" name="mood" required class="mt-1 block w-full rounded-md shadow-sm border-[#D1D5DB] bg-[#F9FAFB] text-[#111827] focus:border-[#6366F1] focus:ring-2 focus:ring-[rgba(99,102,241,0.2)]">
            <option value="" disabled {{ old('mood', $event->mood) ? '' : 'selected' }}>Select mood</option>
            @foreach (config('moods.list', ['Rave','Romantic','Amapiano','Afrobeats','Hip‑Hop','House','Live Band','Jazz','Techno','Gospel','Comedy','Networking']) as $m)
                <option value="{{ $m }}" @selected(old('mood', $event->mood) === $m)>{{ $m }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('mood')" class="mt-2" />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <x-input-label for="starts_at" :value="__('Starts At')" />
            <x-text-input id="starts_at" name="starts_at" type="datetime-local" class="mt-1 block w-full" :value="old('starts_at', optional($event->starts_at)->format('Y-m-d\TH:i'))" required />
            <x-input-error :messages="$errors->get('starts_at')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="ends_at" :value="__('Ends At')" />
            <x-text-input id="ends_at" name="ends_at" type="datetime-local" class="mt-1 block w-full" :value="old('ends_at', optional($event->ends_at)->format('Y-m-d\TH:i'))" />
            <x-input-error :messages="$errors->get('ends_at')" class="mt-2" />
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <x-input-label for="price" :value="__('Price')" />
            <x-text-input id="price" name="price" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('price', $event->price)" />
            <x-input-error :messages="$errors->get('price')" class="mt-2" />
            <p class="mt-1 text-xs text-[#9CA3AF]">Set to 0 for a free event.</p>
        </div>
        <div>
            <x-input-label for="early_bird_price" :value="__('Early-bird price')" />
            <x-text-input id="early_bird_price" name="early_bird_price" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('early_bird_price', $event->early_bird_price)" />
            <x-input-error :messages="$errors->get('early_bird_price')" class="mt-2" />
            <p class="mt-1 text-xs text-[#9CA3AF]">Optional discounted price until the date below.</p>
        </div>
    </div>

    <div class="flex items-start gap-2">
        <input type="hidden" name="pass_fees_to_buyer" value="0" />
        <input id="pass_fees_to_buyer" name="pass_fees_to_buyer" type="checkbox" value="1" class="mt-1 rounded border-[#D1D5DB] text-[#6366F1] shadow-sm focus:ring-[rgba(99,102,241,0.2)]" @checked(old('pass_fees_to_buyer', $event->pass_fees_to_buyer)) />
        <label for="pass_fees_to_buyer" class="text-sm text-[#374151]">Pass fees to buyer <span class="text-[#9CA3AF]">(adds 5% + ₦50 per ticket at checkout)</span></label>
        <x-input-error :messages="$errors->get('pass_fees_to_buyer')" class="mt-2" />
    </div>
    <script>
      document.addEventListener('DOMContentLoaded', () => {
        const price = document.getElementById('price');
        const eb = document.getElementById('early_bird_price');
        const fees = document.getElementById('pass_fees_to_buyer');
        function sync(){
          const isFree = parseFloat(price.value||'0') <= 0;
          fees.disabled = isFree; if (isFree) fees.checked = false;
          eb.disabled = isFree; eb.classList.toggle('opacity-60', isFree);
        }
        price.addEventListener('input', sync);
        sync();
      });
    </script>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <x-input-label for="early_bird_ends_at" :value="__('Early-bird ends at')" />
            <x-text-input id="early_bird_ends_at" name="early_bird_ends_at" type="datetime-local" class="mt-1 block w-full" :value="old('early_bird_ends_at', optional($event->early_bird_ends_at)->format('Y-m-d\TH:i'))" />
            <x-input-error :messages="$errors->get('early_bird_ends_at')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="capacity" :value="__('Capacity')" />
            <x-text-input id="capacity" name="capacity" type="number" step="1" min="1" class="mt-1 block w-full" :value="old('capacity', $event->capacity)" />
            <x-input-error :messages="$errors->get('capacity')" class="mt-2" />
        </div>
    </div>

    <div>
        <x-input-label for="free_tickets_count" :value="__('First N tickets free')" />
        <x-text-input id="free_tickets_count" name="free_tickets_count" type="number" step="1" min="0" class="mt-1 block w-full" :value="old('free_tickets_count', $event->free_tickets_count)" placeholder="e.g. 50" />
        <x-input-error :messages="$errors->get('free_tickets_count')" class="mt-2" />
        <p class="mt-1 text-xs text-[#9CA3AF]">The first N buyers get free tickets. Leave empty to disable.</p>
    </div>

    <div class="flex items-center">
        <input type="hidden" name="is_published" value="0" />
        <input id="is_published" name="is_published" type="checkbox" value="1" class="rounded border-[#D1D5DB] text-[#6366F1] shadow-sm focus:ring-[rgba(99,102,241,0.2)]" @checked(old('is_published', $event->is_published)) />
        <label for="is_published" class="ms-2 text-sm text-[#374151]">{{ __('Published') }}</label>
        <x-input-error :messages="$errors->get('is_published')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="whatsapp_group_url" :value="__('WhatsApp Group Link')" />
        <x-text-input id="whatsapp_group_url" name="whatsapp_group_url" type="url" class="mt-1 block w-full" :value="old('whatsapp_group_url', $event->whatsapp_group_url)" placeholder="https://chat.whatsapp.com/..." />
        <x-input-error :messages="$errors->get('whatsapp_group_url')" class="mt-2" />
        <p class="mt-1 text-xs text-[#9CA3AF]">Optional. Paste a WhatsApp group invite link to show a "Join Group" button on the event page.</p>
    </div>

    <div class="mt-4 flex items-start gap-2">
        <input type="hidden" name="use_custom_slug" value="0" />
        <input id="use_custom_slug" name="use_custom_slug" type="checkbox" value="1" class="mt-1 rounded border-[#D1D5DB] text-[#6366F1] shadow-sm focus:ring-[rgba(99,102,241,0.2)]" @checked(old('use_custom_slug', $event->use_custom_slug)) />
        <div class="flex-1">
            <label for="slug" class="text-sm text-[#374151]">Custom URL</label>
            <div class="mt-1 flex items-center gap-2">
                <span class="text-[#9CA3AF] text-sm">{{ url('/event') }}/</span>
                <input id="slug" name="slug" type="text" placeholder="event-name" value="{{ old('slug', $event->slug) }}" class="flex-1 rounded-md bg-[#F9FAFB] border border-[#D1D5DB] px-3 py-2 text-[#111827] focus:border-[#6366F1] focus:ring-2 focus:ring-[rgba(99,102,241,0.2)]" />
            </div>
            <p class="mt-1 text-xs text-[#9CA3AF]">Toggle to use a friendly URL. Only lowercase letters, numbers and hyphens.</p>
            <x-input-error :messages="$errors->get('slug')" class="mt-1" />
        </div>
    </div>
</div>

<div>
<x-input-label for="image" :value="__('Flyer (primary)')" />

    <script>
      // Simple slug sync
      document.addEventListener('DOMContentLoaded', () => {
        const title = document.getElementById('title');
        const slug = document.getElementById('slug');
        const toggle = document.getElementById('use_custom_slug');
        const normalize = (s) => (s||'').toLowerCase().trim().replace(/[^a-z0-9\s-]/g,'').replace(/[\s_-]+/g,'-').replace(/^-+|-+$/g,'');
        function refreshState(){ slug.disabled = !toggle.checked; slug.classList.toggle('opacity-60', !toggle.checked); }
        title?.addEventListener('input', () => { if (!slug.value) slug.value = normalize(title.value); });
        slug?.addEventListener('input', () => { slug.value = normalize(slug.value); });
        toggle?.addEventListener('change', refreshState);
        refreshState();
      });
    </script>
    <input id="image" name="image" type="file" accept="image/*"
           class="mt-1 block w-full rounded-md shadow-sm border-[#D1D5DB] bg-[#F9FAFB] text-[#111827] file:text-[#374151] file:bg-[#E5E7EB] file:border-0 file:rounded-md file:px-3 file:py-1 file:mr-3 focus:border-[#6366F1] focus:ring-2 focus:ring-[rgba(99,102,241,0.2)]" />
    <x-input-error :messages="$errors->get('image')" class="mt-2" />
    @if (!empty($event->image_path))
        <div class="mt-2">
            <img src="{{ $event->image_url }}" alt="Flyer" class="h-24 rounded" />
        </div>
    @endif
</div>

<div class="mt-4">
  <x-input-label for="gallery" :value="__('Additional flyers (optional)')" />
  <input id="gallery" name="gallery[]" type="file" accept="image/*" multiple class="mt-1 block w-full rounded-md shadow-sm border-[#D1D5DB] bg-[#F9FAFB] text-[#111827] file:text-[#374151] file:bg-[#E5E7EB] file:border-0 file:rounded-md file:px-3 file:py-1 file:mr-3 focus:border-[#6366F1] focus:ring-2 focus:ring-[rgba(99,102,241,0.2)]" />
  @php $gal = $event->gallery_urls ?? []; @endphp
  @if (!empty($gal))
    <div class="mt-2 grid grid-cols-4 gap-2">
      @foreach ($gal as $u)
        <img src="{{ $u }}" class="h-20 w-full object-cover rounded" alt="Flyer" />
      @endforeach
    </div>
  @endif
</div>
