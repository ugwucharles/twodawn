<header class="absolute inset-x-0 top-3 z-50" x-data="{ open:false }" @keydown.window.escape="open=false">
  <div class="mx-auto max-w-7xl px-6">
    <div class="h-14 grid grid-cols-3 items-center">
      <!-- Brand -->
      <a href="{{ url('/') }}" class="justify-self-start text-lg font-extrabold tracking-tight text-white">2<span class="text-indigo-400">DAWN</span></a>

      <!-- Center nav (hidden on small) -->
      <nav class="justify-self-center hidden md:flex items-center justify-center gap-6 text-sm text-zinc-200">
        <a href="{{ route('events.index') }}" class="hover:text-white">Events</a>
        <a href="{{ route('events.recent') }}" class="hover:text-white">Recent</a>
        <a href="{{ url('/#how-to-buy') }}" class="hover:text-white">How it works</a>
        <a href="{{ url('/#host') }}" class="hover:text-white">Host</a>
      </nav>

      <!-- Right: search + hamburger on mobile -->
      <div class="fixed right-4 top-4 z-[60] md:static md:justify-self-end flex items-center gap-4">
        <a href="{{ route('events.index') }}" aria-label="Search" class="text-zinc-200 hover:text-white">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.9 14.32a8 8 0 111.414-1.414l3.387 3.387a1 1 0 01-1.414 1.414l-3.387-3.387zM14 8a6 6 0 11-12 0 6 6 0 0112 0z" clip-rule="evenodd"/></svg>
        </a>
        <button type="button" class="md:hidden text-zinc-200 hover:text-white" aria-label="Open menu" @click="open=true">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
      </div>
    </div>
  </div>

  <!-- Overlay -->
  <div x-show="open" x-transition.opacity class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 md:hidden" @click="open=false" aria-hidden="true"></div>

  <!-- Right drawer -->
  <aside x-show="open" x-transition:enter="transition transform ease-out duration-150" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition transform ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="fixed inset-y-0 right-0 w-72 max-w-[85vw] bg-zinc-950/95 border-l border-white/10 z-50 p-6 md:hidden">
    <div class="flex items-center justify-between">
      <span class="text-base font-extrabold text-white">Menu</span>
      <button type="button" class="text-zinc-300 hover:text-white" aria-label="Close menu" @click="open=false">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
      </button>
    </div>
    <nav class="mt-6 grid gap-2 text-sm text-zinc-200">
      <a href="{{ route('events.index') }}" class="rounded px-3 py-2 hover:bg-white/5">Events</a>
      <a href="{{ route('events.recent') }}" class="rounded px-3 py-2 hover:bg-white/5">Recent</a>
      <a href="{{ url('/#how-to-buy') }}" class="rounded px-3 py-2 hover:bg-white/5">How it works</a>
      <a href="{{ url('/#host') }}" class="rounded px-3 py-2 hover:bg-white/5">Host</a>
    </nav>
  </aside>
</header>
