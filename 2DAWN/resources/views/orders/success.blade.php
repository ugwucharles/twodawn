@extends('layouts.public')

@section('content')
<section class="py-10">
  <div class="max-w-3xl mx-auto px-6">
    {{-- Removed Home/All events links on success page per request --}}

    @php
      $event = $order->event;
      $payload = $order->paystack_reference; // scanned by admin
      try {
        $renderer = new \BaconQrCode\Renderer\ImageRenderer(new \BaconQrCode\Renderer\RendererStyle\RendererStyle(300), new \BaconQrCode\Renderer\Image\SvgImageBackEnd());
        $writer = new \BaconQrCode\Writer($renderer);
        $svg = $writer->writeString($payload);
        $qrData = 'data:image/svg+xml;base64,' . base64_encode($svg);
      } catch (\Throwable $e) { $qrData = null; }
    @endphp

    <div class="rounded-3xl bg-white/5 ring-1 ring-white/10 p-6">
      <h1 class="text-2xl font-extrabold">{{ ($order->amount ?? 0) <= 0 ? 'Ticket Confirmed' : 'Payment Successful' }}</h1>
      <p class="mt-2 text-zinc-300">Show this QR at the gate. It encodes your order reference.</p>
      <p class="mt-2 text-zinc-400 text-sm">Check your email for your tickets. If you can't find it, check your spam folder.</p>
      <div class="mt-3 text-sm rounded-lg bg-yellow-500/10 text-yellow-300 ring-1 ring-yellow-500/20 p-3">
        <strong>Keep it safe:</strong> Do not share your QR code or order reference. It grants entry and can be used once.
      </div>

      <div class="mt-6 grid sm:grid-cols-2 gap-6 items-start">
        <div class="rounded-xl bg-white p-4 flex items-center justify-center">
          @if($qrData)
            <img src="{{ $qrData }}" alt="Order QR" class="w-64 h-64"/>
          @else
            <div class="text-sm text-zinc-500">QR unavailable</div>
          @endif
        </div>
        <div class="text-sm">
          <div class="text-zinc-400">Order Ref</div>
          <div class="font-mono text-lg">{{ $order->paystack_reference }}</div>
          <div class="mt-4 text-zinc-400">Event</div>
          <div class="text-white">{{ $event?->title ?? '—' }}</div>
          <div class="mt-4 text-zinc-400">Buyer</div>
          <div class="text-white">{{ $order->buyer_name }} <span class="text-zinc-400">({{ $order->buyer_email }})</span></div>
          @if($order->ticket_type)
          <div class="mt-4 text-zinc-400">Ticket Type</div>
          <div class="text-white font-bold">{{ $order->ticket_type }}</div>
          @endif
          <div class="mt-4 text-zinc-400">Quantity</div>
          <div class="text-white">{{ $order->quantity }}</div>
        </div>
        @if($event)
        <div>
          @php
            $start = $event->starts_at;
            $end = $event->ends_at ?? ($start ? $start->copy()->addHours(2) : null);
            $startUtc = $start ? $start->copy()->utc()->format('Ymd\THis\Z') : null;
            $endUtc = $end ? $end->copy()->utc()->format('Ymd\THis\Z') : null;
            $gc = $startUtc && $endUtc ? ('https://calendar.google.com/calendar/render?action=TEMPLATE&text=' . urlencode($event->title ?? 'Event') . '&dates='.$startUtc.'/'.$endUtc.'&details=' . urlencode($event->public_url ?? url('/')) . ($event->venue ? ('&location='.urlencode($event->venue)) : '')) : null;
          @endphp
          <div class="mt-6">
            <h2 class="text-sm uppercase tracking-widest text-zinc-400">Promote this event</h2>
            <div class="mt-2 flex flex-wrap items-center gap-2 text-sm">
              <a href="https://wa.me/?text={{ urlencode(($event->title ?? 'Event').' — '.$event->public_url) }}" target="_blank" class="px-3 py-1.5 rounded-full bg-white/10 ring-1 ring-white/10 hover:bg-white/20">WhatsApp</a>
              <a href="https://twitter.com/intent/tweet?text={{ urlencode($event->title ?? 'Event') }}&url={{ urlencode($event->public_url) }}" target="_blank" class="px-3 py-1.5 rounded-full bg-white/10 ring-1 ring-white/10 hover:bg-white/20">Share on X</a>
              <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($event->public_url) }}" target="_blank" class="px-3 py-1.5 rounded-full bg-white/10 ring-1 ring-white/10 hover:bg-white/20">Facebook</a>
              <button id="copy-link" class="px-3 py-1.5 rounded-full bg-white/10 ring-1 ring-white/10 hover:bg-white/20" data-url="{{ $event->public_url }}">Copy link</button>
            </div>
          </div>
          <div class="mt-6">
            <h2 class="text-sm uppercase tracking-widest text-zinc-400">Set reminder</h2>
            <div class="mt-2 flex flex-wrap items-center gap-2 text-sm">
              <button id="set-reminder" class="px-3 py-1.5 rounded-full bg-white text-black hover:bg-zinc-100">Set reminder</button>
            </div>
          </div>

          <script>
            document.getElementById('set-reminder')?.addEventListener('click', function(){
              var ua = navigator.userAgent || '';
              var isiOS = /iP(hone|ad|od)/i.test(ua);
              var isMac = /Macintosh/.test(ua);
              var isAndroid = /Android/i.test(ua);
              var icsUrl = @json(route('events.ics', $event) . '?alarm=60');
              var gcal = @json($gc);
              if (isiOS || isMac) {
                window.location.href = icsUrl;
              } else if (isAndroid && gcal) {
                window.open(gcal, '_blank');
              } else {
                if (gcal) { window.open(gcal, '_blank'); } else { window.location.href = icsUrl; }
              }
            });
          </script>
        </div>
        @endif
      </div>
    </div>

    <script>
      document.getElementById('copy-link')?.addEventListener('click', async (e) => {
        const url = e.currentTarget.getAttribute('data-url');
        try { await navigator.clipboard.writeText(url); e.currentTarget.textContent = 'Copied!'; setTimeout(()=>e.currentTarget.textContent='Copy link', 1500);} catch(_) { alert(url); }
      });
    </script>
  </div>
</section>
<script>
  (function(){
    try {
      if (!window.gtag) return;
      const payload = {
        transaction_id: @json($order->paystack_reference),
        value: {{ number_format($order->amount/100, 2, '.', '') }},
        currency: 'NGN',
        items: [{
          item_id: 'event_{{ $event?->id }}',
          item_name: @json($event?->title ?? 'Ticket'),
          item_category: 'Event',
          quantity: {{ (int) $order->quantity }},
          price: {{ number_format(($order->amount/100)/max(1,$order->quantity), 2, '.', '') }},
        }]
      };
      window.gtag('event', 'purchase', payload);
    } catch (e) {}
  })();
</script>
@endsection
