<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\OrderRefund;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class RefundAdminMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order, public OrderRefund $refund)
    {
    }

    public function build()
    {
        $order = $this->order->load('event');
        $refund = $this->refund;
        $subject = 'Refund issued - '.$order->paystack_reference;
        return $this->subject($subject)
            ->view('emails.refund_admin', [
                'order' => $order,
                'refund' => $refund,
            ]);
    }
}