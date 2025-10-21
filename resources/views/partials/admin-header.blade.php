<header class="absolute inset-x-0 top-3 z-50" x-data="{ open:false }" x-init="window.addEventListener('pageshow', () => open = false); window.addEventListener('popstate', () => open = false);" @keydown.window.escape="open=false">
  <div class="mx-auto max-w-7xl px-6">
    <div class="h-14 grid grid-cols-3 items-center">
      <!-- Brand -->
      <a href="{{ route('admin.dashboard') }}" class="justify-self-start inline-flex items-center h-14 leading-none text-lg font-extrabold tracking-tight text-white">2<span class="text-indigo-400">DAWN</span> <span class="hidden sm:inline text-zinc-300">Admin</span></a>

      <!-- Center nav (hidden on small) -->
      <nav class="justify-self-center hidden md:flex h-14 items-center justify-center gap-6 text-sm text-zinc-200">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-white {{ request()->routeIs('admin.dashboard') ? 'text-white font-semibold' : '' }}">Dashboard</a>
        <a href="{{ route('admin.events.index') }}" class="hover:text-white {{ request()->routeIs('admin.events.*') ? 'text-white font-semibold' : '' }}">Events</a>
        <a href="{{ route('admin.orders.index') }}" class="hover:text-white {{ request()->routeIs('admin.orders.*') ? 'text-white font-semibold' : '' }}">Orders</a>
        <a href="{{ route('admin.scanner.index') }}" class="hover:text-white {{ request()->routeIs('admin.scanner.*') ? 'text-white font-semibold' : '' }}">Scanner</a>
        <a href="{{ route('admin.host-requests.index') }}" class="hover:text-white {{ request()->routeIs('admin.host-requests.*') ? 'text-white font-semibold' : '' }}">Host Requests</a>
        <a href="{{ route('admin.comments.index') }}" class="hover:text-white {{ request()->routeIs('admin.comments.*') ? 'text-white font-semibold' : '' }}">Comments</a>
        <a href="{{ route('admin.events.create') }}" class="inline-flex items-center px-4 py-2 rounded-full bg-white text-black hover:bg-zinc-100 transition">New Event</a>
      </nav>

      <!-- Right: hamburger on mobile (no search) -->
      <div class="col-start-3 justify-self-end flex items-center h-14 gap-4" :class="{ 'invisible pointer-events-none': open }">
        <button type="button" class="md:hidden text-zinc-200 hover:text-white" aria-label="Open menu" @click="open=true">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 align-middle" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
      </div>
    </div>
  </div>

  <!-- Overlay -->
  <div x-show="open" x-transition.opacity class="fixed inset-0 bg-black/50 backdrop-blur-sm z-60 md:hidden" @click="open=false" aria-hidden="true"></div>

  <!-- Right drawer -->
  <aside x-show="open" x-transition:enter="transition transform ease-out duration-150" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition transform ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="fixed inset-y-0 right-0 w-72 max-w-[85vw] bg-zinc-950/95 border-l border-white/10 z-70 p-6 md:hidden">
    <div class="flex items-center justify-between">
      <span class="text-base font-extrabold text-white">Menu</span>
      <button type="button" class="text-zinc-300 hover:text-white" aria-label="Close menu" @click="open=false">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
      </button>
    </div>
    <nav class="mt-6 grid gap-2 text-sm text-zinc-200">
      <a href="{{ route('admin.dashboard') }}" class="rounded px-3 py-2 hover:bg-white/5" @click="open=false">Dashboard</a>
      <a href="{{ route('admin.events.index') }}" class="rounded px-3 py-2 hover:bg-white/5" @click="open=false">Events</a>
      <a href="{{ route('admin.orders.index') }}" class="rounded px-3 py-2 hover:bg-white/5" @click="open=false">Orders</a>
      <a href="{{ route('admin.scanner.index') }}" class="rounded px-3 py-2 hover:bg-white/5" @click="open=false">Scanner</a>
      <a href="{{ route('admin.host-requests.index') }}" class="rounded px-3 py-2 hover:bg-white/5" @click="open=false">Host Requests</a>
      <a href="{{ route('admin.comments.index') }}" class="rounded px-3 py-2 hover:bg-white/5" @click="open=false">Comments</a>
      <a href="{{ route('admin.events.create') }}" class="rounded px-3 py-2 hover:bg-white/5" @click="open=false">New Event</a>
      <a href="{{ route('profile.edit') }}" class="rounded px-3 py-2 hover:bg-white/5" @click="open=false">Profile</a>
      <form method="POST" action="{{ route('logout') }}" class="pt-2">
        @csrf
        <button type="submit" class="w-full text-left rounded px-3 py-2 hover:bg-white/5">Logout</button>
      </form>
    </nav>
  </aside>
</header>
