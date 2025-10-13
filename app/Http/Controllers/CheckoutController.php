<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use App\Jobs\GenerateTickets;

class CheckoutController extends Controller
{
    public function buy(Event $event)
    {
        abort_unless($event->is_published, 404);
        $now = now();
        $isPast = ($event->ends_at && $event->ends_at->lt($now)) || (!$event->ends_at && $event->starts_at && $event->starts_at->lt($now));
        if ($isPast) {
            abort(410, 'Ticket sales closed for this event.');
        }
        return view('events.buy', compact('event'));
    }

    public function create(Request $request, Event $event)
    {
        abort_unless($event->is_published, 404);
        $now = now();
        $isPast = ($event->ends_at && $event->ends_at->lt($now)) || (!$event->ends_at && $event->starts_at && $event->starts_at->lt($now));
        if ($isPast) {
            return back()->withErrors(['event' => 'Ticket sales closed for this event.']);
        }

        $data = $request->validate([
            'buyer_name' => ['required','string','max:255'],
            'buyer_email' => ['required','email','max:255'],
            'buyer_phone' => ['nullable','string','max:50'],
'quantity' => ['required','integer','min:1'],
            'coupon' => ['nullable','string','max:50'],
        ]);

        $quantity = (int) $data['quantity'];

        // Prevent oversell: basic pre-check
        if (! is_null($event->capacity) && $quantity > (int)$event->capacity) {
            return back()->withErrors(['quantity' => 'Only '.$event->capacity.' ticket(s) remaining for this event.'])->withInput();
        }

        // Early bird pricing
        $now = now();
        $unitPrice = (float) ($event->price ?? 0);
        if (!is_null($event->early_bird_price) && !is_null($event->early_bird_ends_at) && $now->lte($event->early_bird_ends_at)) {
            $unitPrice = (float) $event->early_bird_price;
        }
        $subtotalKobo = (int) round($unitPrice * $quantity * 100);

        // Apply coupon if valid
        $couponCode = $data['coupon'] ?? null;
        $discountKobo = 0;
        if ($couponCode) {
            $coupon = \App\Models\Coupon::where('code', $couponCode)->validFor($event->id)->first();
            if ($coupon) {
                if ($coupon->type === 'percent') {
                    $discountKobo = (int) floor($subtotalKobo * min(100, $coupon->value) / 100);
                } else { // fixed (in kobo)
                    $discountKobo = min($subtotalKobo, (int) $coupon->value);
                }
            }
        }

        $amountKobo = max(0, $subtotalKobo - $discountKobo);

        // Create pending order
        $reference = 'PA_'.bin2hex(random_bytes(8));
        $order = Order::create([
            'event_id' => $event->id,
            'buyer_name' => $data['buyer_name'],
            'buyer_email' => $data['buyer_email'],
'buyer_phone' => $data['buyer_phone'] ?? null,
            'coupon_code' => $couponCode,
            'quantity' => $quantity,
            'amount' => $amountKobo,
            'paystack_reference' => $reference,
            'status' => 'pending',
        ]);

        $secret = config('services.paystack.secret');
        if (! $secret) {
            return back()->withErrors(['payment' => 'Payment gateway not configured. Set PAYSTACK_SECRET_KEY in your .env.']);
        }

        // If the amount is zero (free), bypass gateway and mark as paid immediately
        if ($amountKobo <= 0) {
            $this->finalizeZeroCostOrder($order);
            return redirect()->route('orders.public', $reference);
        }

        $callback = config('services.paystack.callback_url') ?: route('paystack.callback');
        $response = Http::withToken($secret)->post('https://api.paystack.co/transaction/initialize', [
            'email' => $order->buyer_email,
            'amount' => $amountKobo,
            'reference' => $reference,
            'callback_url' => $callback,
            'currency' => 'NGN',
        ]);

        if (! $response->ok() || ! data_get($response->json(), 'status')) {
            // Log full context so we can diagnose production failures
            try {
                \Log::error('paystack-init-failed', [
                    'http_status' => $response->status(),
                    'body' => $response->body(),
                    'amount_kobo' => $amountKobo,
                    'reference' => $reference,
                    'email' => $order->buyer_email,
                ]);
            } catch (\Throwable $e) { /* ignore logging errors */ }
            $order->update(['status' => 'failed']);
            return back()->withErrors(['payment' => 'Unable to initialize payment. Please try again.']);
        }

        $authUrl = data_get($response->json(), 'data.authorization_url');
        return redirect()->away($authUrl);
    }

