@extends('layouts.public')

@section('content')
<section class="py-10">
  <div class="max-w-3xl mx-auto px-6">
    <div class="mb-6 flex items-center justify-between print:hidden">
      <a href="{{ url('/') }}" class="inline-flex items-center px-4 py-2 rounded-full bg-white text-black text-sm font-semibold hover:bg-zinc-100 transition">Home</a>
      <a href="{{ route('events.index') }}" class="inline-flex items-center px-4 py-2 rounded-full bg-white/10 ring-1 ring-white/15 text-white text-sm hover:bg-white/15 transition">All Events</a>
    </div>

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
      <h1 class="text-2xl font-extrabold">Payment Successful</h1>
      <p class="mt-2 text-zinc-300">Show this QR at the gate. It encodes your order reference.</p>

      <div class="mt-6 grid sm:grid-cols-2 gap-6 items-center">
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
          <div class="mt-4 text-zinc-400">Quantity</div>
          <div class="text-white">{{ $order->quantity }}</div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
