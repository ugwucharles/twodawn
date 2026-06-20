<header class="app-header bg-black border-b border-zinc-800 sticky top-0 z-[90] h-[72px] w-full flex items-center" x-data="{ open:false }">
  <div class="w-full px-4 md:px-6 lg:px-10 h-full flex items-center justify-between gap-4">

    <!-- Left: Logo -->
    <div class="flex items-center shrink-0">
      <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
        <span class="inline-flex items-center font-black text-2xl tracking-tighter select-none" style="font-family: 'Taskor', sans-serif;">
          <span style="color: #ffffff; margin-right: 2px;">2</span>
          <span style="color: #8b5cf6;">DAWN</span>
        </span>
        <span class="hidden sm:inline text-[11px] font-semibold uppercase tracking-[0.18em] text-zinc-400">Admin</span>
      </a>
    </div>

    <!-- Center: Nav -->
    <div class="hidden lg:flex items-center gap-1 shrink-0">
      <nav class="flex items-center text-[16px] font-medium text-zinc-300">
        <a href="{{ route('admin.dashboard') }}" class="px-3 py-2 hover:text-white {{ request()->routeIs('admin.dashboard') ? 'text-white font-semibold' : '' }}">Dashboard</a>
        <a href="{{ route('admin.events.index') }}" class="px-3 py-2 hover:text-white {{ request()->routeIs('admin.events.*') ? 'text-white font-semibold' : '' }}">Events</a>
        <a href="{{ route('admin.orders.index') }}" class="px-3 py-2 hover:text-white {{ request()->routeIs('admin.orders.*') ? 'text-white font-semibold' : '' }}">Orders</a>
        <a href="{{ route('admin.scanner.index') }}" class="px-3 py-2 hover:text-white {{ request()->routeIs('admin.scanner.*') ? 'text-white font-semibold' : '' }}">Scanner</a>
      </nav>
      <div class="ml-4">
        <a href="{{ route('admin.events.create') }}" class="inline-flex items-center px-4 py-2 rounded-md bg-white text-black text-[14px] font-semibold hover:bg-zinc-200 shadow-sm">
          New event
        </a>
      </div>
    </div>

    <!-- Right: mobile controls -->
    <div class="flex lg:hidden items-center gap-2">
      <button type="button" class="p-2 text-zinc-300 hover:text-white rounded-full transition-colors" aria-label="Open menu" @click="open=true">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
      </button>
    </div>
  </div>

  <!-- Overlay -->
  <div x-cloak x-show="open" x-transition.opacity class="fixed inset-0 bg-black/80 backdrop-blur-xl z-[85] lg:hidden" @click="open=false" aria-hidden="true"></div>

  <!-- Mobile drawer -->
  <aside
    x-cloak
    x-show="open"
    x-transition:enter="transition transform ease-out duration-200"
    x-transition:enter-start="-translate-y-full"
    x-transition:enter-end="translate-y-0"
    x-transition:leave="transition transform ease-in duration-150"
    x-transition:leave-start="translate-y-0"
    x-transition:leave-end="-translate-y-full"
    class="fixed inset-x-0 top-0 w-full bg-black border-b border-zinc-800 shadow-2xl z-[90] flex flex-col lg:hidden max-h-screen overflow-y-auto"
  >
    <div class="flex items-center justify-between p-4 border-b border-zinc-800">
      <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
        <span class="inline-flex items-center font-black text-2xl tracking-tighter select-none" style="font-family: 'Taskor', sans-serif;">
          <span style="color: #ffffff; margin-right: 2px;">2</span>
          <span style="color: #8b5cf6;">DAWN</span>
        </span>
        <span class="text-[11px] font-semibold uppercase tracking-[0.18em] text-zinc-400">Admin</span>
      </a>
      <button type="button" class="p-2 text-zinc-400 hover:text-white hover:bg-zinc-800 rounded-full transition-colors" aria-label="Close menu" @click="open=false">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
      </button>
    </div>
    <nav class="flex-1 overflow-y-auto py-2 text-[15px] text-zinc-300">
      <a href="{{ route('admin.dashboard') }}" class="block px-6 py-3 hover:bg-zinc-900 hover:text-white" @click="open=false">Dashboard</a>
      <a href="{{ route('admin.events.index') }}" class="block px-6 py-3 hover:bg-zinc-900 hover:text-white" @click="open=false">Events</a>
      <a href="{{ route('admin.orders.index') }}" class="block px-6 py-3 hover:bg-zinc-900 hover:text-white" @click="open=false">Orders</a>
      <a href="{{ route('admin.scanner.index') }}" class="block px-6 py-3 hover:bg-zinc-900 hover:text-white" @click="open=false">Scanner</a>
      <a href="{{ route('admin.events.create') }}" class="block px-6 py-3 hover:bg-zinc-900 font-semibold text-white" @click="open=false">New event</a>
      <a href="{{ route('profile.edit') }}" class="block px-6 py-3 hover:bg-zinc-900 hover:text-white" @click="open=false">Profile</a>
      <form method="POST" action="{{ route('logout') }}" class="border-t border-zinc-800 mt-2 pt-2">
        @csrf
        <button type="submit" class="w-full text-left px-6 py-3 hover:bg-zinc-900 hover:text-white">Logout</button>
      </form>
    </nav>
  </aside>
</header>
