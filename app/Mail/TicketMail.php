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
        $qrData = null; // fallback data URI (SVG) if inline fails (many clients block it)

        // Prefer PNG (supported by all major email clients)
        try {
            $renderer = new \BaconQrCode\Renderer\ImageRenderer(
                new \BaconQrCode\Renderer\RendererStyle\RendererStyle(300),
                new \BaconQrCode\Renderer\Image\GdImageBackEnd()
            );
            $writer = new \BaconQrCode\Writer($renderer);
            $png = $writer->writeString($order->paystack_reference); // binary PNG

            // Embed inline and also attach as a downloadable file
            $qrCid = $this->embedData($png, 'ticket-qr.png', 'image/png');
            $this->attachData($png, 'ticket-qr.png', ['mime' => 'image/png']);
        } catch (\Throwable $e) {
            // Fallback to SVG as attachment only (some clients won't display inline SVG)
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

        return $this->subject('Your ticket - '.$order->paystack_reference)
            ->view('emails.ticket', [
                'order' => $order,
                'qrSrc' => $qrCid,
                'qrData' => $qrData,
                'publicUrl' => route('orders.public', $order->paystack_reference),
                'pdfUrl' => $pdfUrl,
            ]);
    }
}
