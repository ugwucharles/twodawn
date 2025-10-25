<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\\Mail\\Mailable;
use Illuminate\\Queue\\SerializesModels;
use Illuminate\\Contracts\\Queue\\ShouldQueue;

class TicketMail extends Mailable implements ShouldQueue
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

        return $this->subject('Your ticket - '.$order->paystack_reference)
            ->view('emails.ticket', [
                'order' => $order,
                'qrData' => $qrData,
                'publicUrl' => route('orders.public', $order->paystack_reference),
                'pdfUrl' => route('orders.pdf', $order->paystack_reference),
            ]);
    }
}