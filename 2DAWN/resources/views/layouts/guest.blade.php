<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', '2DAWN') }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo_dark.svg') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Assets via Vite manifest (no @vite) -->
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
            /* Prevent Alpine FOUC */
            [x-cloak]{ display:none !important; }
            /* Prevent horizontal dragging/scroll on mobile */
            html,body{ width:100%; max-width:100vw; overflow-x:hidden; }
            body{ overscroll-behavior-x:none; touch-action: pan-y; }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div>
                <a href="/">
                    <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
