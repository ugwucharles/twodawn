<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', '2DAWN') }} - Auth</title>

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
        @font-face {
            font-family: 'Taskor';
            src: url('/fonts/Taskor.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        :root {
            --font-ui: 'Montserrat', ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, 'Helvetica Neue', Arial, 'Noto Sans', 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';
        }
    </style>
</head>
<body class="bg-[#8b5cf6]/30 min-h-screen flex items-center justify-center p-4" style="font-family: var(--font-ui);">
    @yield('content')
</body>
</html>
