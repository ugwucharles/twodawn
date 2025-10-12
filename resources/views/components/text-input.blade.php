@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-md shadow-sm border-white/20 bg-zinc-900/70 text-white placeholder-zinc-400 focus:border-indigo-400 focus:ring-indigo-400/30']) }}>
