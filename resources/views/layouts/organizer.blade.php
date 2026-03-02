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
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

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
            :root{ --font-ui: 'Inter', ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, 'Helvetica Neue', Arial, 'Noto Sans', 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; }
            body{ font-family: var(--font-ui); background-color: #f4f5f9; } /* the light grey background from dribbble */
            [x-cloak]{ display:none !important; }
            nav a.bg-blue-500 { color: white !important; }
        </style>
    </head>
    <body class="font-sans antialiased text-gray-900 bg-[#f4f5f9]">
        <div class="min-h-screen flex gap-6">
            <!-- Sidebar -->
            <aside class="w-56 bg-white border-r border-gray-200 hidden md:flex flex-col">
                <div class="h-20 flex items-center px-6">
                    <a href="{{ route('home') }}" class="flex items-center">
                        <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name', '2DAWN') }}" class="h-10 w-auto">
                    </a>
                </div>
                
                <nav class="flex-1 px-4 py-2 space-y-0.5">
                    <a href="{{ route('organizer.dashboard') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-xl {{ request()->routeIs('organizer.dashboard') ? 'bg-blue-500 text-white' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-colors' }}" style="{{ request()->routeIs('organizer.dashboard') ? 'background-color: #3b82f6 !important; color: white !important;' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        Overview
                    </a>
                    
                    <a href="#" class="flex items-center px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900 rounded-xl transition-colors">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        Users
                    </a>
                    
                    <a href="{{ route('organizer.events.create') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-xl {{ request()->routeIs('organizer.events.*') ? 'bg-blue-500 text-white' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-colors' }}" style="{{ request()->routeIs('organizer.events.*') ? 'background-color: #3b82f6 !important; color: white !important;' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Events
                    </a>
                    
                    <a href="#" class="flex items-center px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900 rounded-xl transition-colors">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
                        Tickets
                    </a>
                </nav>
            </aside>

            <!-- Main Content -->
            <div class="flex-1 flex flex-col min-w-0">
                <!-- Top Navbar -->
                <header class="h-20 bg-[#f4f5f9] flex items-center justify-between px-8">
                    <div class="flex-1 flex max-w-2xl">
                        <!-- Search Removed -->
                    </div>

                    <div class="ml-4 flex items-center space-x-6">
                        <button class="w-12 h-12 bg-transparent rounded-full flex items-center justify-center text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-colors">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </button>
                        
                        <button class="w-12 h-12 bg-transparent rounded-full flex items-center justify-center text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-colors">
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"></path></svg>
                        </button>

                        <div class="flex items-center bg-white rounded-full p-1 pr-4 shadow-sm ml-2">
                            <img class="h-8 w-8 rounded-full object-cover" src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=EBF4FF&color=4299E1" alt="{{ Auth::user()->name }}">
                            <div class="ml-2">
                                <p class="text-sm font-semibold text-gray-900 leading-tight">{{ Auth::user()->name }}</p>
                                <p class="text-[10px] font-medium text-gray-500 leading-tight">Organizer</p>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Main Content Area -->
                <main class="flex-1 overflow-y-auto w-full px-8 pb-8 relative bg-[#f4f5f9]">
                    {{ $slot }}
                </main>
            </div>
        </div>
        
        <!-- Flash messages logic -->
        @if (session('status'))
        <div class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            {{ session('status') }}
        </div>
        @endif
    </body>
</html>
