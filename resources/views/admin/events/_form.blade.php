<div class="space-y-4">
    <div>
        <x-input-label for="title" :value="__('Title')" />
        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title', $event->title)" required autofocus />
        <x-input-error :messages="$errors->get('title')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="description" :value="__('Description')" />
        <textarea id="description" name="description" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $event->description) }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="venue" :value="__('Venue')" />
        <x-text-input id="venue" name="venue" type="text" class="mt-1 block w-full" :value="old('venue', $event->venue)" />
        <x-input-error :messages="$errors->get('venue')" class="mt-2" />
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
        </div>
        <div>
            <x-input-label for="early_bird_price" :value="__('Early-bird price')" />
            <x-text-input id="early_bird_price" name="early_bird_price" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('early_bird_price', $event->early_bird_price)" />
            <x-input-error :messages="$errors->get('early_bird_price')" class="mt-2" />
        </div>
    </div>

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

    <div class="flex items-center">
        <input id="is_published" name="is_published" type="checkbox" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(old('is_published', $event->is_published)) />
        <label for="is_published" class="ms-2 text-sm text-gray-700">{{ __('Published') }}</label>
        <x-input-error :messages="$errors->get('is_published')" class="mt-2" />
    </div>
</div>

<div>
    <x-input-label for="image" :value="__('Flyer')" />
    <input id="image" name="image" type="file" accept="image/*"
           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
    <x-input-error :messages="$errors->get('image')" class="mt-2" />
    @if (!empty($event->image_path))
        <div class="mt-2">
            <img src="{{ asset('storage/'.$event->image_path) }}" alt="Flyer" class="h-24 rounded" />
        </div>
    @endif
</div>
