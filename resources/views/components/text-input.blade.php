@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-md shadow-sm border-[#D1D5DB] bg-[#F9FAFB] text-[#111827] placeholder-[#9CA3AF] focus:border-[#6366F1] focus:ring-2 focus:ring-[rgba(99,102,241,0.2)]']) }}>
