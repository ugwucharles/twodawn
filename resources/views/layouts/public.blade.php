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
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@600;700&display=swap" rel="stylesheet">

    @php
      $manifestPath = public_path('build/manifest.json');
      $cssHref = null; $jsSrc = null;
      if (file_exists($manifestPath)) {
          $manifest = json_decode(file_get_contents($manifestPath), true);
          if (isset($manifest['resources/css/app.css']['file'])) {
              $cssHref = asset('build/' . $manifest['resources/css/app.css']['file']);
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
      :root{ --font-ui: 'Manrope', ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, 'Helvetica Neue', Arial, 'Noto Sans', 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; }
      body{ font-family: var(--font-ui); }
    </style>
  </head>
  <body class="antialiased bg-black text-white min-h-screen flex flex-col">
    @php $showPublicHeader = request()->routeIs('home'); @endphp
    @if ($showPublicHeader)
      @include('partials.public-header')
    @endif
    <main class="flex-1 {{ $showPublicHeader ? 'pt-20' : '' }}">
      @yield('content')
    </main>
    @include('partials.footer')
    <script>
(() => {
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const cards = Array.from(document.querySelectorAll('[data-tilt]'));
  if (!cards.length || prefersReducedMotion) return;

  const MAX_DEFAULT = 6; // degrees
  const state = new WeakMap();
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
