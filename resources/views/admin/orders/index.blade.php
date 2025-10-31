<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white/5 ring-1 ring-white/10 rounded-2xl">
                <div class="p-6">
                    <form method="GET" class="mb-4 flex items-end gap-3">
                        <div>
                            <label for="event_id" class="block text-sm text-zinc-300">Filter by event</label>
                            <select id="event_id" name="event_id" class="mt-1 block rounded-md bg-black/30 border border-white/10 focus:border-white/30 focus:ring-0 text-white">
                                <option value="">All events</option>
                                @foreach ($events as $e)
                                    <option value="{{ $e->id }}" @selected($eventId == $e->id)>{{ $e->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-zinc-300">From</label>
                            <input type="date" name="from" value="{{ request('from') }}" class="mt-1 block rounded-md bg-black/30 border border-white/10 focus:border-white/30 focus:ring-0 text-white px-3 py-2" />
                        </div>
                        <div>
                            <label class="block text-sm text-zinc-300">To</label>
                            <input type="date" name="to" value="{{ request('to') }}" class="mt-1 block rounded-md bg-black/30 border border-white/10 focus:border-white/30 focus:ring-0 text-white px-3 py-2" />
                        </div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <button class="inline-flex items-center px-4 py-2 rounded-md bg-white text-black text-sm hover:bg-zinc-100">Apply</button>
                            <a href="{{ route('admin.orders.export', ['event_id' => request('event_id'), 'from' => request('from'), 'to' => request('to')]) }}" class="inline-flex items-center px-4 py-2 bg-white/10 ring-1 ring-white/10 rounded-md text-sm hover:bg-white/20">Export orders CSV</a>
<a href="{{ route('admin.orders.export.summary', ['event_id' => request('event_id'), 'from' => request('from'), 'to' => request('to')]) }}" class="inline-flex items-center px-4 py-2 bg-white/10 ring-1 ring-white/10 rounded-md text-sm hover:bg-white/20">Export sales summary</a>
                            <a href="{{ route('admin.orders.export.summaryDaily', ['event_id' => request('event_id'), 'from' => request('from'), 'to' => request('to')]) }}" class="inline-flex items-center px-4 py-2 bg-white/10 ring-1 ring-white/10 rounded-md text-sm hover:bg-white/20">Export daily breakdown</a>
                            <a href="{{ route('admin.checkins.export', ['event_id' => request('event_id'), 'from' => request('from'), 'to' => request('to')]) }}" class="inline-flex items-center px-4 py-2 bg-white/10 ring-1 ring-white/10 rounded-md text-sm hover:bg-white/20">Export check-ins</a>
                        </div>
                    </form>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-white/10">
                            <thead>
                                <tr class="text-zinc-400 text-xs uppercase tracking-wider">
                                    <th class="px-3 py-2 text-left">Date</th>
                                    <th class="px-3 py-2 text-left">Event</th>
                                    <th class="px-3 py-2 text-left">Buyer</th>
                                    <th class="px-3 py-2 text-left">Qty</th>
                                    <th class="px-3 py-2 text-left">Amount</th>
                                    <th class="px-3 py-2 text-left">Status</th>
                                    <th class="px-3 py-2"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/10">
                                @forelse ($orders as $order)
                                    <tr>
                                        <td class="px-3 py-2">{{ $order->created_at->format('Y-m-d H:i') }}</td>
                                        <td class="px-3 py-2">{{ $order->event->title }}</td>
                                        <td class="px-3 py-2">{{ $order->buyer_name }}<div class="text-xs text-gray-500">{{ $order->buyer_email }}</div></td>
                                        <td class="px-3 py-2">{{ $order->quantity }}</td>
                                        <td class="px-3 py-2">₦{{ number_format($order->amount / 100, 2) }}</td>
                                        <td class="px-3 py-2">
                                            @switch($order->status)
                                                @case('paid')
                                                    <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">Paid</span>
                                                    @break
                                                @case('failed')
                                                    <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded">Failed</span>
                                                    @break
                                                @default
                                                    <span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded">{{ ucfirst($order->status) }}</span>
                                            @endswitch
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            <a href="{{ route('admin.orders.show', $order) }}" class="text-indigo-300 hover:underline">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="px-3 py-6 text-center text-gray-500" colspan="7">No orders yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $orders->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
