<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Your refund</title>
  </head>
  <body style="font-family:Arial,Helvetica,sans-serif; color:#111;">
    <h2 style="margin:0 0 6px 0;">Refund processed</h2>
    <p style="margin:0 0 12px 0;">We have processed your refund.</p>

    <p style="margin:0 0 8px 0;"><strong>Order ref:</strong> {{ $order->paystack_reference }}</p>
    <p style="margin:0 0 8px 0;"><strong>Event:</strong> {{ $order->event?->title ?? '—' }}</p>
    <p style="margin:0 0 8px 0;"><strong>Refund amount:</strong> ₦{{ number_format(($refund->amount ?? 0)/100, 2) }}</p>
    @if(!empty($refund->reason))
      <p style="margin:0 0 8px 0;"><strong>Reason:</strong> {{ $refund->reason }}</p>
    @endif
    <p style="margin:0 0 8px 0;"><strong>Status:</strong> {{ ucfirst($refund->status) }}</p>

    <p style="margin:14px 0;"><a href="{{ $publicUrl }}" style="background:#111; color:#fff; text-decoration:none; padding:10px 14px; border-radius:8px;">View your order</a></p>

    <p style="margin:18px 0 0 0; font-size:12px; color:#6b7280;">If you have questions, reply to this email.</p>
  </body>
</html>