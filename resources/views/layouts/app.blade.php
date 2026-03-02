<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', '2DAWN') }}</title>

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
        <style>
            :root{ --font-ui: 'Inter', ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, 'Helvetica Neue', Arial, 'Noto Sans', 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; }
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
        <div class="min-h-screen bg-[#f8f7fa] text-eventbrite-dark">
            @include('partials.admin-header')

            <!-- Page Heading -->
            @isset($header)
                <header class="border-b border-eventbrite-gray-100 bg-[#f8f7fa]">
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

        @php
          $chatPref = trim($__env->yieldContent('chat', 'on'));
          $chatOk = config('chat.enabled') && strtolower($chatPref) !== 'off' && config('chat.provider') === 'crisp' && config('chat.crisp_website_id');
        @endphp
        @if ($chatOk)
          <script type="text/javascript">window.$crisp=[];window.CRISP_WEBSITE_ID={{ json_encode(config('chat.crisp_website_id')) }};(function(){d=document;s=d.createElement("script");s.src="https://client.crisp.chat/l.js";s.async=1;d.getElementsByTagName("head")[0].appendChild(s);})();</script>
          @auth
            <script>window.$crisp=window.$crisp||[];window.$crisp.push(["set","user:email", {{ json_encode(Auth::user()->email) }} ]);window.$crisp.push(["set","user:nickname", {{ json_encode(Auth::user()->name ?? 'Admin') }} ]);</script>
          @endauth
        @endif
    </body>
</html>
