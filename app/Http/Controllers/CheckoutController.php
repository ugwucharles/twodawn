<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\TicketMail;
use Illuminate\Support\Str;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use App\Services\LoggerService;

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
        $startTime = microtime(true);
        
        abort_unless($event->is_published, 404);
        $now = now();
        $isPast = ($event->ends_at && $event->ends_at->lt($now)) || (!$event->ends_at && $event->starts_at && $event->starts_at->lt($now));
        if ($isPast) {
            LoggerService::logSecurity('Attempted to buy tickets for past event', array_merge([
                'event_id' => $event->id,
                'event_title' => $event->title,
            ], LoggerService::getRequestContext($request)));
            return back()->withErrors(['event' => 'Ticket sales closed for this event.']);
        }

        // Server-side double submission protection
        $submissionToken = $request->input('submission_token');
        if (!$submissionToken) {
            LoggerService::logSecurity('Form submission without token', LoggerService::getRequestContext($request));
            return back()->withErrors(['general' => 'Invalid form submission. Please refresh and try again.'])->withInput();
        }

        // Check if this token was already used (simple in-memory cache for demo)
        $cacheKey = 'submission_token_' . $submissionToken;
        if (\Cache::has($cacheKey)) {
            LoggerService::logSecurity('Duplicate form submission detected', array_merge([
                'submission_token' => $submissionToken,
            ], LoggerService::getRequestContext($request)));
            return back()->withErrors(['general' => 'This form has already been submitted. Please refresh the page to try again.'])->withInput();
        }

        // Mark token as used (expires in 1 hour)
        \Cache::put($cacheKey, true, 3600);

        try {
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

            // Calculate buyer fees if passed to buyer: 5% + ₦50 per ticket
            // Never add fees for free events (unit price <= 0)
            $feesKobo = 0;
            if ($unitPrice > 0 && $event->pass_fees_to_buyer) {
                $perTicketFeeKobo = (int) round($unitPrice * 0.05 * 100) + 5000; // 50 NGN = 5000 kobo
                $feesKobo = max(0, $perTicketFeeKobo * $quantity);
            }

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
                } else {
                    return back()->withErrors(['coupon' => 'Invalid or expired coupon code.'])->withInput();
                }
            }

            $amountKobo = max(0, $subtotalKobo - $discountKobo + $feesKobo);

            // If total is zero (free), enforce 1 free claim per hour per email
            if ($amountKobo <= 0) {
                $ip = (string) $request->ip();
                $recentFree = Order::where('created_ip', $ip)
                    ->where('event_id', $event->id)
                    ->where('amount', '<=', 0)
                    ->where('status', 'paid')
                    ->where('created_at', '>=', now()->subHour())
                    ->orderByDesc('created_at')
                    ->first();
                if ($recentFree) {
                    $mins = max(1, 60 - now()->diffInMinutes($recentFree->created_at));
                    return back()->withErrors(['general' => 'You recently claimed a free ticket. Please try again in ~'.$mins.' minute(s).'])->withInput();
                }
            }

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
                'created_ip' => $request->ip(),
            ]);

            // Log order creation
            LoggerService::logOrder('created', $reference, array_merge([
                'event_id' => $event->id,
                'event_title' => $event->title,
                'buyer_email' => $data['buyer_email'],
                'quantity' => $quantity,
                'amount_kobo' => $amountKobo,
                'coupon_code' => $couponCode,
            ], LoggerService::getRequestContext($request)));

            $secret = config('services.paystack.secret');
            if (! $secret) {
                LoggerService::logPaymentFailure('Paystack secret key not configured', [
                    'order_reference' => $reference,
                ]);
                return back()->withErrors(['payment' => 'Payment gateway not configured. Please contact support.'])->withInput();
            }

            // If the amount is zero (free), bypass gateway and mark as paid immediately
            if ($amountKobo <= 0) {
                LoggerService::logPayment('Free order processed', [
                    'order_reference' => $reference,
                    'amount_kobo' => $amountKobo,
                ]);
                $this->finalizeZeroCostOrder($order);
                return redirect()->route('orders.public', $reference);
            }

            // Log payment initialization attempt
            LoggerService::logPayment('Initializing payment', [
                'order_reference' => $reference,
                'amount_kobo' => $amountKobo,
                'buyer_email' => $order->buyer_email,
            ]);

            $callback = config('services.paystack.callback_url') ?: route('paystack.callback');
            $response = Http::withToken($secret)->post('https://api.paystack.co/transaction/initialize', [
                'email' => $order->buyer_email,
                'amount' => $amountKobo,
                'reference' => $reference,
                'callback_url' => $callback,
                'currency' => 'NGN',
            ]);

            if (! $response->ok() || ! data_get($response->json(), 'status')) {
                // Log payment failure with detailed context
                LoggerService::logPaymentFailure('Paystack initialization failed', [
                    'order_reference' => $reference,
                    'http_status' => $response->status(),
                    'response_body' => $response->body(),
                    'amount_kobo' => $amountKobo,
                    'buyer_email' => $order->buyer_email,
                ]);
                $order->update(['status' => 'failed']);
                return back()->withErrors(['payment' => 'Unable to initialize payment. Please try again or contact support.'])->withInput();
            }

            $authUrl = data_get($response->json(), 'data.authorization_url');
            
            // Log successful payment initialization
            LoggerService::logPayment('Payment initialized successfully', [
                'order_reference' => $reference,
                'amount_kobo' => $amountKobo,
                'buyer_email' => $order->buyer_email,
            ]);

            // Log performance
            $duration = microtime(true) - $startTime;
            LoggerService::logPerformance('Order creation and payment initialization', $duration, [
                'order_reference' => $reference,
                'event_id' => $event->id,
            ]);

            return redirect()->away($authUrl);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Log validation errors
            LoggerService::logUserAction('Order validation failed', array_merge([
                'event_id' => $event->id,
                'validation_errors' => $e->errors(),
            ], LoggerService::getRequestContext($request)));
            
            // Re-throw validation exceptions to show form errors
            throw $e;
        } catch (\Exception $e) {
            // Log unexpected errors with comprehensive context
            LoggerService::logPaymentFailure('Unexpected error during order creation', array_merge([
                'error' => $e->getMessage(),
                'event_id' => $event->id,
                'event_title' => $event->title,
                'trace' => $e->getTraceAsString(),
            ], LoggerService::getRequestContext($request)));
            
            return back()->withErrors(['general' => 'Failed to process your order. Please try again.'])->withInput();
        }
    }

    public function quote(Event $event, Request $request)
    {
        abort_unless($event->is_published, 404);
        try {
            $data = $request->validate([
                'quantity' => ['required','integer','min:1'],
                'coupon' => ['nullable','string','max:50'],
            ]);
            $quantity = (int) $data['quantity'];

            // Base/early-bird unit price
            $unitPrice = (float) ($event->price ?? 0);
            $now = now();
            if (!is_null($event->early_bird_price) && !is_null($event->early_bird_ends_at) && $now->lte($event->early_bird_ends_at)) {
                $unitPrice = (float) $event->early_bird_price;
            }
            $subtotalKobo = (int) round($unitPrice * $quantity * 100);

            // Fees (never add fees for free events)
            $feesKobo = 0;
            if ($unitPrice > 0 && $event->pass_fees_to_buyer) {
                $perTicketFeeKobo = (int) round($unitPrice * 0.05 * 100) + 5000; // 50 NGN
                $feesKobo = max(0, $perTicketFeeKobo * $quantity);
            }

            // Coupon
            $discountKobo = 0;
            $validCoupon = false;
            $couponCode = $data['coupon'] ?? null;
            if ($couponCode) {
                $coupon = \App\Models\Coupon::where('code', $couponCode)->validFor($event->id)->first();
                if ($coupon) {
                    $validCoupon = true;
                    if ($coupon->type === 'percent') {
                        $discountKobo = (int) floor($subtotalKobo * min(100, $coupon->value) / 100);
                    } else {
                        $discountKobo = min($subtotalKobo, (int) $coupon->value);
                    }
                }
            }

            $totalKobo = max(0, $subtotalKobo - $discountKobo + $feesKobo);

            return response()->json([
                'ok' => true,
                'subtotal_kobo' => $subtotalKobo,
                'fees_kobo' => $feesKobo,
                'discount_kobo' => $discountKobo,
                'total_kobo' => $totalKobo,
                'coupon_valid' => $validCoupon,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => 'Could not compute quote'], 422);
        }
    }

    public function callback(Request $request)
    {
        $reference = (string) $request->query('reference', '');
        if ($reference === '') {
            LoggerService::logPaymentFailure('Callback received without reference', LoggerService::getRequestContext($request));
            return view('orders.failed', ['message' => 'Missing payment reference.']);
        }

        LoggerService::logPayment('Callback received', [
            'reference' => $reference,
        ]);

        // Finalize payment. If it fails (sold out or gateway error), show a friendly message.
        $ok = $this->finalizePayment($reference);
        if (! $ok) {
            LoggerService::logPaymentFailure('Payment verification failed', [
                'reference' => $reference,
            ]);
            return view('orders.failed', ['message' => 'Payment failed or tickets sold out.']);
        }
        
        LoggerService::logPayment('Payment completed successfully', [
            'reference' => $reference,
        ]);
        return redirect()->route('orders.public', $reference);
    }

    public function showByReference(string $reference)
    {
        $order = Order::with(['event'])->where('paystack_reference', $reference)->first();
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

        // Idempotency: if already paid, return
        if ($order->status === 'paid') {
            return true;
        }

        $secret = config('services.paystack.secret');
        $verify = Http::withToken($secret)->get('https://api.paystack.co/transaction/verify/'.$reference);
        $body = $verify->json();
        $status = (string) data_get($body, 'data.status');
        $amount = (int) data_get($body, 'data.amount');
        $currency = (string) data_get($body, 'data.currency');

        if (! $verify->ok() || $status !== 'success' || $amount !== (int) $order->amount || $currency !== 'NGN') {
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

        // Send ticket email immediately (avoid queue reliance on shared hosting)
        try {
            Log::info('Sending TicketMail', ['reference' => $order->paystack_reference, 'email' => $order->buyer_email]);
            Mail::to($order->buyer_email)->send(new TicketMail($order));
            Log::info('TicketMail sent', ['reference' => $order->paystack_reference, 'email' => $order->buyer_email]);
        } catch (\Throwable $e) {
            Log::error('TicketMail failed', ['reference' => $order->paystack_reference, 'email' => $order->buyer_email, 'error' => $e->getMessage()]);
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
            return; // stop on failure
        }

        // For free orders, email the ticket as well (send immediately)
        try {
            Log::info('Sending TicketMail (free)', ['reference' => $order->paystack_reference, 'email' => $order->buyer_email]);
            Mail::to($order->buyer_email)->send(new TicketMail($order));
            Log::info('TicketMail sent (free)', ['reference' => $order->paystack_reference, 'email' => $order->buyer_email]);
        } catch (\Throwable $e) {
            Log::error('TicketMail failed (free)', ['reference' => $order->paystack_reference, 'email' => $order->buyer_email, 'error' => $e->getMessage()]);
        }
    }

    public function downloadPdf(string $reference)
    {
        $order = Order::with(['event'])->where('paystack_reference', $reference)->firstOrFail();

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

        // Generate order-level QR as data URI
        try {
            $renderer = new ImageRenderer(new RendererStyle(300), new SvgImageBackEnd());
            $writer = new Writer($renderer);
            $svg = $writer->writeString($order->paystack_reference);
            $orderQrData = 'data:image/svg+xml;base64,' . base64_encode($svg);
        } catch (\Throwable $e) {
            $orderQrData = null;
        }

        // Render a minimal HTML receipt (no app layout) for PDF
        $html = view('orders.pdf', [
            'order' => $order,
            'brandName' => $brandName,
            'flyerDataUri' => $flyerDataUri,
            'orderQrData' => $orderQrData,
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
