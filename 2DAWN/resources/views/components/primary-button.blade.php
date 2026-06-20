<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2.5 bg-[#6366F1] border border-transparent rounded-md font-semibold text-sm text-white hover:bg-[#4F46E5] focus:bg-[#4F46E5] active:bg-[#4338CA] focus:outline-none focus:ring-2 focus:ring-[#6366F1] focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
