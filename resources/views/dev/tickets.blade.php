@extends('layouts.public')

@section('content')
<section class="py-8">
  <div class="max-w-7xl mx-auto px-6">
    <div class="print:hidden mb-6 rounded-2xl bg-white/5 ring-1 ring-white/10 p-4">
      <form method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-3 items-end">
        <div>
          <label class="block text-xs text-zinc-400">Brand</label>
          <input type="text" name="brand" value="{{ $brand }}" class="mt-1 block w-full rounded-lg bg-black/30 border border-white/10 px-3 py-2" />
        </div>
        <div>
          <label class="block text-xs text-zinc-400">Brand color</label>
          <input type="color" name="brandColor" value="{{ $brandColor }}" class="mt-1 block w-full h-[38px] rounded-lg bg-transparent" />
        </div>
        <div>
          <label class="block text-xs text-zinc-400">Gradient 1</label>
          <input type="color" name="g1" value="{{ $g1 }}" class="mt-1 block w-full h-[38px] rounded-lg bg-transparent" />
        </div>
        <div>
          <label class="block text-xs text-zinc-400">Gradient 3</label>
          <input type="color" name="g3" value="{{ $g3 }}" class="mt-1 block w-full h-[38px] rounded-lg bg-transparent" />
        </div>
        <div>
          <label class="block text-xs text-zinc-400">Order reference (optional)</label>
          <input type="text" name="reference" value="{{ request('reference') }}" placeholder="PA_..." class="mt-1 block w-full rounded-lg bg-black/30 border border-white/10 px-3 py-2" />
        </div>
        <div class="md:col-span-5 flex items-center gap-3">
          <button class="inline-flex items-center px-4 py-2 rounded-lg bg-white text-black text-sm font-semibold hover:bg-zinc-100">Apply</button>
          <a href="{{ route('home') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-white/10 ring-1 ring-white/10 text-white text-sm hover:bg-white/15">Home</a>
          @if ($order)
            <a href="{{ route('orders.pdf', $order->paystack_reference) }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-white/10 ring-1 ring-white/10 text-white text-sm hover:bg-white/15">Download current PDF</a>
          @endif
        </div>
      </form>
    </div>

    <style>
:root{
        --g1: {{ $g1 }}; --g2: {{ $g2 }}; --g3: {{ $g3 }};
        --brand-color: {{ $brandColor }};
        --ticket-h: 180pt;
      }
      .ticket{ border:1px solid rgb(39 39 42 / 0.3); border-radius:16px; overflow:hidden; }
      .ticket-table{ width:100%; border-collapse:collapse; table-layout:fixed; }
      .ticket-table td{ padding:0; vertical-align:middle; }
.brand-panel{ position:relative; height:var(--ticket-h); color:#fff; background: #2d0a51; background-image:
        linear-gradient(90deg, rgba(0,0,0,0.45), rgba(0,0,0,0.25) 40%, rgba(0,0,0,0.10) 70%, rgba(0,0,0,0.00) 100%),
        radial-gradient(120% 100% at 0% 0%, rgba(255,255,255,0.10), rgba(255,255,255,0) 60%),
        linear-gradient(120deg, rgba(124,58,237,0.22), rgba(59,130,246,0.22) 50%, rgba(236,72,153,0.22) 100%),
        linear-gradient(90deg, var(--g1) 0%, var(--g1) 60%, var(--g1) 100%);
      }