    public function callback(Request $request)
    {
        $reference = (string) $request->query('reference', '');
        if ($reference === '') {
            return view('orders.failed', ['message' => 'Missing payment reference.']);
        }

        // Finalize payment. If it fails (sold out or gateway error), show a friendly message.
        $ok = $this->finalizePayment($reference);
        if (! $ok) {
            return view('orders.failed', ['message' => 'Payment failed or tickets sold out.']);
        }
        return redirect()->route('orders.public', $reference);
    }

    public function showByReference(string $reference)
    {
        $order = Order::with(['event','tickets'])->where('paystack_reference', $reference)->first();
        if (! $order) {
            abort(404);
        }
        return view('orders.success', ['order' => $order]);
    }

    protected function finalizePayment(string $reference)
    {
        $order = Order::where('paystack_reference', $reference)->first();
        if (! $order) {
            return false;
        }

        $secret = config('services.paystack.secret');
        $verify = Http::withToken($secret)->get('https://api.paystack.co/transaction/verify/'.$reference);

        if (! $verify->ok() || data_get($verify->json(), 'data.status') !== 'success') {
            $order->update(['status' => 'failed']);
            return false;
        }

        // Safely mark paid and reduce capacity without overselling
        try {
            DB::transaction(function () use ($order) {
                $event = $order->event()->lockForUpdate()->first();
                if (! $event) {
                    throw new \RuntimeException('Event missing');
                }
                if (! is_null($event->capacity)) {
                    $needed = (int) $order->quantity;
                    $current = (int) $event->capacity;
                    if ($current < $needed) {
                        // Not enough tickets left; fail this order
                        $order->update(['status' => 'failed']);
                        throw new \RuntimeException('Sold out');
                    }
                    $event->update(['capacity' => $current - $needed]);
                }

                if ($order->coupon_code) {
                    \App\Models\Coupon::where('code', $order->coupon_code)->increment('uses');
                }

                $order->update(['status' => 'paid']);
            });
        } catch (\Throwable $e) {
            return false;
        }

        // Defer QR generation to the queue so the callback returns fast
        try {
            GenerateTickets::dispatch($order->id);
        } catch (\Throwable $e) {
            // If queue fails to dispatch, tickets will be missing until manual retry
        }

        return true;
    }

    protected function finalizeZeroCostOrder(Order $order): void
    {
        try {
            DB::transaction(function () use ($order) {
                $event = $order->event()->lockForUpdate()->first();
                if ($event && ! is_null($event->capacity)) {
                    $needed = (int) $order->quantity;
                    $current = (int) $event->capacity;
                    if ($current < $needed) {
                        throw new \RuntimeException('Sold out');
                    }
                    $event->update(['capacity' => $current - $needed]);
                }
                if ($order->coupon_code) {
                    \App\Models\Coupon::where('code', $order->coupon_code)->increment('uses');
                }
                $order->update(['status' => 'paid']);
            });
        } catch (\Throwable $e) {
            $order->update(['status' => 'failed']);
        }
    }

    public function downloadPdf(string $reference)
    {
        $order = Order::with(['event','tickets'])->where('paystack_reference', $reference)->firstOrFail();

        $brandName = config('app.name', '2DAWN');
        $brandColor = '#818CF8';

        // Prepare flyer as data URI for reliable embedding
        $flyerDataUri = null;
        if ($order->event && $order->event->image_path) {
            try {
                if (Storage::exists($order->event->image_path)) {
                    $bin = Storage::get($order->event->image_path);
                    $mime = Storage::mimeType($order->event->image_path) ?? 'image/jpeg';
                    $flyerDataUri = 'data:' . $mime . ';base64,' . base64_encode($bin);
                }
            } catch (\Throwable $e) {
                // ignore, keep null
            }
        }

        // Prepare QR images (SVG) as data URIs keyed by ticket code
        $qrMap = [];
        foreach ($order->tickets as $t) {
            if ($t->qr_path) {
                try {
                    if (Storage::exists($t->qr_path)) {
                        $svg = Storage::get($t->qr_path);
                        $qrMap[$t->code] = 'data:image/svg+xml;base64,' . base64_encode($svg);
                    }
                } catch (\Throwable $e) {
                    // skip if cannot read
                }
            }
        }

        // Render a minimal HTML receipt (no app layout) for PDF
        $html = view('orders.pdf', [
            'order' => $order,
            'brandName' => $brandName,
            'flyerDataUri' => $flyerDataUri,
'qrMap' => $qrMap,
            'brandColor' => $brandColor,
        ])->render();

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'receipt_'.$reference.'.pdf';
        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"'
        ]);
    }
}
