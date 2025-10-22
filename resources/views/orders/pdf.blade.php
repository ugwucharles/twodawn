<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ticket {{ $order->paystack_reference }}</title>
  <style>
    @page { margin: 28pt; }
    * { box-sizing: border-box; }
    body { font-family: DejaVu Sans, Arial, sans-serif; color: #111; font-size: 12pt; }

    /* Ticket layout - compact, single ticket per block */
    .ticket{ border:1px solid #e5e7eb; border-radius:12pt; overflow:hidden; margin-top:0; }
    .ticket-table{ width:100%; border-collapse:collapse; border-spacing:0; table-layout:fixed; }
    .ticket-table td{ padding:0; vertical-align:middle; }

    .brand-panel{ position:relative; height:160pt; color:#fff; background:#0b0b0b; }
    .brand-inner{ padding:8px 12px 0 30px; height:100%; display:flex; align-items:flex-start; }
    .brand-name{ font-weight:800; font-size:22pt; letter-spacing:.8pt; color: {{ $brandColor ?? '#818cf8' }}; text-rendering:geometricPrecision; }
    .amp-line{ margin-top:3pt; padding-left:27px; font-weight:800; font-size:14pt; color:rgba(255,255,255,0.9); letter-spacing:1pt; }

    .invite-center{ position:absolute; left:25%; right:25%; top:56%; transform:translateY(-50%); display:block; text-align:center; padding:0; margin:0 auto; width:50%; }
    .invite-host, .invite-guest{ font-style:italic; font-size:17pt; color:rgba(255,255,255,0.95); }
    .invite-host{ text-align:left; padding-left:2pt; }
    .invite-mid{ text-align:center; font-weight:700; letter-spacing:.8pt; font-size:10pt; color:rgba(255,255,255,0.9); }
    .invite-guest{ text-align:right; padding-right:2pt; }

    .flyer-cell{ text-align:left; background:#0b0b0b; overflow:hidden; }
.flyer-img{ display:block; width:100%; max-width:none; height:160pt; object-fit:cover; object-position:center center; transform:none; background:#0b0b0b; }

    .qr-panel{ background:#FAFAFA; height:160pt; position:relative; overflow:hidden; }
    .qr-mini{ position:absolute; top:8pt; right:8pt; width:54pt; height:54pt; background:#fff; border:1px solid #e5e7eb; border-radius:6pt; object-fit:contain; }
    .qr-vert{ position:absolute; top:50%; right:4pt; transform:translateY(-50%); }
  </style>
</head>
<body>
  @php $qr = $orderQrData ?? null; @endphp
  <div class="ticket">
    <table class="ticket-table">
      <tr>
        <td class="brand-panel" style="width:65%;">
          @php 
            $b = (string)($brandName ?? '2DAWN'); $first = mb_substr($b,0,1); $rest = mb_substr($b,1);
            $event = $order->event ?? null;
            $host = $event?->title ?? $event?->organizer_name ?? $event?->host_name ?? $event?->organiser_name ?? $event?->organizer ?? ($brandName ?? '2DAWN');
            $bf = trim((string)($order->buyer_name ?? 'Guest'));
            $parts = preg_split('/\s+/', $bf, -1, PREG_SPLIT_NO_EMPTY);
            if (count($parts) >= 2) {
              $guest = $parts[0] . ' ' . mb_strtoupper(mb_substr($parts[1], 0, 1)) . '.';
            } else {
              $guest = $bf;
            }
          @endphp
          <div class="brand-inner">
            <div>
              <div class="brand-name"><span style="color:#ffffff;">{{ $first }}</span>{{ $rest }}</div>
              <div class="amp-line">&amp;</div>
            </div>
          </div>
          <div class="invite-center">
            <div class="invite-host">{{ $host }}</div>
            <div class="invite-mid">cordially invites</div>
            <div class="invite-guest">{{ $guest }}</div>
          </div>
        </td>
        <td class="flyer-cell" style="width:22%;">
          @if (!empty($flyerDataUri))
            <img class="flyer-img" src="{{ $flyerDataUri }}" alt="Event flyer">
          @else
            <div class="flyer-img" style="background:linear-gradient(90deg,#7C3AED,#EF4444);"></div>
          @endif
        </td>
        <td class="qr-panel" style="width:13%;">
          @if ($qr)
            <img class="qr-mini" src="{{ $qr }}" alt="Order QR" />
          @else
            <div class="qr-mini"></div>
          @endif
          <div class="qr-vert">
            <svg xmlns="http://www.w3.org/2000/svg" width="26pt" height="170pt" viewBox="0 0 38 190">
              <g transform="rotate(-90 19 175)">
                <text x="0" y="165" font-size="9" fill="#0a0a0a" font-family="DejaVu Sans, Arial, sans-serif" font-weight="700">
                  <tspan x="0" dy="0">Order Ref</tspan>
                  <tspan x="0" dy="16">{{ $order->paystack_reference }}</tspan>
                </text>
              </g>
            </svg>
          </div>
        </td>
      </tr>
    </table>
  </div>
</body>
</html>
