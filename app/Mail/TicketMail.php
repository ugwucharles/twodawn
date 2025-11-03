<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class TicketMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
    }

    public function build()
    {
        $order = $this->order->load('event');

        $qrCid = null; // inline image src (cid:...)
        $qrData = null; // fallback data URI (SVG)
        $qrRemote = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' . urlencode($order->paystack_reference);

        // Prefer PNG (supported by all major email clients)
        try {
            // Try Imagick back-end first (if available in bacon-qr-code + ext-imagick)
            $renderer = new \BaconQrCode\Renderer\ImageRenderer(
                new \BaconQrCode\Renderer\RendererStyle\RendererStyle(300),
                new \BaconQrCode\Renderer\Image\ImagickImageBackEnd()
            );
            $writer = new \BaconQrCode\Writer($renderer);
            $png = $writer->writeString($order->paystack_reference); // binary PNG
            $qrCid = $this->embedData($png, 'ticket-qr.png', 'image/png');
            $this->attachData($png, 'ticket-qr.png', ['mime' => 'image/png']);
        } catch (\Throwable $e) {
            // Fallback to SVG attachment; many clients block inline SVG
            try {
                $renderer = new \BaconQrCode\Renderer\ImageRenderer(
                    new \BaconQrCode\Renderer\RendererStyle\RendererStyle(300),
                    new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
                );
                $writer = new \BaconQrCode\Writer($renderer);
                $svg = $writer->writeString($order->paystack_reference);
                $qrData = 'data:image/svg+xml;base64,' . base64_encode($svg);
                $this->attachData($svg, 'ticket-qr.svg', ['mime' => 'image/svg+xml']);
            } catch (\Throwable $e2) {
                $qrCid = null;
                $qrData = null;
            }
        }

        $pdfUrl = URL::temporarySignedRoute('orders.pdf', now()->addDays(7), ['reference' => $order->paystack_reference]);

        // Disable SendGrid click/open tracking via SMTP header to avoid urlXXX subdomain rewrites
        $this->withSymfonyMessage(function ($message) {
            try {
                $host = (string) env('MAIL_HOST');
                if (str_contains(strtolower($host), 'sendgrid')) {
                    $message->getHeaders()->addTextHeader('X-SMTPAPI', json_encode([
                        'filters' => [
                            'clicktrack' => ['settings' => ['enable' => 0]],
                            'opentrack'  => ['settings' => ['enable' => 0]],
                        ],
                    ]));
                }
            } catch (\Throwable $e) { /* ignore */ }
        });

        return $this->subject('Your ticket - '.$order->paystack_reference)
            ->view('emails.ticket', [
                'order' => $order,
                'qrSrc' => $qrCid,
                'qrData' => $qrData,
                'qrRemote' => $qrRemote,
                'publicUrl' => route('orders.public', $order->paystack_reference),
                'pdfUrl' => $pdfUrl,
            ]);
    }
}