.brand-inner{ padding:8px 12px 0 30px; height:100%; display:flex; align-items:flex-start; }
.brand-name{ position:relative; font-weight:800; font-size:28pt; letter-spacing:.5pt; color: var(--brand-color); font-family:'Montserrat','Manrope',ui-sans-serif,system-ui,sans-serif; }
.brand-name .brand-num{ color:#ffffff; }
.amp-line{ margin-top:3pt; padding-left:2pt; font-family:'Montserrat','Manrope',ui-sans-serif,system-ui,sans-serif; font-weight:800; font-size:14pt; color:rgba(255,255,255,0.9); letter-spacing:1pt; }
.invite-center{ position:absolute; left:25%; right:25%; top:50%; transform:translateY(-50%); display:flex; flex-direction:column; align-items:stretch; justify-content:center; gap:3pt; padding:0; text-align:center; pointer-events:none; width:50%; margin:0 auto; }
      .invite-host,.invite-guest{ font-family:'Dancing Script', cursive; font-size:20pt; line-height:1.05; color:rgba(255,255,255,0.96); width:100%; }
      .invite-host{ text-align:left; padding-left:2pt; }
      .invite-mid{ font-family:'Montserrat','Manrope',ui-sans-serif,system-ui,sans-serif; font-weight:700; letter-spacing:0.8pt; font-size:10pt; color:rgba(255,255,255,0.9); text-transform:none; width:100%; text-align:center; }
.invite-guest{ text-align:right; padding-right:2pt; }
.flyer-cell{ background:#0b0b0b; text-align:left; overflow:hidden; }
.flyer-img{ display:block; width:100%; max-width:none; height:var(--ticket-h); object-fit:cover; object-position:center center; transform:none; }
.qr-panel{ background:#FAFAFA; height:var(--ticket-h); position:relative; display:flex; align-items:flex-start; justify-content:flex-start; padding:8pt; }
.qr-img{ position:relative; z-index:5; width:200pt; height:200pt; object-fit:contain; background:#fff; padding:8pt; border:1px solid #d4d4d8; border-radius:8pt; }
.qr-img-right{ position:absolute; left:50%; top:25%; transform:translate(-50%,-50%); z-index:20; width:80pt; height:80pt; object-fit:contain; background:#000; padding:0; border:1px solid #000; border-radius:8pt; }
.vertical-code{ position:absolute; right:8pt; bottom:10pt; transform-origin:bottom right; z-index:100; pointer-events:none; }
.vertical-code svg{ overflow: visible; }
    </style>

    @php $ticketsList = $tickets; @endphp

    <div class="space-y-6">
      @foreach ($ticketsList as $t)
        @php $code = $t->code; $qr = $qrMap[$code] ?? null; @endphp
        <div class="ticket">
          <table class="ticket-table">
            <tr>
              <td style="width:53%;" class="brand-panel">
                @php 
                  $b = (string) $brand; $first = mb_substr($b,0,1); $rest = mb_substr($b,1);
                  $event = $order?->event;
                  $host = $event?->title ?? $event?->organizer_name ?? $event?->host_name ?? $event?->organiser_name ?? $event?->organizer ?? $brand;
                  $bf = trim((string)($order?->buyer_name ?? 'Guest'));
                  $parts = preg_split('/\s+/', $bf, -1, PREG_SPLIT_NO_EMPTY);
                  if (count($parts) >= 2) {
                    $guest = $parts[0] . ' ' . mb_strtoupper(mb_substr($parts[1], 0, 1)) . '.';
                  } else {
                    $guest = $bf;
                  }
                @endphp
                <div class="brand-inner">
                  <div>
                    <div class="brand-name"><span class="brand-num">{{ $first }}</span>{{ $rest }}</div>
                    <div class="amp-line">&amp;</div>
                  </div>
                </div>
                <div class="invite-center">
                  <div class="invite-host">{{ $host }}</div>
                  <div class="invite-mid">cordially invites</div>
                  <div class="invite-guest">{{ $guest }}</div>
                </div>
              </td>
              <td style="width:15%;" class="flyer-cell">
                @if (!empty($flyerDataUri))
                  <img class="flyer-img" src="{{ $flyerDataUri }}" alt="Flyer"/>
                @else
                  <div class="flyer-img" style="background:linear-gradient(90deg,#7C3AED,#EF4444);"></div>
                @endif
              </td>
              <td style="width:32%;" class="qr-panel">
                @if ($qr)
                  <img class="qr-img" src="{{ $qr }}" alt="QR {{ $code }}"/>
                  <img class="qr-img-right" src="{{ $qr }}" alt="QR Right {{ $code }}"/>
                @else
                  <div class="qr-img"></div>
                  <div class="qr-img-right"></div>
                @endif
                <div class="vertical-code">
<svg xmlns="http://www.w3.org/2000/svg" width="40pt" height="200pt" viewBox="0 0 40 200">
                    <g transform="rotate(-90 20 180)">
                      <text x="0" y="168" font-size="12" fill="#0a0a0a" font-family="DejaVu Sans, Arial, sans-serif" font-weight="700">
                        <tspan x="0" dy="0">Ticket Code</tspan>
                        <tspan x="0" dy="16">{{ $code }}</tspan>
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

    <div class="mt-8 text-zinc-400 text-sm">
      @if (!$order)
        Tip: provide an order reference to preview with real QR and codes.
      @else
        Previewing order {{ $order->paystack_reference }} ({{ $order->quantity }} ticket{{ $order->quantity > 1 ? 's' : '' }}).
      @endif
    </div>
  </div>
</section>
@endsection
