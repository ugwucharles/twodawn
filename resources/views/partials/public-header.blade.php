<header class="app-header bg-white border-b border-[#eeedf2] sticky top-0 z-[100] h-[72px] w-full flex items-center" x-data="{ open:false }">
  @php $logoOnly = request()->routeIs('admin.login'); @endphp
  <div class="w-full px-4 md:px-6 lg:px-10 h-full flex items-center justify-between gap-4">
    
    <!-- Left: Logo -->
    <div class="flex items-center shrink-0">
      <a href="{{ url('/') }}" class="flex items-center">
        <img src="{{ $finalLogoUrl ?? '/images/logo.png' }}" onerror="this.src='/logo.png'" alt="{{ config('app.name', '2DAWN') }}" class="h-10 w-auto mix-blend-multiply object-contain" style="transform: scale(2.5); transform-origin: left center;">
      </a>
    </div>

    @unless($logoOnly)
    <!-- Right: Desktop Navigation -->
    <div class="hidden lg:flex items-center gap-1 shrink-0">
      @unless (request()->routeIs('host.*'))
      <nav class="flex items-center text-[16px] font-normal text-eventbrite-dark">
        @if(!auth()->check() || (!auth()->user()->is_admin && !auth()->user()->is_organizer))
            <a href="{{ route('events.index') }}" class="px-3.5 py-2 hover:text-tix-orange transition-colors">Discover events</a>
            <a href="{{ route('events.recent') }}" class="px-3.5 py-2 hover:text-tix-orange transition-colors">Find my tickets</a>
        @endif
        
        @auth
            @if(auth()->user()->is_organizer)
                <a href="{{ route('organizer.dashboard') }}" class="px-3.5 py-2 hover:text-tix-orange transition-colors">Dashboard</a>
            @elseif(auth()->user()->is_admin)
                <a href="{{ route('admin.dashboard') }}" class="px-3.5 py-2 hover:text-tix-orange transition-colors">Admin</a>
            @else
                <a href="{{ route('organizer.login') }}" class="px-3.5 py-2 hover:text-tix-orange transition-colors">Create event</a>
            @endif
            
            <form method="POST" action="{{ route('logout') }}" class="inline ml-2">
                @csrf
                <button type="submit" class="text-xs font-bold text-gray-400 hover:text-red-500 uppercase tracking-wider px-2 py-1">Logout</button>
            </form>
        @else
            <a href="{{ route('organizer.login') }}" class="px-3.5 py-2 hover:text-tix-orange transition-colors">Create event</a>
        @endauth
      </nav>
      @endunless
    </div>

    <!-- Mobile Nav Controls -->
    <div class="flex lg:hidden items-center gap-2">
      <button type="button" class="p-2 text-[#1e0a3c] rounded-full transition-colors" aria-label="Open menu" @click="open=true">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
      </button>
    </div>
  </div>
  @endunless

  @unless($logoOnly)
  <!-- Overlay -->
  <div x-cloak x-show="open" x-transition.opacity class="fixed inset-0 bg-white/90 backdrop-blur-xl z-[110] lg:hidden" @click="open=false" aria-hidden="true"></div>

  <!-- Mobile Drawer -->
  <aside x-cloak x-show="open" x-transition:enter="transition transform ease-out duration-300" x-transition:enter-start="-translate-y-full" x-transition:enter-end="translate-y-0" x-transition:leave="transition transform ease-in duration-200" x-transition:leave-start="translate-y-0" x-transition:leave-end="-translate-y-full" class="fixed inset-x-0 top-0 w-full bg-white shadow-2xl z-[120] flex flex-col lg:hidden max-h-screen overflow-y-auto">
    
    <!-- Drawer Header -->
    <div class="flex items-center justify-between p-4 border-b border-eventbrite-gray-100">
      <span class="text-lg font-bold text-eventbrite-dark">Menu</span>
      <button type="button" class="p-2 text-eventbrite-gray-400 hover:bg-eventbrite-gray-50 rounded-full transition-colors" aria-label="Close menu" @click="open=false">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
      </button>
    </div>
    
    <!-- Drawer Content -->
    <div class="flex-1 overflow-y-auto py-2 px-0">
    @if (request()->routeIs('host.*'))
      <nav class="grid text-[16px] text-eventbrite-dark">
        @if(isset($host))
          <a href="{{ route('host.scan', $host->token) }}" class="px-6 py-4 hover:bg-eventbrite-gray-50 transition-colors" @click="open=false">Scanner</a>
          <a href="{{ route('host.panel', $host->token) }}#recent-card" class="px-6 py-4 hover:bg-eventbrite-gray-50 transition-colors" @click="open=false">Recent scans</a>
          <a href="{{ route('host.people', $host->token) }}" class="px-6 py-4 hover:bg-eventbrite-gray-50 transition-colors" @click="open=false">People</a>
          <a href="{{ route('host.sales.export', $host->token) }}" class="px-6 py-4 hover:bg-eventbrite-gray-50 transition-colors" @click="open=false">Export sales</a>
          <a href="{{ route('host.sales.exportDaily', $host->token) }}" class="px-6 py-4 hover:bg-eventbrite-gray-50 transition-colors" @click="open=false">Daily sales</a>
          <a href="{{ route('host.people.export', $host->token) }}" class="px-6 py-4 hover:bg-eventbrite-gray-50 transition-colors" @click="open=false">Check-ins CSV</a>
        @endif
        @if(isset($event) && $event?->public_url)
          <a href="{{ $event->public_url }}" class="px-6 py-4 hover:bg-eventbrite-gray-50 transition-colors" @click="open=false">View event</a>
        @endif
      </nav>
    @else
      <nav class="grid text-[16px] text-eventbrite-dark">
        @if(!auth()->check() || (!auth()->user()->is_admin && !auth()->user()->is_organizer))
            <a href="{{ route('events.index') }}" class="px-6 py-4 hover:bg-eventbrite-gray-50 transition-colors border-b border-eventbrite-gray-50" @click="open=false">Discover events</a>
            <a href="{{ route('events.recent') }}" class="px-6 py-4 hover:bg-eventbrite-gray-50 transition-colors border-b border-eventbrite-gray-50" @click="open=false">Find my tickets</a>
        @endif
        
        @auth
            @if(auth()->user()->is_organizer)
                <a href="{{ route('organizer.dashboard') }}" class="px-6 py-4 hover:bg-eventbrite-gray-50 transition-colors" @click="open=false">Dashboard</a>
            @else
                <a href="{{ route('organizer.login') }}" class="px-6 py-4 hover:bg-eventbrite-gray-50 transition-colors" @click="open=false">Create event</a>
            @endif
            <form method="POST" action="{{ route('logout') }}" class="contents">
                @csrf
                <button type="submit" class="w-full text-left px-6 py-4 text-red-500 font-bold hover:bg-red-50 transition-colors">Logout</button>
            </form>
        @else
            <a href="{{ route('organizer.login') }}" class="px-6 py-4 hover:bg-eventbrite-gray-50 transition-colors border-b border-eventbrite-gray-50" @click="open=false">Create event</a>
        @endauth
      </nav>
    @endif
    </div>
  </aside>
  @endunless
</header>

