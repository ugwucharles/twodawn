<header class="absolute inset-x-0 top-3 z-50" x-data="{ open:false }" x-init="window.addEventListener('pageshow', () => open = false); window.addEventListener('popstate', () => open = false);" @keydown.window.escape="open=false">
  <div class="mx-auto max-w-7xl px-6">
    <div class="relative h-14 flex items-center justify-between">
      <!-- Brand -->
      <a href="{{ url('/') }}" class="relative z-20 inline-flex items-center h-14 leading-none text-lg font-extrabold tracking-tight text-white">2<span class="text-indigo-400">DAWN</span></a>

      <!-- Center nav (desktop only, absolutely centered) -->
      <nav class="hidden md:flex absolute inset-0 items-center justify-center gap-6 text-sm text-zinc-200 z-10">
        <a href="{{ route('events.index') }}" class="hover:text-white">Events</a>
        <a href="{{ route('events.recent') }}" class="hover:text-white">Recent</a>
        <a href="{{ route('pricing') }}" class="hover:text-white">Pricing</a>
        <a href="{{ url('/#how-to-buy') }}" class="hover:text-white">How it works</a>
        <a href="{{ url('/#host') }}" class="hover:text-white">Host</a>
      </nav>

      <!-- Right: search icon + hamburger on mobile -->
      <div class="relative z-20 flex items-center h-14 gap-4" :class="{ 'invisible pointer-events-none': open }">
        <button type="button" aria-label="Search" class="text-zinc-200 hover:text-white" @click="$dispatch('open-modal', 'search-modal')">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 align-middle" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.9 14.32a8 8 0 111.414-1.414l3.387 3.387a1 1 0 01-1.414 1.414l-3.387-3.387zM14 8a6 6 0 11-12 0 6 6 0 0112 0z" clip-rule="evenodd"/></svg>
        </button>
        <button type="button" class="md:hidden text-zinc-200 hover:text-white" aria-label="Open menu" @click="open=true">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 align-middle" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
      </div>
    </div>
  </div>

  <!-- Overlay -->
  <div x-cloak x-show="open" x-transition.opacity class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[60] md:hidden" @click="open=false" aria-hidden="true"></div>

  <!-- Right drawer -->
  <aside x-cloak x-show="open" x-transition:enter="transition transform ease-out duration-150" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition transform ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="fixed inset-y-0 right-0 w-72 max-w-[85vw] bg-zinc-950/95 border-l border-white/10 z-[70] p-6 md:hidden">
    <div class="flex items-center justify-between">
      <span class="text-base font-extrabold text-white">Menu</span>
      <button type="button" class="text-zinc-300 hover:text-white" aria-label="Close menu" @click="open=false">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
      </button>
    </div>
    @if (request()->routeIs('host.*'))
      <nav class="mt-6 grid gap-2 text-sm text-zinc-200">
        <a href="#scan" class="rounded px-3 py-2 hover:bg-white/5" @click="open=false">Scanner</a>
        <a href="#manual" class="rounded px-3 py-2 hover:bg-white/5" @click="open=false">Manual entry</a>
        <a href="#recent-card" class="rounded px-3 py-2 hover:bg-white/5" @click="open=false">Recent scans</a>
        @if(isset($host))
          <a href="{{ route('host.people', $host->token) }}" class="rounded px-3 py-2 hover:bg-white/5" @click="open=false">People</a>
          <a href="{{ route('host.sales.export', $host->token) }}" class="rounded px-3 py-2 hover:bg-white/5" @click="open=false">Export sales</a>
          <a href="{{ route('host.sales.exportDaily', $host->token) }}" class="rounded px-3 py-2 hover:bg-white/5" @click="open=false">Daily sales</a>
          <a href="{{ route('host.people.export', $host->token) }}" class="rounded px-3 py-2 hover:bg-white/5" @click="open=false">Check-ins CSV</a>
        @endif
        @if(isset($event) && $event?->public_url)
          <a href="{{ $event->public_url }}" class="rounded px-3 py-2 hover:bg-white/5" @click="open=false">View event</a>
        @endif
      </nav>
    @else
      <nav class="mt-6 grid gap-2 text-sm text-zinc-200">
        <a href="{{ route('events.index') }}" class="rounded px-3 py-2 hover:bg-white/5" @click="open=false">Events</a>
        <a href="{{ route('events.recent') }}" class="rounded px-3 py-2 hover:bg-white/5" @click="open=false">Recent</a>
        <a href="{{ route('pricing') }}" class="rounded px-3 py-2 hover:bg-white/5" @click="open=false">Pricing</a>
        <a href="{{ url('/#how-to-buy') }}" class="rounded px-3 py-2 hover:bg-white/5" @click="open=false">How it works</a>
        <a href="{{ url('/#host') }}" class="rounded px-3 py-2 hover:bg-white/5" @click="open=false">Host</a>
      </nav>
    @endif
  </aside>
</header>
