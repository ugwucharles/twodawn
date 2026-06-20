<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', '2DAWN') }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo_dark.svg') }}">

        <!-- Font & Assets -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

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
        <style>
            :root{ --font-ui: 'Montserrat', ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, 'Helvetica Neue', Arial, 'Noto Sans', 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; }
            body{ font-family: var(--font-ui); }
            /* Prevent Alpine FOUC */
            [x-cloak]{ display:none !important; }
            /* Prevent horizontal dragging/scroll on mobile */
            html,body{ width:100%; max-width:100vw; overflow-x:hidden; }
            body{ overscroll-behavior-x:none; touch-action: pan-y; }
            ::selection{ background:#1e0a3c !important; color:#ffffff !important; }
            ::-moz-selection{ background:#1e0a3c !important; color:#ffffff !important; }
        </style>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-white text-[#111827]">
            @include('partials.admin-header')

            <!-- Page Heading -->
            @isset($header)
                <header class="border-b border-[#E5E7EB] bg-white">
                    <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="pt-6 sm:pt-8 px-4 sm:px-6">
                {{ $slot }}
            </main>
        </div>

        {{-- Chat/support widget removed --}}
    </body>
</html>
