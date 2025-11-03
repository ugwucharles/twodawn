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
        $qrData = null;
        try {
            $renderer = new \BaconQrCode\Renderer\ImageRenderer(new \BaconQrCode\Renderer\RendererStyle\RendererStyle(300), new \BaconQrCode\Renderer\Image\SvgImageBackEnd());
            $writer = new \BaconQrCode\Writer($renderer);
            $svg = $writer->writeString($order->paystack_reference);
            $qrData = 'data:image/svg+xml;base64,' . base64_encode($svg);
            $this->attachData($svg, 'ticket-qr.svg', ['mime' => 'image/svg+xml']);
        } catch (\Throwable $e) {
            $qrData = null;
        }

        $pdfUrl = URL::temporarySignedRoute('orders.pdf', now()->addDays(7), ['reference' => $order->paystack_reference]);

        return $this->subject('Your ticket - '.$order->paystack_reference)
            ->view('emails.ticket', [
                'order' => $order,
                'qrData' => $qrData,
                'publicUrl' => route('orders.public', $order->paystack_reference),
                'pdfUrl' => $pdfUrl,
            ]);
    }
}
