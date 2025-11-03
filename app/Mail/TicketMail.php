<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

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
        $qrRemote = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' . urlencode($order->paystack_reference);

        // Prefer PNG (supported by all major email clients)
        try {
            $renderer = new \BaconQrCode\Renderer\ImageRenderer(
                new \BaconQrCode\Renderer\RendererStyle\RendererStyle(300),
                new \BaconQrCode\Renderer\Image\ImagickImageBackEnd()
            );
            $writer = new \BaconQrCode\Writer($renderer);
            $png = $writer->writeString($order->paystack_reference); // binary PNG
            $qrCid = $this->embedData($png, 'ticket-qr.png', 'image/png');
            $this->attachData($png, 'ticket-qr.png', ['mime' => 'image/png']);
        } catch (\Throwable $e) {
            // If PNG generation fails, rely on remote PNG fallback
            $qrCid = null;
        }

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
                'qrRemote' => $qrRemote,
                'publicUrl' => route('orders.public', $order->paystack_reference),
            ]);
    }
}
