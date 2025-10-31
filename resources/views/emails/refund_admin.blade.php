<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Refund issued</title>
  </head>
  <body style="font-family:Arial,Helvetica,sans-serif; color:#111;">
    <h2 style="margin:0 0 6px 0;">Refund issued</h2>
    <p style="margin:0 0 12px 0;">A refund has been processed.</p>

    <p style="margin:0 0 8px 0;"><strong>Order ref:</strong> {{ $order->paystack_reference }}</p>
    <p style="margin:0 0 8px 0;"><strong>Event:</strong> {{ $order->event?->title ?? '—' }}</p>
    <p style="margin:0 0 8px 0;"><strong>Buyer:</strong> {{ $order->buyer_name }} ({{ $order->buyer_email }})</p>
    <p style="margin:0 0 8px 0;"><strong>Refund amount:</strong> ₦{{ number_format(($refund->amount ?? 0)/100, 2) }}</p>
    @if(!empty($refund->reason))
      <p style="margin:0 0 8px 0;"><strong>Reason:</strong> {{ $refund->reason }}</p>
    @endif
    <p style="margin:0 0 8px 0;"><strong>Status:</strong> {{ ucfirst($refund->status) }}</p>
  </body>
</html>