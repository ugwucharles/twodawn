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
                    @if (session('status'))
                        <div class="mb-3 p-2 rounded bg-green-50 text-green-700 text-sm">{{ session('status') }}</div>
                    @endif
                    @if ($errors->has('mail'))
                        <div class="mb-3 p-2 rounded bg-red-50 text-red-700 text-sm">{{ $errors->first('mail') }}</div>
                    @endif

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
                        <form method="POST" action="{{ route('admin.orders.resend', $order) }}" class="mt-3">
                            @csrf
                            <button class="inline-flex px-4 py-2 rounded bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-500">Resend ticket email</button>
                        </form>
                    </div>

                    <!-- Refunds -->
                    @if ($order->status === 'paid' || $order->status === 'partially_refunded')
                    <div class="mt-6 border-t pt-4">
                        <h3 class="font-semibold">Refunds</h3>
                        <form method="POST" action="{{ route('admin.orders.refunds.store', $order) }}" class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-3">
                            @csrf
                            <div>
                                <label class="text-sm text-gray-600">Amount (₦)</label>
                                <input name="amount" type="number" step="0.01" min="0" placeholder="Leave empty for full" class="mt-1 block w-full rounded border border-white/10 bg-black/30 px-3 py-2" />
                            </div>
                            <div class="sm:col-span-2">
                                <label class="text-sm text-gray-600">Reason</label>
                                <input name="reason" type="text" class="mt-1 block w-full rounded border border-white/10 bg-black/30 px-3 py-2" />
                            </div>
                            <div class="sm:col-span-3">
                                <button class="inline-flex px-4 py-2 rounded bg-white text-black text-sm font-semibold hover:bg-zinc-100">Process refund</button>
                            </div>
                        </form>
                        @php $refunds = \App\Models\OrderRefund::where('order_id',$order->id)->latest()->get(); @endphp
                        @if($refunds->count())
                        <div class="mt-4 text-sm">
                            <div class="font-semibold">History</div>
                            <ul class="mt-1 space-y-1">
                                @foreach($refunds as $r)
                                  <li>
                                    <span class="font-mono">₦{{ number_format($r->amount/100, 2) }}</span>
                                    — {{ $r->status }}
                                    @if($r->reason) <span class="text-gray-600">({{ $r->reason }})</span>@endif
                                    <span class="text-gray-500">{{ optional($r->created_at)->format('Y-m-d H:i') }}</span>
                                  </li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                    </div>
                    @endif

                    <div class="mt-6">
                        <a href="{{ route('admin.orders.index') }}" class="text-indigo-600 hover:underline">← Back to orders</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
