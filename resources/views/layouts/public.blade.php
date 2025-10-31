<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
      $tenant = $tenant ?? (View::shared('tenant') ?? null);
      $appName = ($tenant->name ?? null) ?: config('app.name', '2DAWN');
      $seoTitle = trim($__env->yieldContent('title', $appName));
      $seoDesc = trim($__env->yieldContent('meta_description', 'Find the Night. Curated events and seamless tickets.'));
      $seoCanon = trim($__env->yieldContent('canonical', url()->current()));
      $seoImage = trim($__env->yieldContent('meta_image', asset('favicon.ico')));
      $seoRobots = trim($__env->yieldContent('robots', 'index, follow'));
      $ogType = trim($__env->yieldContent('og:type', 'website'));
      $ogUpdated = trim($__env->yieldContent('og:updated_time', now()->toAtomString()));
      $ogLocale = env('APP_OG_LOCALE', 'en_NG');
      $twitterSite = env('TWITTER_SITE');
    @endphp
    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ \Illuminate\Support\Str::limit($seoDesc, 160, '') }}">
    <meta name="robots" content="{{ $seoRobots }}">
    <link rel="canonical" href="{{ $seoCanon }}">
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:site_name" content="{{ $appName }}">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ \Illuminate\Support\Str::limit($seoDesc, 200, '') }}">
    <meta property="og:url" content="{{ $seoCanon }}">
    <meta property="og:image" content="{{ $seoImage }}">
    <meta property="og:image:alt" content="{{ $seoTitle }}">
    <meta property="og:updated_time" content="{{ $ogUpdated }}">
    <meta property="og:locale" content="{{ $ogLocale }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ \Illuminate\Support\Str::limit($seoDesc, 200, '') }}">
    <meta name="twitter:image" content="{{ $seoImage }}">
    @if ($twitterSite)
      <meta name="twitter:site" content="{{ $twitterSite }}">
    @endif
    <meta name="theme-color" content="#00000000" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#00000000" media="(prefers-color-scheme: dark)">
    <meta name="color-scheme" content="light dark">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    @if (env('GOOGLE_SITE_VERIFICATION'))
      <meta name="google-site-verification" content="{{ env('GOOGLE_SITE_VERIFICATION') }}">
    @endif
    @if (env('BING_SITE_VERIFICATION'))
      <meta name="msvalidate.01" content="{{ env('BING_SITE_VERIFICATION') }}">
    @endif

    @if (env('GA_MEASUREMENT_ID'))
      <script async src="https://www.googletagmanager.com/gtag/js?id={{ env('GA_MEASUREMENT_ID') }}"></script>
      <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);} gtag('js', new Date());
        gtag('config', '{{ env('GA_MEASUREMENT_ID') }}');
      </script>
    @elseif (env('PLAUSIBLE_DOMAIN'))
      <script defer data-domain="{{ env('PLAUSIBLE_DOMAIN') }}" src="https://plausible.io/js/script.js"></script>
    @endif

    <!-- Font & Assets -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@600;700&display=swap" rel="stylesheet">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/Group 2.png') }}">
    <meta name="apple-mobile-web-app-title" content="{{ $appName }}">

    @php
      $manifestPath = public_path('build/manifest.json');
      $cssHref = null; $jsSrc = null;
      if (file_exists($manifestPath)) {
          $manifest = json_decode(file_get_contents($manifestPath), true);
          // Prefer direct CSS entry; fall back to CSS emitted by the JS entry
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
      :root{ --font-ui: 'Manrope', ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, 'Helvetica Neue', Arial, 'Noto Sans', 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; @if(!empty($tenant?->brand_color)) --brand: {{ $tenant->brand_color }}; @endif }
      body{ font-family: var(--font-ui); }
      /* Prevent Alpine FOUC */
      [x-cloak]{ display:none !important; }
      /* Prevent horizontal dragging/scroll on mobile */
      html,body{ width:100%; max-width:100vw; overflow-x:hidden; }
      body{ overscroll-behavior-x:none; touch-action: pan-y; }
      /* Hide scrollbars for mood scroller */
      .no-scrollbar::-webkit-scrollbar{ display:none; }
      .no-scrollbar{ -ms-overflow-style:none; scrollbar-width:none; }
    </style>
    @yield('head_links')
    @yield('jsonld')

    @php
      $orgJson = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => $appName,
        'url' => config('app.url', url('/')),
        'logo' => asset('images/Group 2.png'),
      ];
      $siteJson = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'url' => config('app.url', url('/')),
        'name' => $appName,
        'potentialAction' => [
          '@type' => 'SearchAction',
          'target' => route('events.index', ['q' => '{search_term_string}']),
          'query-input' => 'required name=search_term_string',
        ],
      ];
    @endphp
    <script type="application/ld+json">{!! json_encode($orgJson, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
    <script type="application/ld+json">{!! json_encode($siteJson, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
  </head>
  <body class="antialiased bg-black text-white min-h-screen flex flex-col">
    <main class="flex-1">
      @yield('content')
    </main>
    @include('partials.footer')

    @if (request()->routeIs('home'))
      <script>
        (()=>{
          // Transparent on Android and PWA; dark fallback on iOS Safari browser
          let m = document.querySelector('meta[name="theme-color"][data-dynamic]');
          if (!m) { m = document.createElement('meta'); m.name='theme-color'; m.setAttribute('data-dynamic',''); document.head.appendChild(m); }
          const ua = navigator.userAgent || '';
          const isStandalone = (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) || (window.navigator && window.navigator.standalone === true);
          const isIOS = /iP(hone|ad|od)/.test(ua) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
          const isSafari = /Safari/i.test(ua) && !/CriOS|FxiOS|EdgiOS|OPiOS/i.test(ua);
          const FALLBACK = '#0b0b0b';
          const apply = () => {
            const color = (isIOS && isSafari && !isStandalone) ? FALLBACK : '#00000000';
            m.setAttribute('content', color);
          };
          apply();
          window.addEventListener('scroll', apply, { passive: true });
          window.addEventListener('resize', apply, { passive: true });
          document.addEventListener('visibilitychange', apply);
        })();
      </script>
    @endif

    @php
      $chatPref = trim($__env->yieldContent('chat', 'on'));
      $chatEnabled = config('chat.enabled') && strtolower($chatPref) !== 'off';
      $provider = config('chat.provider');
      $showMode = null;
      if ($provider === 'native' && $chatEnabled) {
        if (request()->routeIs('home')) { $showMode = 'after-host'; }
        if (request()->routeIs('orders.public')) { $showMode = 'always'; }
      }
    @endphp
    @if ($chatEnabled && $provider === 'crisp' && config('chat.crisp_website_id'))
      <script type="text/javascript">window.$crisp=[];window.CRISP_WEBSITE_ID={{ json_encode(config('chat.crisp_website_id')) }};(function(){d=document;s=d.createElement("script");s.src="https://client.crisp.chat/l.js";s.async=1;d.getElementsByTagName("head")[0].appendChild(s);})();</script>
      @if (Auth::check())
        <script>window.$crisp=window.$crisp||[];window.$crisp.push(["set","user:email", {{ json_encode(Auth::user()->email) }} ]);window.$crisp.push(["set","user:nickname", {{ json_encode(Auth::user()->name ?? 'User') }} ]);</script>
      @endif
    @elseif ($provider === 'native' && $showMode)
      @include('partials.chat-widget', ['showMode' => $showMode])
    @endif


    <!-- Global search modal -->
    <x-modal name="search-modal" maxWidth="xl" panelClass="bg-black text-white ring-1 ring-white/10" overlayClass="bg-black/60 backdrop-blur-sm" focusable>
      <form method="GET" action="{{ route('events.index') }}" class="p-6 sm:p-10">
        <div class="text-sm text-zinc-400 mb-3">Search</div>
        <div class="flex items-center gap-3">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-zinc-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.9 14.32a8 8 0 111.414-1.414l3.387 3.387a1 1 0 01-1.414 1.414l-3.387-3.387zM14 8a6 6 0 11-12 0 6 6 0 0112 0z" clip-rule="evenodd"/></svg>
          <input name="q" placeholder="Search events, venues, vibes…" class="flex-1 bg-transparent text-2xl sm:text-3xl focus:outline-none placeholder:text-zinc-500" />
        </div>
        <div class="mt-6 flex items-center justify-end gap-3 text-sm">
          <button type="button" class="px-4 py-2 rounded-full bg-white/5 ring-1 ring-white/10 text-zinc-200 hover:bg-white/10" @click="$dispatch('close-modal', 'search-modal')">Cancel</button>
          <button class="px-5 py-2 rounded-full bg-white text-black font-semibold hover:bg-zinc-100">Search</button>
        </div>
      </form>
    </x-modal>

    <script>
(() => {
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const cards = Array.from(document.querySelectorAll('[data-tilt]'));
  if (!cards.length || prefersReducedMotion) return;

  const MAX_DEFAULT = 6; // degrees
  const state = new Map();
  let rafId = null;

  const clamp = (v, min, max) => Math.max(min, Math.min(max, v));
  const shape = (x, el) => {
    const dzAttr = el.getAttribute('data-tilt-deadzone');
    const dz = dzAttr ? Math.max(0, Math.min(0.4, parseFloat(dzAttr))) : 0.12; // center dead zone (0..0.4)
    let s = clamp(x, -1, 1);
    const sign = s < 0 ? -1 : 1;
    s = Math.abs(s);
    if (s < dz) {
      s = 0;
    } else {
      s = (s - dz) / (1 - dz); // renormalize outside dead zone
    }
    const edgeMax = 0.85; // soft limit to avoid tilting at the edges
    s = Math.min(s, edgeMax);
    // smoothstep easing (0..1)
    s = s * s * (3 - 2 * s);
    return sign * s;
  };

  const setTilt = (el, nx, ny) => {
    const max = parseFloat(el.getAttribute('data-tilt-max') || MAX_DEFAULT);
    const shaped = shape(nx, el);
    const ry = shaped * max;
    el.style.transform = `perspective(900px) rotateX(0deg) rotateY(${ry}deg)`;
  };

  const resetTilt = (el) => {
    el.style.transform = 'perspective(900px) rotateX(0deg) rotateY(0deg)';
  };

  const schedule = () => {
    if (rafId) return;
    rafId = requestAnimationFrame(() => {
      rafId = null;
      for (const [el, s] of state) setTilt(el, s.nx, s.ny);
    });
  };

  // Pointer (desktop / touch drag)
  const onPointerMove = (e) => {
    const el = e.currentTarget;
    const r = el.getBoundingClientRect();
    const x = (e.clientX - r.left) / r.width;  // 0..1
    const y = (e.clientY - r.top) / r.height;  // 0..1
    const nx = (x - 0.5) * 2;                  // -1..1
    const ny = (y - 0.5) * 2;                  // -1..1
    state.set(el, { nx, ny });
    schedule();
  };
  const onLeave = (e) => {
    const el = e.currentTarget;
    state.delete(el);
    el.style.transition = 'transform 250ms ease';
    resetTilt(el);
    setTimeout(() => (el.style.transition = ''), 260);
  };

  cards.forEach((el) => {
    el.style.transformStyle = 'preserve-3d';
    el.style.willChange = 'transform';
    resetTilt(el);
    el.addEventListener('pointermove', onPointerMove, { passive: true });
    el.addEventListener('pointerleave', onLeave, { passive: true });
    el.addEventListener('pointerup', onLeave, { passive: true });
  });

  // Device orientation (mobile)
  const hasMotion = () => 'DeviceOrientationEvent' in window;
  const activateMotion = () => {
    const handler = (ev) => {
      const gamma = ev.gamma || 0; // left/right   -90..90
      const nx = Math.max(-1, Math.min(1, gamma / 30));
      cards.forEach((el) => setTilt(el, nx, 0));
    };
    window.addEventListener('deviceorientation', handler, true);
  };

  // iOS permission (needs a user gesture)
  if (hasMotion() && typeof DeviceOrientationEvent?.requestPermission === 'function') {
    const ask = () => {
      DeviceOrientationEvent.requestPermission()
        .then((res) => { if (res === 'granted') activateMotion(); })
        .catch(() => {})
        .finally(() => {
          window.removeEventListener('click', ask);
          window.removeEventListener('touchstart', ask);
        });
    };
    window.addEventListener('click', ask, { once: true });
    window.addEventListener('touchstart', ask, { once: true, passive: true });
  } else if (hasMotion()) {
    activateMotion();
  }
})();
    </script>
  </body>
</html>
