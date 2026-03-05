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

        <style>
            :root { 
                --font-ui: 'Montserrat', ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, 'Helvetica Neue', Arial, 'Noto Sans', 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';
                --accent-pink: #FF6B81;
                --accent-pink-light: #FFF0F2;
                --bg-sidebar: #FFFFFF;
                --bg-main: #F4F7FE;
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
    <body class="antialiased selection:bg-pink-100 selection:text-pink-600">
        <div class="flex h-screen overflow-hidden">
            <!-- Sidebar -->
            <aside class="w-64 bg-white hidden lg:flex flex-col border-r border-gray-100 shrink-0">
                <!-- Logo -->
                <div class="p-8 flex items-center gap-2">
                    <div class="w-8 h-8 bg-black rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                    </div>
                    <span class="text-xl font-black tracking-tight uppercase">EvenTum</span>
                </div>
                
                <!-- Nav Groups -->
                <nav class="flex-1 px-4 space-y-8 overflow-y-auto">
                    <div>
                        <p class="px-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-4">Main Menu</p>
                        <ul class="space-y-1">
                            <li>
                                <a href="{{ route('organizer.dashboard') }}" class="sidebar-link group flex items-center px-4 py-3 text-sm font-bold transition-all {{ request()->routeIs('organizer.dashboard') ? 'active' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50 rounded-xl' }}">
                                    <svg class="w-5 h-5 mr-3 transition-colors {{ request()->routeIs('organizer.dashboard') ? 'text-pink-500' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                                    Dashboard
                                </a>
                            </li>
                            <li>
                                <a href="#" class="sidebar-link group flex items-center px-4 py-3 text-sm font-bold text-gray-500 hover:text-gray-900 hover:bg-gray-50 rounded-xl transition-all">
                                    <svg class="w-5 h-5 mr-3 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    Calendar
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('organizer.events.create') }}" class="sidebar-link group flex items-center px-4 py-3 text-sm font-bold transition-all {{ request()->routeIs('organizer.events.*') ? 'active' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50 rounded-xl' }}">
                                    <svg class="w-5 h-5 mr-3 transition-colors {{ request()->routeIs('organizer.events.*') ? 'text-pink-500' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                    Events
                                </a>
                            </li>
                            <li>
                                <a href="#" class="sidebar-link group flex items-center px-4 py-3 text-sm font-bold text-gray-500 hover:text-gray-900 hover:bg-gray-50 rounded-xl transition-all">
                                    <svg class="w-5 h-5 mr-3 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                    Orders
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div>
                        <p class="px-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-4">Other</p>
                        <ul class="space-y-1">
                            <li>
                                <a href="#" class="sidebar-link group flex items-center px-4 py-3 text-sm font-bold text-gray-500 hover:text-gray-900 hover:bg-gray-50 rounded-xl transition-all">
                                    <svg class="w-5 h-5 mr-3 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    Settings
                                </a>
                            </li>
                        </ul>
                    </div>
                </nav>

                <!-- Help Banner (matching EventTum "Upgrade to better plan") -->
                <div class="p-4 mx-4 mb-8 bg-gray-900 rounded-3xl relative overflow-hidden">
                    <div class="relative z-10">
                        <p class="text-white text-xs font-bold mb-1">Need help?</p>
                        <p class="text-gray-400 text-[10px] mb-3">Our support team is here for you 24/7.</p>
                        <a href="#" class="inline-block bg-white text-black text-[10px] font-black px-4 py-2 rounded-xl">Get Support</a>
                    </div>
                    <div class="absolute -right-4 -bottom-4 w-16 h-16 bg-white/10 rounded-full blur-xl"></div>
                </div>
            </aside>

            <!-- Main Content Container -->
            <div class="flex-1 flex flex-col min-w-0 bg-[#F4F7FE] overflow-hidden">
                <!-- Top Header -->
                <header class="h-20 flex items-center justify-between px-4 lg:px-12 shrink-0">
                    <div>
                        <h2 class="text-xl lg:text-2xl font-black text-gray-900 tracking-tight">Dashboard</h2>
                    </div>

                    <div class="flex items-center gap-2 lg:gap-6">
                        <!-- Theme Toggle / Icons -->
                        <div class="hidden md:flex items-center gap-2">
                            <button class="w-10 h-10 flex items-center justify-center text-gray-400 hover:bg-white hover:shadow-sm rounded-xl transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                            </button>
                            <button class="w-10 h-10 flex items-center justify-center text-gray-400 hover:bg-white hover:shadow-sm rounded-xl transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                            </button>
                        </div>

                        <!-- User Profile Card -->
                        <div class="flex items-center gap-3 bg-white pl-1 pr-4 py-1 rounded-2xl shadow-sm border border-gray-100">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=EBF4FF&color=4299E1" 
                                 alt="{{ Auth::user()->name }}" 
                                 class="w-8 h-8 rounded-xl object-cover">
                            <div class="hidden sm:block">
                                <p class="text-xs font-black text-gray-900 leading-tight">{{ Auth::user()->name }}</p>
                                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-tighter">Organizer</p>
                            </div>
                            <svg class="w-4 h-4 text-gray-400 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </header>

                <!-- Scrollable Body -->
                <main class="flex-1 overflow-y-auto w-full px-4 lg:px-12 pb-12 custom-scrollbar">
                    {{ $slot }}
                </main>
            </div>
        </div>

        <!-- Mobile Nav Trigger (Simple Implementation) -->
        <button class="lg:hidden fixed bottom-6 right-6 w-14 h-14 bg-black text-white rounded-full shadow-2xl z-50 flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
        </button>

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
