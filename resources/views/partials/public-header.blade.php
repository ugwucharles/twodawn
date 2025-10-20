<header class="absolute inset-x-0 top-3 z-50">
  <div class="mx-auto max-w-7xl px-6">
    <div class="h-14 flex items-center justify-between">
      <a href="{{ url('/') }}" class="text-lg font-extrabold tracking-tight text-white">2<span class="text-indigo-400">DAWN</span></a>
      <nav class="flex items-center gap-6 text-sm text-zinc-200">
        <a href="{{ route('events.index') }}" class="hover:text-white">Events</a>
        <a href="{{ route('events.recent') }}" class="hover:text-white">Recent</a>
        <a href="{{ url('/#how-to-buy') }}" class="hover:text-white">How it works</a>
        <a href="{{ url('/#host') }}" class="hover:text-white">Host</a>
      </nav>
    </div>
  </div>
</header>
