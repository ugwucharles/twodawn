@extends('layouts.public')

@section('content')
<section class="py-10">
  <div class="max-w-3xl mx-auto px-6">
    <div class="mb-6 flex items-center justify-between print:hidden">
      <a href="{{ url('/') }}" class="inline-flex items-center px-4 py-2 rounded-full bg-white text-black text-sm font-semibold hover:bg-zinc-100 transition">Home</a>
      <div class="flex items-center gap-2">
        <a href="{{ route('orders.pdf', $order->paystack_reference) }}" class="inline-flex items-center px-4 py-2 rounded-full bg-white/10 ring-1 ring-white/15 text-white text-sm hover:bg-white/15 transition">Download PDF</a>
        <button type="button" onclick="downloadTicketsAsPng()" class="inline-flex items-center px-4 py-2 rounded-full bg-white text-black text-sm font-semibold hover:bg-zinc-100 transition">Download PNG</button>
      </div>
    </div>

    <style>
      .ticket{ border:1px solid rgba(255,255,255,0.12); border-radius:16px; overflow:hidden; }
.ticket-table{ width:100%; border-collapse:collapse; border-spacing:0; table-layout:fixed; }
      .ticket-table td{ padding:0; vertical-align:middle; }
.brand-panel{ position:relative; height:160pt; color:#fff; background:#0b0b0b; }
      .brand-inner{ padding:8px 12px 0 30px; height:100%; display:flex; align-items:flex-start; }
      .brand-name{ font-weight:800; font-size:22pt; letter-spacing:.8pt; color: {{ $brandColor ?? '#818cf8' }}; font-family:'Montserrat','Manrope',ui-sans-serif,system-ui,sans-serif; }
      .brand-name .brand-num{ color:#fff; }
.amp-line{ margin-top:3pt; padding-left:27px; font-weight:800; font-size:14pt; color:rgba(255,255,255,0.9); letter-spacing:1pt; }
.flyer-cell{ background:#0b0b0b; text-align:left; overflow:hidden; }
.flyer-img{ display:block; width:100%; max-width:none; height:160pt; object-fit:cover; object-position:center center; transform:none; background:#0b0b0b; }
.qr-panel{ background:#FAFAFA; height:160pt; position:relative; overflow:hidden; }
      .qr-mini{ position:absolute; top:8pt; right:8pt; width:54pt; height:54pt; background:#fff; border:1px solid #e5e7eb; border-radius:6pt; object-fit:contain; }
.qr-vert{ position:absolute; top:50%; right:4pt; transform:translateY(-50%); }
    </style>

    <style>
      /* Mobile tuning for ticket size and typography */
      @media (max-width: 640px) {
        /* Keep EXACTLY three sections visible on mobile */
        .ticket { border-radius: 12px; max-width: 360px; margin-left:auto; margin-right:auto; }
        /* Section widths: 1) invitation, 2) flyer fill, 3) QR+code */
        .ticket-table td:nth-child(1) { width: 64% !important; }
        .ticket-table td:nth-child(2) { width: 26% !important; }
        .ticket-table td:nth-child(3) { width: 10% !important; }
        /* Section heights */
        .brand-panel, .qr-panel { height: 40pt; }
        /* Flyer MUST fill its column */
        .flyer-img { height: 100% !important; width: 100% !important; object-fit: cover !important; }
        /* Invitation text sizing */
        .brand-inner { padding: 2pt 5pt 0 8pt; }
        .brand-name { font-size: 8.5pt; letter-spacing: .2pt; }
        .amp-line { font-size: 5pt; padding-left: 6pt; letter-spacing: .3pt; }
        .invite-center { left: 12%; right: 12%; width: 76%; top: 50%; }
        .invite-center div:nth-child(1) { font-size: 7.5pt !important; line-height: 1.05 !important; padding-left: 1pt !important; }
        .invite-center div:nth-child(2) { font-size: 5.2pt !important; letter-spacing: .35pt !important; line-height: 1 !important; }
        .invite-center div:nth-child(3) { font-size: 7.5pt !important; line-height: 1.05 !important; padding-right: 1pt !important; }
        /* Section 3 stays white, shows QR and vertical code */
        .qr-panel { background:#FAFAFA !important; }
        .qr-mini { display:block; width: 18pt; height: 18pt; top: 3pt; right: 3pt; }
        .qr-vert { display:block; right: 2pt; }
        .qr-vert svg { height: 32pt !important; width: auto !important; }
      }
    </style>

    @php
      $brandName = config('app.name', '2DAWN');
      $b = (string)$brandName; $first = mb_substr($b,0,1); $rest = mb_substr($b,1);
      $event = $order->event ?? null;
      $host = $event?->title ?? $event?->organizer_name ?? $event?->host_name ?? $event?->organiser_name ?? $event?->organizer ?? $brandName;
      $bf = trim((string)($order->buyer_name ?? 'Guest'));
      $parts = preg_split('/\s+/', $bf, -1, PREG_SPLIT_NO_EMPTY);
      if (count($parts) >= 2) { $guest = $parts[0] . ' ' . mb_strtoupper(mb_substr($parts[1], 0, 1)) . '.'; } else { $guest = $bf; }
      $flyerUrl = $event && $event->image_path ? Storage::url($event->image_path) : null;
    @endphp

    @if ($order->tickets && $order->tickets->count())
      <div class="space-y-6">
        @foreach ($order->tickets as $t)
          <div class="ticket" id="ticket-{{ $t->code }}" data-code="{{ $t->code }}">
            <table class="ticket-table">
              <tr>
                <td class="brand-panel" style="width:65%;">
                  <div class="brand-inner">
                    <div>
                      <div class="brand-name"><span class="brand-num">{{ $first }}</span>{{ $rest }}</div>
                      <div class="amp-line">&amp;</div>
                    </div>
                  </div>
                  <div class="invite-center" style="position:absolute; left:25%; right:25%; top:56%; transform:translateY(-50%); width:50%; margin:0 auto; text-align:center;">
<div style="width:100%; text-align:left; padding-left:2pt; font-style:italic; font-size:17pt;">{{ $host }}</div>
                    <div style="width:100%; text-align:center; font-weight:700; letter-spacing:.8pt; font-size:10pt;">cordially invites</div>
<div style="width:100%; text-align:right; padding-right:2pt; font-style:italic; font-size:17pt;">{{ $guest }}</div>
                  </div>
                </td>
                <td class="flyer-cell" style="width:22%;">
                  @if ($flyerUrl)
                    <img class="flyer-img" src="{{ $flyerUrl }}" alt="Flyer">
                  @else
                    <div class="flyer-img" style="background:linear-gradient(90deg,#7C3AED,#EF4444);"></div>
                  @endif
                </td>
                <td class="qr-panel" style="width:13%;">
                  @if ($t->qr_path)
                    <img class="qr-mini" src="{{ Storage::url($t->qr_path) }}" alt="QR small {{ $t->code }}" />
                  @else
                    <div class="qr-mini"></div>
                  @endif
                  <div class="qr-vert">
<svg xmlns="http://www.w3.org/2000/svg" width="26pt" height="170pt" viewBox="0 0 38 190">
                      <g transform="rotate(-90 19 175)">
<text x="0" y="165" font-size="9" fill="#0a0a0a" font-family="DejaVu Sans, Arial, sans-serif" font-weight="700">
                          <tspan x="0" dy="0">Ticket Code</tspan>
                          <tspan x="0" dy="16">{{ $t->code }}</tspan>
                        </text>
                      </g>
                    </svg>
                  </div>
                </td>
              </tr>
            </table>
          </div>
        @endforeach
      </div>
    @else
      <div class="mt-6 p-3 bg-emerald-500/10 text-emerald-300 rounded ring-1 ring-emerald-500/30">Payment successful. Generating your tickets... this page will refresh automatically.</div>
      <script>setTimeout(() => location.reload(), 3000);</script>
    @endif
    </div>
  </div>
</section>

<style>
@media print{
  header, .print\:hidden, .print\:hidden *{ display:none !important; }
  body{ background:#fff !important; color:#000 !important; }
}
</style>

<script src="https://unpkg.com/html-to-image@1.11.11/dist/html-to-image.min.js" defer></script>
<script>
  // Ensure the html-to-image library is available, with CDN fallbacks
  let h2iReady;
  function ensureHtmlToImage(){
    if (window.htmlToImage) return Promise.resolve(window.htmlToImage);
    if (h2iReady) return h2iReady;
    const sources = [
      'https://unpkg.com/html-to-image@1.11.11/dist/html-to-image.min.js',
      'https://cdn.jsdelivr.net/npm/html-to-image@1.11.11/dist/html-to-image.min.js'
    ];
    h2iReady = new Promise(async (resolve, reject) => {
      for (const src of sources) {
        try {
          await new Promise((res, rej) => {
            const s = document.createElement('script');
            s.src = src; s.async = true; s.onload = res; s.onerror = rej; document.head.appendChild(s);
          });
          if (window.htmlToImage) return resolve(window.htmlToImage);
        } catch (e) { /* try next */ }
      }
      reject(new Error('html-to-image failed to load'));
    });
    return h2iReady;
  }

  async function downloadTicketsAsPng(){
    try{
      const h2i = await ensureHtmlToImage();
      // Wait for all images inside tickets to finish loading to avoid blank areas
      const imgEls = Array.from(document.querySelectorAll('.ticket img'));
      await Promise.all(imgEls.map(img => img.complete ? Promise.resolve() : new Promise(r => { img.onload = img.onerror = r; })));

      const tickets = document.querySelectorAll('.ticket[data-code]');
      for (const el of tickets) {
        const code = el.getAttribute('data-code') || 'ticket';
        try {
          const dataUrl = await h2i.toPng(el, { pixelRatio: 2, backgroundColor: null, cacheBust: true, style: { background: 'transparent' } });
          const a = document.createElement('a');
          a.href = dataUrl; a.download = code + '.png';
          document.body.appendChild(a); a.click(); a.remove();
          await new Promise(r => setTimeout(r, 200));
        } catch (e) {
          console.error('PNG export failed', e);
          alert('Could not generate PNG for ' + code + '.');
        }
      }
    } catch (e) {
      console.error(e);
      alert('Could not load image library. If you are offline, try Download PDF instead.');
    }
  }
</script>
@endsection
