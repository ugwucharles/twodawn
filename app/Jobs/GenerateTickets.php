<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class GenerateTickets implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 3;

    public function __construct(public int $orderId)
    {
    }

    public function handle(): void
    {
        $order = Order::with(['event', 'tickets'])->find($this->orderId);
        if (! $order || $order->status !== 'paid') {
            return;
        }

        $existing = (int) $order->tickets()->count();
        $toCreate = max(0, (int) $order->quantity - $existing);
        if ($toCreate <= 0) {
            return; // already generated
        }

        $event = $order->event;
        if (! $event) {
            return;
        }

        $renderer = new ImageRenderer(new RendererStyle(300), new SvgImageBackEnd());
        $writer = new Writer($renderer);

        for ($i = 0; $i < $toCreate; $i++) {
            $code = 'T-' . Str::upper(Str::random(10));
            $path = 'tickets/' . $code . '.svg';
            $svgData = $writer->writeString($code);
            Storage::put($path, $svgData, 'public');

            Ticket::create([
                'order_id' => $order->id,
                'event_id' => $event->id,
                'code' => $code,
                'qr_path' => $path,
            ]);
        }
    }
}
