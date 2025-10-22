<?php
declare(strict_types=1);

use Illuminate\Support\Carbon;

require __DIR__.'/../vendor/autoload.php';

/** @var \Illuminate\Foundation\Application $app */
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Ensure we have at least one published event
$event = \App\Models\Event::query()->first();
if (! $event) {
    $event = \App\Models\Event::create([
        'title' => 'E2E Launch Party',
        'description' => 'Seeded for end-to-end validation',
        'venue' => 'Main Hall',
        'starts_at' => now()->addDays(2),
        'ends_at' => null,
        'price' => 2500.00,
        'capacity' => 200,
        'is_published' => true,
    ]);
}

$quantity = 2;
$unitKobo = (int) round(((float) ($event->price ?? 0)) * 100);
$amountKobo = $unitKobo * $quantity;
$ref = 'PA_'.bin2hex(random_bytes(8));

$order = \App\Models\Order::create([
    'event_id' => $event->id,
    'buyer_name' => 'E2E Tester',
    'buyer_email' => 'tester@example.com',
    'buyer_phone' => null,
    'quantity' => $quantity,
    'amount' => $amountKobo,
    'paystack_reference' => $ref,
    'status' => 'paid',
]);

$base = rtrim((string) config('app.url'), '/');
$payload = [
    'reference' => $ref,
    'success_url' => $base.'/orders/'.$ref,
    'admin_login' => $base.'/admin/login',
    'scanner_url' => $base.'/admin/scanner',
];

echo json_encode($payload, JSON_PRETTY_PRINT).PHP_EOL;
