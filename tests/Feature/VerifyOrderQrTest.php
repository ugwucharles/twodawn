<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Event;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class VerifyOrderQrTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'http://localhost']);
    }

    public function test_admin_can_verify_paid_order_qr(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $event = Event::create([
            'title' => 'Sample Event',
            'starts_at' => now()->addDay(),
            'ends_at' => null,
            'price' => 1000,
            'capacity' => 100,
            'is_published' => true,
        ]);
        $order = Order::create([
            'event_id' => $event->id,
            'buyer_name' => 'Jane Doe',
            'buyer_email' => 'jane@example.com',
            'buyer_phone' => null,
            'quantity' => 2,
            'amount' => 50000,
            'paystack_reference' => 'PA_'.bin2hex(random_bytes(8)),
            'status' => 'paid',
        ]);

        $this->actingAs($admin);
        $controller = app(\App\Http\Controllers\Admin\TicketScanController::class);
        $request = Request::create('/verify-ticket', 'POST', ['text' => $order->paystack_reference]);
        $response = $controller->verify($request);
        $this->assertSame(200, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertTrue($data['ok']);
        $this->assertTrue($data['valid']);
        $this->assertSame('order', $data['type']);
        $this->assertSame($order->paystack_reference, $data['reference']);
        $this->assertSame('paid', $data['status']);
        $this->assertSame(2, $data['quantity']);
    }

    public function test_admin_verification_handles_url_embedded_reference(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $event = Event::create([
            'title' => 'Another Event',
            'starts_at' => now()->addDays(2),
            'ends_at' => null,
            'price' => 1500,
            'capacity' => 50,
            'is_published' => true,
        ]);
        $order = Order::create([
            'event_id' => $event->id,
            'buyer_name' => 'John Smith',
            'buyer_email' => 'john@example.com',
            'buyer_phone' => null,
            'quantity' => 1,
            'amount' => 25000,
            'paystack_reference' => 'PA_'.bin2hex(random_bytes(8)),
            'status' => 'paid',
        ]);

        $text = 'https://example.com/check?ref='.$order->paystack_reference.'#scan';

        $this->actingAs($admin);
        $controller = app(\App\Http\Controllers\Admin\TicketScanController::class);
        $request = Request::create('/verify-ticket', 'POST', ['text' => $text]);
        $response = $controller->verify($request);
        $this->assertSame(200, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertTrue($data['ok']);
        $this->assertTrue($data['valid']);
        $this->assertSame($order->paystack_reference, $data['reference']);
    }

    public function test_admin_gets_404_for_unknown_reference(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);
        $controller = app(\App\Http\Controllers\Admin\TicketScanController::class);
        $request = Request::create('/verify-ticket', 'POST', ['text' => 'PA_'.bin2hex(random_bytes(8))]);
        $response = $controller->verify($request);
        $this->assertSame(404, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertFalse($data['ok']);
        $this->assertFalse($data['valid']);
    }

    public function test_admin_sees_invalid_for_unpaid_order(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $event = Event::create([
            'title' => 'Unpaid Event',
            'starts_at' => now()->addDay(),
            'ends_at' => null,
            'price' => 2000,
            'capacity' => 25,
            'is_published' => false,
        ]);
        $order = Order::create([
            'event_id' => $event->id,
            'buyer_name' => 'Unpaid User',
            'buyer_email' => 'unpaid@example.com',
            'buyer_phone' => null,
            'quantity' => 3,
            'amount' => 75000,
            'paystack_reference' => 'PA_'.bin2hex(random_bytes(8)),
            'status' => 'pending',
        ]);

        $this->actingAs($admin);
        $controller = app(\App\Http\Controllers\Admin\TicketScanController::class);
        $request = Request::create('/verify-ticket', 'POST', ['text' => $order->paystack_reference]);
        $response = $controller->verify($request);
        $this->assertSame(200, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertTrue($data['ok']);
        $this->assertFalse($data['valid']);
        $this->assertSame('pending', $data['status']);
    }
}
