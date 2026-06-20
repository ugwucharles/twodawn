<footer class="py-12">
  <div class="max-w-7xl mx-auto px-6">
    <div class="flex flex-col items-center text-center gap-4">

      <nav class="flex flex-wrap items-center justify-center gap-x-[5px] gap-y-[5px] text-sm text-eventbrite-dark">
        <a href="{{ url('/') }}" class="px-0 hover:text-tix-orange">Home</a>
        <span aria-hidden class="mx-[5px] opacity-40 text-eventbrite-gray-400">|</span>
        <a href="{{ route('events.index') }}" class="px-0 hover:text-tix-orange">Discover events</a>
        <span aria-hidden class="mx-[5px] opacity-40 text-eventbrite-gray-400">|</span>
        <a href="{{ route('events.recent') }}" class="px-0 hover:text-tix-orange">Find my tickets</a>
        <span aria-hidden class="mx-[5px] opacity-40 text-eventbrite-gray-400">|</span>
        <a href="{{ route('organizer.login') }}" class="px-0 hover:text-tix-orange">Create event</a>
      </nav>
      <div class="text-xs text-zinc-500">© {{ date('Y') }} All rights reserved.</div>
    </div>
  </div>
</footer>
