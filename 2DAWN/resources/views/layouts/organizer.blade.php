<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', '2DAWN') }} Organizer</title>

        <!-- Font & Assets -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Outfit:wght@100..900&display=swap" rel="stylesheet">

        @php
            $manifestPath = public_path('build/manifest.json');
            $cssHref = null; $jsSrc = null;
            if (file_exists($manifestPath)) {
                $manifest = json_decode(file_get_contents($manifestPath), true);
                if (isset($manifest['resources/css/app.css']['file'])) {
                    $cssHref = asset('build/' . $manifest['resources/css/app.css']['file']);
                } elseif (!empty($manifest['resources/js/app.js']['css'][0])) {
                    $cssFromJs = $manifest['resources/js/app.js']['css'][0];
                    $cssHref = asset('build/' . ltrim($cssFromJs, '/'));
                }
                if (isset($manifest['resources/js/app.js']['file'])) {
                    $jsSrc = asset('build/' . $manifest['resources/js/app.js']['file']);
                }
            }
        @endphp
        @if($cssHref)
            <link rel="stylesheet" href="{{ $cssHref }}">
        @endif
        @if($jsSrc)
            <script type="module" src="{{ $jsSrc }}"></script>
        @endif
        
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

        <style>
            @font-face {
                font-family: 'Taskor';
                src: url('/fonts/Taskor.ttf') format('truetype');
                font-weight: normal;
                font-style: normal;
            }

            :root { 
                --font-ui: 'Montserrat', ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, 'Helvetica Neue', Arial, 'Noto Sans', 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';
                --accent-pink: #8b5cf6;
                --accent-pink-light: rgba(139, 92, 246, 0.15);
                --bg-sidebar: #000000;
                --bg-main: #FFFFFF;
            }
            body { 
                font-family: var(--font-ui); 
                background-color: var(--bg-main); 
                color: #1A1C1E;
            }
            [x-cloak] { display:none !important; }
            
            /* Sidebar active state matching EventTum */
            .sidebar-link.active {
                background-color: var(--accent-pink-light);
                color: var(--accent-pink);
                border-radius: 12px;
                border-right: 3px solid var(--accent-pink);
            }
            .sidebar-link.active svg {
                color: var(--accent-pink);
            }
        </style>
    </head>
    <body class="antialiased selection:bg-gray-200 selection:text-black">
        <div class="flex h-screen overflow-hidden">
            <!-- Sidebar -->
            <aside class="w-64 bg-black hidden lg:flex flex-col border-r border-zinc-800 shrink-0">
                <!-- Logo -->
                <div class="p-8 flex items-center gap-2">
                    <a href="{{ url('/') }}" class="flex items-center">
                        <span class="inline-flex items-center font-black text-2xl tracking-tighter select-none" style="font-family: 'Taskor', sans-serif;">
                            <span style="color: #ffffff; margin-right: 2px;">2</span>
                            <span style="color: #8b5cf6;">DAWN</span>
                        </span>
                    </a>
                </div>
                
                <!-- Nav Groups -->
                <nav class="flex-1 px-4 space-y-8 overflow-y-auto">
                    <div>
                        <p class="px-4 text-[10px] font-bold text-zinc-500 uppercase tracking-widest mb-4">Main Menu</p>
                        <ul class="space-y-1">
                            <li>
                                <a href="{{ route('organizer.dashboard') }}" class="sidebar-link group flex items-center px-4 py-3 text-sm font-bold transition-all {{ request()->routeIs('organizer.dashboard') ? 'active' : 'text-zinc-400 hover:text-white hover:bg-zinc-900 rounded-xl' }}">
                                    <svg class="w-5 h-5 mr-3 transition-colors {{ request()->routeIs('organizer.dashboard') ? '' : 'text-zinc-400 group-hover:text-zinc-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                                    Dashboard
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('organizer.events.index') }}" class="sidebar-link group flex items-center px-4 py-3 text-sm font-bold transition-all {{ request()->routeIs('organizer.events.*') ? 'active' : 'text-zinc-400 hover:text-white hover:bg-zinc-900 rounded-xl' }}">
                                    <svg class="w-5 h-5 mr-3 transition-colors {{ request()->routeIs('organizer.events.*') ? '' : 'text-zinc-400 group-hover:text-zinc-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                    Events
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('organizer.orders.index') }}" class="sidebar-link group flex items-center px-4 py-3 text-sm font-bold transition-all {{ request()->routeIs('organizer.orders.*') ? 'active' : 'text-zinc-400 hover:text-white hover:bg-zinc-900 rounded-xl' }}">
                                    <svg class="w-5 h-5 mr-3 transition-colors {{ request()->routeIs('organizer.orders.*') ? '' : 'text-zinc-400 group-hover:text-zinc-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                    Orders
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('organizer.scanner.index') }}" class="sidebar-link group flex items-center px-4 py-3 text-sm font-bold transition-all {{ request()->routeIs('organizer.scanner.*') ? 'active' : 'text-zinc-400 hover:text-white hover:bg-zinc-900 rounded-xl' }}">
                                    <svg class="w-5 h-5 mr-3 transition-colors {{ request()->routeIs('organizer.scanner.*') ? '' : 'text-zinc-400 group-hover:text-zinc-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                                    Scanner
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div>
                        <p class="px-4 text-[10px] font-bold text-zinc-500 uppercase tracking-widest mb-4">Other</p>
                        <ul class="space-y-1">
                            <li>
                                <a href="{{ route('organizer.wallet.index') }}" class="sidebar-link group flex items-center px-4 py-3 text-sm font-bold transition-all {{ request()->routeIs('organizer.wallet.*') ? 'active' : 'text-zinc-400 hover:text-white hover:bg-zinc-900 rounded-xl' }}">
                                    <svg class="w-5 h-5 mr-3 transition-colors {{ request()->routeIs('organizer.wallet.*') ? '' : 'text-zinc-400 group-hover:text-zinc-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                    Wallet
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('organizer.settings.edit') }}" class="sidebar-link group flex items-center px-4 py-3 text-sm font-bold transition-all {{ request()->routeIs('organizer.settings.*') ? 'active' : 'text-zinc-400 hover:text-white hover:bg-zinc-900 rounded-xl' }}">
                                    <svg class="w-5 h-5 mr-3 transition-colors {{ request()->routeIs('organizer.settings.*') ? '' : 'text-zinc-400 group-hover:text-zinc-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    Settings
                                </a>
                            </li>
                        </ul>
                    </div>
                </nav>
            </aside>

            <!-- Main Content Container -->
            <div class="flex-1 flex flex-col min-w-0 bg-white overflow-hidden">
                <!-- Top Header -->
                <header class="h-20 bg-black flex items-center justify-between px-4 lg:px-12 shrink-0">
                    <div class="flex items-center gap-3">
                        <a href="{{ url('/') }}" class="flex items-center lg:hidden">
                            <span class="inline-flex items-center font-black text-xl lg:text-2xl tracking-tighter select-none" style="font-family: 'Taskor', sans-serif;">
                                <span style="color: #ffffff; margin-right: 2px;">2</span>
                                <span style="color: #8b5cf6;">DAWN</span>
                            </span>
                        </a>
                        <h2 class="text-xl lg:text-2xl font-black text-white tracking-tight hidden sm:block lg:hidden">Dashboard</h2>
                    </div>

                    <div class="flex items-center gap-2 lg:gap-6">
                        <!-- User Profile Card with Dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" 
                                    @click.outside="open = false"
                                    class="flex items-center gap-3 bg-white pl-1 pr-4 py-1 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-all">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=EBF4FF&color=4299E1" 
                                     alt="{{ Auth::user()->name }}" 
                                     class="w-8 h-8 rounded-xl object-cover">
                                <div class="hidden sm:block">
                                    <p class="text-xs font-black text-gray-900 leading-tight">{{ Auth::user()->name }}</p>
                                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-tighter">Organizer</p>
                                </div>
                                <svg class="w-4 h-4 text-black ml-1 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>

                            <!-- Dropdown Menu -->
                            <div x-show="open"
                                 x-transition
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-2xl shadow-xl border border-gray-100 py-2 z-50">
                                <!-- Mobile-only navigation links -->
                                <a href="{{ route('organizer.dashboard') }}" class="block lg:hidden px-4 py-3 text-sm font-bold text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                                        Dashboard
                                    </div>
                                </a>
                                <a href="{{ route('organizer.events.index') }}" class="block lg:hidden px-4 py-3 text-sm font-bold text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                        Events
                                    </div>
                                </a>
                                <a href="{{ route('organizer.orders.index') }}" class="block lg:hidden px-4 py-3 text-sm font-bold text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                        Orders
                                    </div>
                                </a>
                                <a href="{{ route('organizer.scanner.index') }}" class="block lg:hidden px-4 py-3 text-sm font-bold text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                                        Scanner
                                    </div>
                                </a>
                                <div class="border-t border-gray-100 my-2 lg:hidden"></div>
                                <a href="{{ route('organizer.wallet.index') }}" class="block px-4 py-3 text-sm font-bold text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                        Wallet
                                    </div>
                                </a>
                                <a href="{{ route('organizer.settings.edit') }}" class="block px-4 py-3 text-sm font-bold text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                        Settings
                                    </div>
                                </a>
                                <div class="border-t border-gray-100 my-2"></div>
                                <form method="POST" action="{{ route('organizer.logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-3 text-sm font-bold text-red-600 hover:bg-red-50 transition-colors">
                                        <div class="flex items-center gap-3">
                                            <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                            Logout
                                        </div>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Scrollable Body -->
                <main class="flex-1 overflow-y-auto w-full px-4 lg:px-12 pb-12 custom-scrollbar">
                    {{ $slot }}
                </main>
            </div>
        </div>

        <style>
            .custom-scrollbar::-webkit-scrollbar { width: 6px; }
            .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
            .custom-scrollbar::-webkit-scrollbar-thumb { background: #E2E8F0; border-radius: 10px; }
            .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #CBD5E0; }
        </style>

        <!-- Flash messages logic -->
        @if (session('status'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
             class="fixed bottom-4 right-4 bg-green-900 text-white px-6 py-4 rounded-3xl shadow-2xl z-[100] border border-green-800 flex items-center gap-3 animate-slide-up">
            <div class="w-8 h-8 bg-green-800 rounded-xl flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <span class="text-sm font-bold">{{ session('status') }}</span>
        </div>
        @endif
    </body>
</html>

