<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\CheckoutController;
use Tests\TestCase;

class OrderSuccessPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'http://localhost']);
    }

    public function test_success_page_renders_with_qr_and_reference(): void
    {
        $event = Event::create([
            'title' => 'Launch Party',
            'starts_at' => now()->addDay(),
            'ends_at' => null,
            'price' => 2500,
            'capacity' => 100,
            'is_published' => true,
        ]);

        $ref = 'PA_'.bin2hex(random_bytes(8));
        Order::create([
            'event_id' => $event->id,
            'buyer_name' => 'Alice Example',
            'buyer_email' => 'alice@example.com',
            'buyer_phone' => null,
            'quantity' => 1,
            'amount' => 250000,
            'paystack_reference' => $ref,
            'status' => 'paid',
        ]);

        $controller = app(CheckoutController::class);
        $view = $controller->showByReference($ref);
        $this->assertNotNull($view);
        $html = $view->render();
        $this->assertStringContainsString($ref, $html);
        $this->assertStringContainsString('data:image/svg+xml;base64,', $html);
    }

    public function test_signed_pdf_download_returns_pdf(): void
    {
        $event = Event::create([
            'title' => 'Download Test',
            'starts_at' => now()->addDay(),
            'ends_at' => null,
            'price' => 1000,
            'capacity' => 10,
            'is_published' => true,
        ]);

        $ref = 'PA_'.bin2hex(random_bytes(8));
        Order::create([
            'event_id' => $event->id,
            'buyer_name' => 'Bob Example',
            'buyer_email' => 'bob@example.com',
            'buyer_phone' => null,
            'quantity' => 2,
            'amount' => 200000,
            'paystack_reference' => $ref,
            'status' => 'paid',
        ]);

        $controller = app(CheckoutController::class);
        $response = $controller->downloadPdf($ref);
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
        $this->assertGreaterThan(1000, strlen($response->getContent()));
    }
}
