<x-app-layout>
    <div class="py-6 bg-black min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-black border border-white/10 rounded-2xl shadow-xl overflow-hidden">
                <div class="p-6 sm:p-8">
                    <form method="GET" class="mb-6 flex flex-col xl:flex-row xl:items-end justify-between gap-6">
                        <div class="flex flex-wrap items-end gap-4">
                            <div>
                                <label for="event_id" class="block text-sm font-medium text-white/70 mb-1">Filter by event</label>
                                <select id="event_id" name="event_id" class="block w-full sm:w-64 rounded-xl bg-black border border-white/20 text-white focus:border-white focus:ring-1 focus:ring-white transition-colors px-4 py-2 sm:text-sm">
                                    <option value="">All events</option>
                                    @foreach ($events as $e)
                                        <option value="{{ $e->id }}" @selected($eventId == $e->id)>{{ $e->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-white/70 mb-1">From</label>
                                <input type="date" name="from" value="{{ request('from') }}" class="block rounded-xl bg-black border border-white/20 text-white focus:border-white focus:ring-1 focus:ring-white transition-colors px-4 py-2 sm:text-sm" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-white/70 mb-1">To</label>
                                <input type="date" name="to" value="{{ request('to') }}" class="block rounded-xl bg-black border border-white/20 text-white focus:border-white focus:ring-1 focus:ring-white transition-colors px-4 py-2 sm:text-sm" />
                            </div>
                            <button class="inline-flex items-center justify-center px-6 py-2 rounded-xl bg-white text-black font-semibold text-sm hover:bg-white/90 transition-colors h-[42px]">Apply</button>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <a href="{{ route('admin.orders.export', ['event_id' => request('event_id'), 'from' => request('from'), 'to' => request('to')]) }}" class="inline-flex items-center px-4 py-2 bg-transparent border border-white/20 rounded-xl text-white text-sm hover:bg-white/10 transition-colors">Export CSV</a>
                            <a href="{{ route('admin.orders.export.summary', ['event_id' => request('event_id'), 'from' => request('from'), 'to' => request('to')]) }}" class="inline-flex items-center px-4 py-2 bg-transparent border border-white/20 rounded-xl text-white text-sm hover:bg-white/10 transition-colors">Sales sum.</a>
                            <a href="{{ route('admin.orders.export.summaryDaily', ['event_id' => request('event_id'), 'from' => request('from'), 'to' => request('to')]) }}" class="inline-flex items-center px-4 py-2 bg-transparent border border-white/20 rounded-xl text-white text-sm hover:bg-white/10 transition-colors">Daily brk.</a>
                            <a href="{{ route('admin.checkins.export', ['event_id' => request('event_id'), 'from' => request('from'), 'to' => request('to')]) }}" class="inline-flex items-center px-4 py-2 bg-transparent border border-white/20 rounded-xl text-white text-sm hover:bg-white/10 transition-colors">Check-ins</a>
                        </div>
                    </form>
                    
                    <div class="overflow-x-auto rounded-xl border border-white/10">
                        <table class="min-w-full divide-y divide-white/10">
                            <thead class="bg-white/5">
                                <tr class="text-white/60 text-xs uppercase tracking-wider">
                                    <th class="px-6 py-4 text-center font-medium">Date</th>
                                    <th class="px-6 py-4 text-center font-medium">Event</th>
                                    <th class="px-6 py-4 text-center font-medium">Buyer</th>
                                    <th class="px-6 py-4 text-center font-medium">Qty</th>
                                    <th class="px-6 py-4 text-center font-medium">Amount</th>
                                    <th class="px-6 py-4 text-center font-medium">Status</th>
                                    <th class="px-6 py-4 text-center font-medium"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/10 bg-black">
                                @forelse ($orders as $order)
                                    <tr class="hover:bg-white/5 transition-colors">
                                        <td class="px-6 py-4 text-center whitespace-nowrap text-sm text-white/80">{{ $order->created_at->format('Y-m-d H:i') }}</td>
                                        <td class="px-6 py-4 text-center text-sm text-white font-medium">{{ $order->event->title }}</td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="text-sm text-white">{{ $order->buyer_name }}</div>
                                            <div class="text-xs text-white/50">{{ $order->buyer_email }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-center text-sm text-white">{{ $order->quantity }}</td>
                                        <td class="px-6 py-4 text-center text-sm text-white font-semibold">₦{{ number_format($order->amount / 100, 2) }}</td>
                                        <td class="px-6 py-4 text-center whitespace-nowrap">
                                            @switch($order->status)
                                                @case('paid')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Paid</span>
                                                    @break
                                                @case('failed')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-500/10 text-red-400 border border-red-500/20">Failed</span>
                                                    @break
                                                @default
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-white/10 text-white/70 border border-white/20">{{ ucfirst($order->status) }}</span>
                                            @endswitch
                                        </td>
                                        <td class="px-6 py-4 text-center whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('admin.orders.show', $order) }}" class="text-white/60 hover:text-white transition-colors">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="px-6 py-12 text-center text-white/50 text-sm" colspan="7">No orders found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($orders->hasPages())
                        <div class="mt-6">
                            {{ $orders->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
