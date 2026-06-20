<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Order Details') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm">
                <div class="p-6 text-[#111827] space-y-2">
                    @if (session('status'))
                        <div class="mb-3 p-2 rounded bg-green-500/10 text-green-300 ring-1 ring-green-500/20 text-sm">{{ session('status') }}</div>
                    @endif
                    @if ($errors->has('mail'))
                        <div class="mb-3 p-2 rounded bg-red-500/10 text-red-300 ring-1 ring-red-500/20 text-sm">{{ $errors->first('mail') }}</div>
                    @endif

                    <div><span class="text-[#6B7280]">Reference:</span> <span class="font-mono">{{ $order->paystack_reference }}</span></div>
                    <div><span class="text-[#6B7280]">Status:</span> <span class="uppercase">{{ $order->status }}</span></div>
                    <div><span class="text-[#6B7280]">Date:</span> {{ $order->created_at->format('Y-m-d H:i') }}</div>

                    <div class="mt-4">
                        <h3 class="font-semibold">Event</h3>
                        <div>{{ $order->event->title }}</div>
                        <div class="text-sm text-[#6B7280]">{{ optional($order->event->starts_at)->format('D, M j, Y g:i A') }}</div>
                    </div>

                    <div class="mt-4">
                        <h3 class="font-semibold">Buyer</h3>
                        <div>{{ $order->buyer_name }}</div>
                        <div class="text-sm text-[#6B7280]">{{ $order->buyer_email }}</div>
                        @if ($order->buyer_phone)
                            <div class="text-sm text-[#6B7280]">{{ $order->buyer_phone }}</div>
                        @endif
                    </div>

                    <div class="mt-4">
                        <h3 class="font-semibold">Payment</h3>
                        <div>Quantity: {{ $order->quantity }}</div>
                        <div>Amount: ₦{{ number_format($order->amount / 100, 2) }}</div>
                        <form method="POST" action="{{ route('admin.orders.resend', $order) }}" class="mt-3">
                            @csrf
                            <button class="inline-flex px-4 py-2 rounded-md bg-[#6366F1] text-white text-sm font-semibold hover:bg-[#4F46E5]">Resend ticket email</button>
                        </form>
                    </div>

                    <!-- Refunds -->
                    @if ($order->status === 'paid' || $order->status === 'partially_refunded')
                    <div class="mt-6 border-t border-[#E5E7EB] pt-4">
                        <h3 class="font-semibold">Refunds</h3>
                        <form method="POST" action="{{ route('admin.orders.refunds.store', $order) }}" class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-3">
                            @csrf
                            <div>
                                <label class="text-sm text-[#374151]">Amount (₦)</label>
                                <input name="amount" type="number" step="0.01" min="0" placeholder="Leave empty for full" class="mt-1 block w-full rounded-md border border-[#D1D5DB] bg-[#F9FAFB] px-3 py-2 text-[#111827] placeholder-[#9CA3AF] focus:border-[#6366F1] focus:ring-2 focus:ring-[rgba(99,102,241,0.2)]" />
                            </div>
                            <div class="sm:col-span-2">
                                <label class="text-sm text-[#374151]">Reason</label>
                                <input name="reason" type="text" class="mt-1 block w-full rounded-md border border-[#D1D5DB] bg-[#F9FAFB] px-3 py-2 text-[#111827] focus:border-[#6366F1] focus:ring-2 focus:ring-[rgba(99,102,241,0.2)]" />
                            </div>
                            <div class="sm:col-span-3">
                                <button class="inline-flex px-4 py-2 rounded-md bg-[#6366F1] text-white text-sm font-semibold hover:bg-[#4F46E5]">Process refund</button>
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
                                    @if($r->reason) <span class="text-zinc-400">({{ $r->reason }})</span>@endif
                                    <span class="text-zinc-500">{{ optional($r->created_at)->format('Y-m-d H:i') }}</span>
                                  </li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                    </div>
                    @endif

                    <div class="mt-6">
                        <a href="{{ route('admin.orders.index') }}" class="text-[#6366F1] hover:underline">← Back to orders</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
