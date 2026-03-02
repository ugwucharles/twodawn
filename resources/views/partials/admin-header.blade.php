<header class="app-header bg-white border-b border-[#eeedf2] sticky top-0 z-[90] h-[64px] w-full flex items-center" x-data="{ open:false }">
  <div class="w-full px-4 md:px-6 lg:px-10 h-full flex items-center justify-between gap-4">

    <!-- Left: Logo -->
    <div class="flex items-center shrink-0">
      <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
        <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name', '2DAWN') }}" class="h-12 w-auto">
        <span class="hidden sm:inline text-[11px] font-semibold uppercase tracking-[0.18em] text-eventbrite-gray-600">Admin</span>
      </a>
    </div>

    <!-- Center: Nav -->
    <div class="hidden lg:flex items-center gap-1 shrink-0">
      <nav class="flex items-center text-[14px] font-medium text-eventbrite-dark">
        <a href="{{ route('admin.dashboard') }}" class="px-3 py-2 hover:text-tix-orange {{ request()->routeIs('admin.dashboard') ? 'text-tix-orange' : '' }}">Dashboard</a>
        <a href="{{ route('admin.events.index') }}" class="px-3 py-2 hover:text-tix-orange {{ request()->routeIs('admin.events.*') ? 'text-tix-orange' : '' }}">Events</a>
        <a href="{{ route('admin.orders.index') }}" class="px-3 py-2 hover:text-tix-orange {{ request()->routeIs('admin.orders.*') ? 'text-tix-orange' : '' }}">Orders</a>
        <a href="{{ route('admin.scanner.index') }}" class="px-3 py-2 hover:text-tix-orange {{ request()->routeIs('admin.scanner.*') ? 'text-tix-orange' : '' }}">Scanner</a>
        <a href="{{ route('admin.host-requests.index') }}" class="px-3 py-2 hover:text-tix-orange {{ request()->routeIs('admin.host-requests.*') ? 'text-tix-orange' : '' }}">Host Requests</a>
        <a href="{{ route('admin.comments.index') }}" class="px-3 py-2 hover:text-tix-orange {{ request()->routeIs('admin.comments.*') ? 'text-tix-orange' : '' }}">Comments</a>
        <a href="{{ route('admin.chat.index') }}" class="px-3 py-2 hover:text-tix-orange {{ request()->routeIs('admin.chat.*') ? 'text-tix-orange' : '' }}">Chat</a>
      </nav>
      <div class="ml-4">
        <a href="{{ route('admin.events.create') }}" class="inline-flex items-center px-4 py-2 rounded-md bg-tix-orange text-white text-[14px] font-semibold hover:bg-[#e55a2d] shadow-sm">
          New event
        </a>
      </div>
    </div>

    <!-- Right: mobile controls -->
    <div class="flex lg:hidden items-center gap-2">
      <button type="button" class="p-2 text-eventbrite-dark hover:bg-[#f8f7fa] rounded-full transition-colors" aria-label="Open menu" @click="open=true">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
      </button>
    </div>
  </div>

  <!-- Overlay -->
  <div x-cloak x-show="open" x-transition.opacity class="fixed inset-0 bg-eventbrite-dark/40 backdrop-blur-sm z-[85] lg:hidden" @click="open=false" aria-hidden="true"></div>

  <!-- Mobile drawer -->
  <aside
    x-cloak
    x-show="open"
    x-transition:enter="transition transform ease-out duration-200"
    x-transition:enter-start="translate-x-full"
    x-transition:enter-end="translate-x-0"
    x-transition:leave="transition transform ease-in duration-150"
    x-transition:leave-start="translate-x-0"
    x-transition:leave-end="translate-x-full"
    class="fixed inset-y-0 right-0 w-full max-w-[320px] bg-white shadow-2xl z-[90] flex flex-col lg:hidden"
  >
    <div class="flex items-center justify-between p-4 border-b border-eventbrite-gray-100">
      <span class="text-sm font-semibold text-eventbrite-dark uppercase tracking-[0.18em]">Admin</span>
      <button type="button" class="p-2 text-eventbrite-gray-400 hover:bg-eventbrite-gray-50 rounded-full transition-colors" aria-label="Close menu" @click="open=false">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
      </button>
    </div>
    <nav class="flex-1 overflow-y-auto py-2 text-[15px] text-eventbrite-dark">
      <a href="{{ route('admin.dashboard') }}" class="block px-6 py-3 hover:bg-eventbrite-gray-50" @click="open=false">Dashboard</a>
      <a href="{{ route('admin.events.index') }}" class="block px-6 py-3 hover:bg-eventbrite-gray-50" @click="open=false">Events</a>
      <a href="{{ route('admin.orders.index') }}" class="block px-6 py-3 hover:bg-eventbrite-gray-50" @click="open=false">Orders</a>
      <a href="{{ route('admin.scanner.index') }}" class="block px-6 py-3 hover:bg-eventbrite-gray-50" @click="open=false">Scanner</a>
      <a href="{{ route('admin.host-requests.index') }}" class="block px-6 py-3 hover:bg-eventbrite-gray-50" @click="open=false">Host Requests</a>
      <a href="{{ route('admin.comments.index') }}" class="block px-6 py-3 hover:bg-eventbrite-gray-50" @click="open=false">Comments</a>
      <a href="{{ route('admin.chat.index') }}" class="block px-6 py-3 hover:bg-eventbrite-gray-50" @click="open=false">Chat</a>
      <a href="{{ route('admin.events.create') }}" class="block px-6 py-3 hover:bg-eventbrite-gray-50 font-semibold text-tix-orange" @click="open=false">New event</a>
      <a href="{{ route('profile.edit') }}" class="block px-6 py-3 hover:bg-eventbrite-gray-50" @click="open=false">Profile</a>
      <form method="POST" action="{{ route('logout') }}" class="border-t border-eventbrite-gray-100 mt-2 pt-2">
        @csrf
        <button type="submit" class="w-full text-left px-6 py-3 hover:bg-eventbrite-gray-50">Logout</button>
      </form>
    </nav>
  </aside>
</header>
