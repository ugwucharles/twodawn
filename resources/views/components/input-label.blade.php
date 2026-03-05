@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-[#374151]']) }}>
    {{ $value ?? $slot }}
</label>
