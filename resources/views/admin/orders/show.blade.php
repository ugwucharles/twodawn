<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Order Details') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-2">
                    <div><span class="text-gray-600">Reference:</span> <span class="font-mono">{{ $order->paystack_reference }}</span></div>
                    <div><span class="text-gray-600">Status:</span> <span class="uppercase">{{ $order->status }}</span></div>
                    <div><span class="text-gray-600">Date:</span> {{ $order->created_at->format('Y-m-d H:i') }}</div>

                    <div class="mt-4">
                        <h3 class="font-semibold">Event</h3>
                        <div>{{ $order->event->title }}</div>
                        <div class="text-sm text-gray-600">{{ optional($order->event->starts_at)->format('D, M j, Y g:i A') }}</div>
                    </div>

                    <div class="mt-4">
                        <h3 class="font-semibold">Buyer</h3>
                        <div>{{ $order->buyer_name }}</div>
                        <div class="text-sm text-gray-600">{{ $order->buyer_email }}</div>
                        @if ($order->buyer_phone)
                            <div class="text-sm text-gray-600">{{ $order->buyer_phone }}</div>
                        @endif
                    </div>

                    <div class="mt-4">
                        <h3 class="font-semibold">Payment</h3>
                        <div>Quantity: {{ $order->quantity }}</div>
                        <div>Amount: ₦{{ number_format($order->amount / 100, 2) }}</div>
                    </div>

                    <div class="mt-6">
                        <a href="{{ route('admin.orders.index') }}" class="text-indigo-600 hover:underline">← Back to orders</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
