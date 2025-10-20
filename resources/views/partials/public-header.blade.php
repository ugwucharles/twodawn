<header class="absolute inset-x-0 top-3 z-50">
  <div class="mx-auto max-w-7xl px-6">
    <div class="h-14 grid grid-cols-3 items-center">
      <a href="{{ url('/') }}" class="justify-self-start text-lg font-extrabold tracking-tight text-white">2<span class="text-indigo-400">DAWN</span></a>
      <nav class="justify-self-center flex items-center gap-6 text-sm text-zinc-200">
        <a href="{{ route('events.index') }}" class="hover:text-white">Events</a>
        <a href="{{ route('events.recent') }}" class="hover:text-white">Recent</a>
        <a href="{{ url('/#how-to-buy') }}" class="hover:text-white">How it works</a>
        <a href="{{ url('/#host') }}" class="hover:text-white">Host</a>
      </nav>
      <div class="justify-self-end">
        <a href="{{ route('events.index') }}" aria-label="Search" class="text-zinc-200 hover:text-white">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.9 14.32a8 8 0 111.414-1.414l3.387 3.387a1 1 0 01-1.414 1.414l-3.387-3.387zM14 8a6 6 0 11-12 0 6 6 0 0112 0z" clip-rule="evenodd"/></svg>
        </a>
      </div>
    </div>
  </div>
</header>
