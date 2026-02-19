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
        <textarea id="description" name="description" class="mt-1 block w-full rounded-md shadow-sm border-white/20 bg-zinc-900/70 text-white placeholder-zinc-400 focus:border-indigo-400 focus:ring-indigo-400/30">{{ old('description', $event->description) }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="venue" :value="__('Venue')" />
        <x-text-input id="venue" name="venue" type="text" class="mt-1 block w-full" :value="old('venue', $event->venue)" />
        <x-input-error :messages="$errors->get('venue')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="mood" :value="__('Mood')" />
        <select id="mood" name="mood" required class="mt-1 block w-full rounded-md shadow-sm border-white/20 bg-zinc-900/70 text-white focus:border-indigo-400 focus:ring-indigo-400/30">
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
            <p class="mt-1 text-xs text-zinc-400">Set to 0 for a free event.</p>
        </div>
        <div>
            <x-input-label for="early_bird_price" :value="__('Early-bird price')" />
            <x-text-input id="early_bird_price" name="early_bird_price" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('early_bird_price', $event->early_bird_price)" />
            <x-input-error :messages="$errors->get('early_bird_price')" class="mt-2" />
            <p class="mt-1 text-xs text-zinc-400">Optional discounted price until the date below.</p>
        </div>
    </div>

    <div class="flex items-start gap-2">
        <input type="hidden" name="pass_fees_to_buyer" value="0" />
        <input id="pass_fees_to_buyer" name="pass_fees_to_buyer" type="checkbox" value="1" class="mt-1 rounded border-white/20 text-indigo-500 shadow-sm focus:ring-indigo-400/30" @checked(old('pass_fees_to_buyer', $event->pass_fees_to_buyer)) />
        <label for="pass_fees_to_buyer" class="text-sm text-zinc-300">Pass fees to buyer <span class="text-zinc-500">(adds 5% + ₦50 per ticket at checkout)</span></label>
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
        <p class="mt-1 text-xs text-zinc-400">The first N buyers get free tickets. Leave empty to disable.</p>
    </div>

    <div class="flex items-center">
        <input type="hidden" name="is_published" value="0" />
        <input id="is_published" name="is_published" type="checkbox" value="1" class="rounded border-white/20 text-indigo-500 shadow-sm focus:ring-indigo-400/30" @checked(old('is_published', $event->is_published)) />
        <label for="is_published" class="ms-2 text-sm text-zinc-300">{{ __('Published') }}</label>
        <x-input-error :messages="$errors->get('is_published')" class="mt-2" />
    </div>

    <div class="mt-4 flex items-start gap-2">
        <input type="hidden" name="use_custom_slug" value="0" />
        <input id="use_custom_slug" name="use_custom_slug" type="checkbox" value="1" class="mt-1 rounded border-white/20 text-indigo-500 shadow-sm focus:ring-indigo-400/30" @checked(old('use_custom_slug', $event->use_custom_slug)) />
        <div class="flex-1">
            <label for="slug" class="text-sm text-zinc-300">Custom URL</label>
            <div class="mt-1 flex items-center gap-2">
                <span class="text-zinc-400 text-sm">{{ url('/event') }}/</span>
                <input id="slug" name="slug" type="text" placeholder="event-name" value="{{ old('slug', $event->slug) }}" class="flex-1 rounded-md bg-black/30 border border-white/10 px-3 py-2 focus:border-white/30 focus:ring-0" />
            </div>
            <p class="mt-1 text-xs text-zinc-500">Toggle to use a friendly URL. Only lowercase letters, numbers and hyphens.</p>
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
           class="mt-1 block w-full rounded-md shadow-sm border-white/20 bg-zinc-900/70 text-white file:text-white file:bg-zinc-800 file:border-0 focus:border-indigo-400 focus:ring-indigo-400/30" />
    <x-input-error :messages="$errors->get('image')" class="mt-2" />
    @if (!empty($event->image_path))
        <div class="mt-2">
            <img src="{{ $event->image_url }}" alt="Flyer" class="h-24 rounded" />
        </div>
    @endif
</div>

<div class="mt-4">
  <x-input-label for="gallery" :value="__('Additional flyers (optional)')" />
  <input id="gallery" name="gallery[]" type="file" accept="image/*" multiple class="mt-1 block w-full rounded-md shadow-sm border-white/20 bg-zinc-900/70 text-white file:text-white file:bg-zinc-800 file:border-0 focus:border-indigo-400 focus:ring-indigo-400/30" />
  @php $gal = $event->gallery_urls ?? []; @endphp
  @if (!empty($gal))
    <div class="mt-2 grid grid-cols-4 gap-2">
      @foreach ($gal as $u)
        <img src="{{ $u }}" class="h-20 w-full object-cover rounded" alt="Flyer" />
      @endforeach
    </div>
  @endif
</div>
