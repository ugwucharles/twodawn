<header class="fixed top-6 left-0 right-0 z-50">
  <div class="mx-auto max-w-7xl px-4 sm:px-6">
    <div x-data="{ open:false }" class="relative mx-auto w-full sm:w-[90%] md:w-[70%] rounded-full bg-white ring-1 ring-zinc-200 px-4 py-3 text-black">
      <div class="flex items-center justify-between">
        <a href="{{ route('admin.dashboard') }}" class="text-lg font-extrabold tracking-tight">2<span class="text-purple-600">DAWN</span> <span class="text-zinc-600">Admin</span></a>
        <button class="sm:hidden inline-flex items-center justify-center p-2 rounded-md text-zinc-700 hover:bg-zinc-100" @click="open=!open" aria-label="Toggle menu">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <nav class="hidden sm:flex items-center gap-4 text-sm">
          <a href="{{ route('admin.dashboard') }}" class="px-1 {{ request()->routeIs('admin.dashboard') ? 'text-black font-semibold' : 'text-zinc-700 hover:text-black' }}">Dashboard</a>
          <a href="{{ route('admin.events.index') }}" class="px-1 {{ request()->routeIs('admin.events.*') ? 'text-black font-semibold' : 'text-zinc-700 hover:text-black' }}">Events</a>
          <a href="{{ route('admin.orders.index') }}" class="px-1 {{ request()->routeIs('admin.orders.*') ? 'text-black font-semibold' : 'text-zinc-700 hover:text-black' }}">Orders</a>
          <a href="{{ route('admin.host-requests.index') }}" class="px-1 {{ request()->routeIs('admin.host-requests.*') ? 'text-black font-semibold' : 'text-zinc-700 hover:text-black' }}">Requests</a>
          <a href="{{ route('admin.comments.index') }}" class="px-1 {{ request()->routeIs('admin.comments.*') ? 'text-black font-semibold' : 'text-zinc-700 hover:text-black' }}">Comments</a>
          <a href="{{ route('admin.events.create') }}" class="inline-flex items-center px-4 py-2 rounded-full bg-black text-white hover:opacity-90 transition">New Event</a>
          <form method="POST" action="{{ route('logout') }}" class="inline">
            @csrf
            <button type="submit" class="text-zinc-700 hover:text-black">Logout</button>
          </form>
        </nav>
      </div>
      <!-- Mobile menu -->
      <div x-show="open" x-transition class="sm:hidden absolute left-0 right-0 top-full mt-2">
        <div class="mx-auto w-[96%] rounded-2xl bg-white ring-1 ring-zinc-200 p-4 space-y-2 text-sm">
          <a href="{{ route('admin.dashboard') }}" class="block {{ request()->routeIs('admin.dashboard') ? 'text-black font-semibold' : 'text-zinc-700 hover:text-black' }}">Dashboard</a>
          <a href="{{ route('admin.events.index') }}" class="block {{ request()->routeIs('admin.events.*') ? 'text-black font-semibold' : 'text-zinc-700 hover:text-black' }}">Events</a>
          <a href="{{ route('admin.orders.index') }}" class="block {{ request()->routeIs('admin.orders.*') ? 'text-black font-semibold' : 'text-zinc-700 hover:text-black' }}">Orders</a>
          <a href="{{ route('admin.host-requests.index') }}" class="block {{ request()->routeIs('admin.host-requests.*') ? 'text-black font-semibold' : 'text-zinc-700 hover:text-black' }}">Requests</a>
          <a href="{{ route('admin.comments.index') }}" class="block {{ request()->routeIs('admin.comments.*') ? 'text-black font-semibold' : 'text-zinc-700 hover:text-black' }}">Comments</a>
          <a href="{{ route('admin.events.create') }}" class="block text-center px-4 py-2 rounded-full bg-black text-white">New Event</a>
          <form method="POST" action="{{ route('logout') }}" class="block pt-2">
            @csrf
            <button type="submit" class="w-full text-left text-zinc-700 hover:text-black">Logout</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</header>
