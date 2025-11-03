<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Your ticket</title>
  </head>
  <body style="font-family:Arial,Helvetica,sans-serif; color:#111;">
    <h2 style="margin:0 0 6px 0;">Payment successful</h2>
    <p style="margin:0 0 12px 0;">Show this QR at the gate.</p>
    <p style="margin:8px 0; font-size:12px; color:#b91c1c;"><strong>Keep it safe:</strong> Do not share your QR code or order reference. It grants entry and can be used once.</p>

    <table role="presentation" cellpadding="0" cellspacing="0" style="margin:12px 0;">
      <tr>
        <td style="padding:8px; background:#fff; border:1px solid #e5e7eb; border-radius:8px;">
          @if(!empty($qrSrc))
          <img src="{{ $qrSrc }}" alt="Ticket QR" width="220" height="220" style="display:block; width:220px; height:220px; object-fit:contain;" />
          @elseif(!empty($qrRemote))
          <img src="{{ $qrRemote }}" alt="Ticket QR" width="220" height="220" style="display:block; width:220px; height:220px; object-fit:contain;" />
          @else
          <div style="width:220px; height:220px; background:#f1f5f9; display:block;"></div>
          @endif
        </td>
      </tr>
    </table>

    <p style="margin:0 0 8px 0;"><strong>Order ref:</strong> {{ $order->paystack_reference }}</p>
    <p style="margin:0 0 8px 0;"><strong>Event:</strong> {{ $order->event?->title ?? '—' }}</p>
    @php
      $start = optional($order->event?->starts_at);
      $end = optional($order->event?->ends_at) ?: optional($order->event?->starts_at)?->copy()->addHours(2);
    @endphp
    <p style="margin:0 0 8px 0;"><strong>Date:</strong> {{ $start ? $start->format('D, M j, Y') : '—' }}</p>
    <p style="margin:0 0 8px 0;"><strong>Time:</strong> {{ $start ? $start->format('g:ia') : '—' }} @if($end) – {{ $end->format('g:ia') }} @endif</p>
    @if(!empty($order->event?->venue))
      <p style="margin:0 0 8px 0;"><strong>Location:</strong> {{ $order->event->venue }}</p>
    @endif
    <p style="margin:0 0 8px 0;"><strong>Quantity:</strong> {{ $order->quantity }}</p>

    <p style="margin:14px 0;">
      <a href="{{ $publicUrl }}" style="background:#111; color:#fff; text-decoration:none; padding:10px 14px; border-radius:8px;">View ticket</a>
    </p>

    <p style="margin:18px 0 0 0; font-size:12px; color:#6b7280;">If you didn’t make this purchase, please ignore this email.</p>
  </body>
</html>
