<x-organizer-layout>
    <div class="max-w-7xl mx-auto mt-2">
        <!-- Header -->
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-start gap-4">
                <a href="{{ route('organizer.dashboard') }}" class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-gray-500 hover:text-gray-700 shadow-sm transition-colors shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $event->title }}</h1>
                    <p class="text-gray-500 text-sm mt-1">{{ $event->venue }} &mdash; {{ $event->starts_at->format('M j, Y g:i A') }}</p>
                </div>
            </div>

            <div class="flex items-center gap-4">
                @if($event->is_published)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">Live</span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">Draft</span>
                @endif
                <a href="{{ $event->public_url }}" target="_blank" class="inline-flex items-center gap-2 text-sm text-blue-600 hover:text-blue-800 font-semibold bg-blue-50 hover:bg-blue-100 rounded-xl px-4 py-2 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    View Public Page
                </a>
            </div>
        </div>

        <!-- Stats Row -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-2xl p-6 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)]">
                <p class="text-sm font-medium text-gray-500 mb-2">Tickets Sold</p>
                <div class="flex items-end gap-2">
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($totalSold) }}</p>
                    @if($event->capacity)
                        <p class="text-sm text-gray-400 mb-1">/ {{ number_format($event->capacity) }}</p>
                    @endif
                </div>
            </div>
            <div class="bg-white rounded-2xl p-6 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)]">
                <p class="text-sm font-medium text-gray-500 mb-2">Ticket Price</p>
                <p class="text-3xl font-bold text-gray-900">
                    {{ $event->price > 0 ? '₦'.number_format($event->price / 100, 2) : 'Free' }}
                </p>
            </div>
            <div class="bg-white rounded-2xl p-6 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)]">
                <p class="text-sm font-medium text-gray-500 mb-2">Total Revenue</p>
                <p class="text-3xl font-bold text-gray-900 mb-2">₦{{ number_format($totalRevenue / 100, 2) }}</p>
            </div>
        </div>

        <!-- Attendees Table -->
        <div class="bg-white rounded-2xl p-0 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] overflow-hidden">
            <div class="p-6 flex justify-between items-center border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-900">Attendees</h2>
                <span class="text-sm font-medium text-gray-500 bg-gray-50 px-3 py-1 rounded-lg">{{ $orders->total() }} total orders</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-500 uppercase bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 font-semibold">Name</th>
                            <th class="px-6 py-4 font-semibold">Email</th>
                            <th class="px-6 py-4 font-semibold text-center">Qty</th>
                            <th class="px-6 py-4 font-semibold text-right">Paid</th>
                            <th class="px-6 py-4 font-semibold">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr class="bg-white border-b border-gray-50 last:border-0 hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $order->buyer_name }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $order->buyer_email }}</td>
                                <td class="px-6 py-4 text-center text-gray-700">{{ $order->quantity }}</td>
                                <td class="px-6 py-4 text-right font-bold text-gray-900">₦{{ number_format($order->amount / 100, 2) }}</td>
                                <td class="px-6 py-4 text-gray-500">{{ $order->created_at->format('M j, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">No ticket sales yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($orders->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>
</x-organizer-layout>
