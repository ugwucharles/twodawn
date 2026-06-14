<x-organizer-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Orders</h1>
            <p class="text-gray-500 mt-1 font-medium">View all your event orders</p>
        </div>

        <!-- Orders Table -->
        <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100/50 overflow-hidden">
            @if($orders->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-[11px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-50">
                                <th class="px-8 py-5">Order ID</th>
                                <th class="px-8 py-5">Event</th>
                                <th class="px-8 py-5">Customer</th>
                                <th class="px-8 py-5">Tickets</th>
                                <th class="px-8 py-5">Amount</th>
                                <th class="px-8 py-5">Date</th>
                                <th class="px-8 py-5">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($orders as $order)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-8 py-6 text-sm font-semibold text-gray-500">{{ $order->reference }}</td>
                                    <td class="px-8 py-6 text-sm font-black text-gray-900">{{ $order->event->title }}</td>
                                    <td class="px-8 py-6 text-sm font-semibold text-gray-500">{{ $order->name }}</td>
                                    <td class="px-8 py-6 text-sm font-black text-gray-900">{{ $order->quantity }}</td>
                                    <td class="px-8 py-6 text-sm font-black text-gray-900">₦{{ number_format($order->amount, 2) }}</td>
                                    <td class="px-8 py-6 text-sm font-semibold text-gray-500">{{ $order->created_at->format('M j, Y - g:i A') }}</td>
                                    <td class="px-8 py-6">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">
                                            Paid
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($orders->hasPages())
                    <div class="p-4 border-t border-gray-50">
                        {{ $orders->links() }}
                    </div>
                @endif
            @else
                <div class="py-20 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto mb-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <p class="text-xl font-bold text-gray-900 mb-2">No orders yet</p>
                    <p class="text-gray-500">Orders will appear here once customers purchase tickets</p>
                </div>
            @endif
        </div>
    </div>
</x-organizer-layout>
