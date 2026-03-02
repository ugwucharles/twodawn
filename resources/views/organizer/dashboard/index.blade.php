<x-organizer-layout>
    <div class="max-w-7xl mx-auto mt-2">
        <!-- Stats Row -->
        <div class="flex flex-row gap-4 mb-6">
            <div class="flex-1 bg-white rounded-[20px] p-5 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-gray-100">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Total Events</p>
                    <p class="text-2xl font-black text-gray-900">{{ number_format($totalEvents) }}</p>
                </div>
            </div>
            <div class="flex-1 bg-white rounded-[20px] p-5 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-gray-100">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Total Tickets</p>
                    <p class="text-2xl font-black text-gray-900">{{ number_format($totalTicketsSold) }}</p>
                </div>
            </div>
            <div class="flex-1 bg-white rounded-[20px] p-5 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-gray-100">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Total Revenue</p>
                    <p class="text-2xl font-black text-gray-900">₦{{ number_format($totalRevenue / 100, 2) }}</p>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Revenue Statistics -->
            <div class="lg:col-span-2 bg-white rounded-2xl p-6 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)]">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-lg font-bold text-gray-900">Revenue Statistics</h2>
                    <select class="bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block pl-4 pr-10 py-2.5 min-w-[140px]">
                        <option>Annually</option>
                        <option>Monthly</option>
                        <option>Weekly</option>
                    </select>
                </div>
                <div class="relative h-64 w-full">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <!-- Sales Statistics -->
            <div class="bg-white rounded-2xl p-6 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex flex-col">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-lg font-bold text-gray-900">Sales Statistics</h2>
                    <select class="bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block pl-4 pr-10 py-2.5 min-w-[140px]">
                        <option>Monthly</option>
                        <option>Annually</option>
                    </select>
                </div>
                
                <div class="flex-1 flex flex-col items-center justify-center relative">
                    <div class="relative h-48 w-48 mx-auto -mt-4">
                        <canvas id="salesChart"></canvas>
                        <!-- Center Text -->
                        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                            <span class="text-xs text-gray-500 mb-1">Total Tickets</span>
                            <span class="text-lg font-bold text-gray-900 -mt-1">{{ number_format($totalCapacity) }}</span>
                            <span class="text-xs font-semibold text-gray-400 -mt-1">(100%)</span>
                        </div>
                    </div>
                </div>

                <div class="flex justify-center gap-6 mt-4">
                    <div class="flex items-center">
                        <span class="w-2 h-2 rounded-full bg-blue-300 mr-2"></span>
                        <span class="text-sm font-medium text-gray-600">Sold Tickets</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-2 h-2 rounded-full bg-orange-300 mr-2"></span>
                        <span class="text-sm font-medium text-gray-600">Left Tickets</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Transaction History -->
            <div class="lg:col-span-2 bg-white rounded-2xl p-0 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] overflow-hidden">
                <div class="p-6 flex justify-between items-center border-b border-gray-100">
                    <h2 class="text-lg font-bold text-gray-900">Transaction History</h2>
                    <select class="bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block pl-4 pr-10 py-2.5 min-w-[160px]">
                        <option>Last 24 Hours</option>
                        <option>Last 7 Days</option>
                    </select>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-500 uppercase bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-semibold">Transaction ID</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Date</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Method</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Status</th>
                                <th scope="col" class="px-6 py-4 font-semibold text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentOrders as $order)
                            <tr class="bg-white border-b border-gray-50 last:border-0 hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $order->reference ?? 'TXN'.str_pad($order->id, 10, '0', STR_PAD_LEFT) }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $order->created_at->format('M j, Y') }}</td>
                                <td class="px-6 py-4 text-gray-600">Credit Card</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="h-2 w-2 rounded-full bg-green-500 mr-2"></div>
                                        <span class="font-medium text-gray-700">Successful</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-bold text-gray-900 text-right">₦{{ number_format($order->amount / 100, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">No transactions found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Visitors/Recent Events mini card (Bonus) -->
            <div class="bg-white rounded-2xl p-6 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)]">
                <h2 class="text-lg font-bold text-gray-900 mb-6">Recent Events</h2>
                <div class="space-y-4">
                    @forelse($events->take(3) as $event)
                    <div class="flex items-center bg-gray-50 p-3 rounded-xl border border-gray-100">
                        <div class="w-12 h-12 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-lg mr-4 text-center p-1 leading-tight">
                            @if($event->starts_at)
                                {{ $event->starts_at->format('d') }}
                                <span class="text-[9px] block uppercase -mt-1">{{ $event->starts_at->format('M') }}</span>
                            @else
                                <span class="text-xs">TBD</span>
                            @endif
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 text-sm truncate">{{ $event->title }}</h3>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $event->orders()->where('status', 'paid')->sum('quantity') }} tickets sold</p>
                        </div>
                        <a href="{{ route('organizer.events.show', $event) }}" class="text-blue-500 bg-blue-50 hover:bg-blue-100 p-2 rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </a>
                    </div>
                    @empty
                    <p class="text-center text-sm text-gray-500">No events yet.</p>
                    @endforelse
                </div>
                <div class="mt-6 pt-4 border-t border-gray-100">
                     <a href="{{ route('organizer.events.create') }}" class="w-full text-center block text-sm font-semibold text-blue-600 hover:text-blue-700">
                        + Create a New Event
                     </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Revenue Chart (Line Chart with Area)
            const revCtx = document.getElementById('revenueChart');
            if (revCtx && window.Chart) {
                const labels = @json($months);
                const data = @json($revenueStats);

                // gradient fill
                const gradient = revCtx.getContext('2d').createLinearGradient(0, 0, 0, 400);
                gradient.addColorStop(0, 'rgba(59, 130, 246, 0.2)'); // blue-500 20%
                gradient.addColorStop(1, 'rgba(59, 130, 246, 0)');

                new Chart(revCtx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Revenue',
                            data: data,
                            borderColor: '#3b82f6', // blue-500
                            backgroundColor: gradient,
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4, // smooth curves
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#3b82f6',
                            pointBorderWidth: 2,
                            pointRadius: 3,
                            pointHoverRadius: 5
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#111827',
                                titleFont: { size: 13, family: "'Inter', sans-serif" },
                                bodyFont: { size: 14, weight: 'bold', family: "'Inter', sans-serif" },
                                padding: 10,
                                displayColors: false,
                                callbacks: {
                                    label: function(context) { return '₦' + context.parsed.y.toLocaleString(); }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                border: { display: false },
                                grid: { color: '#f3f4f6', drawBorder: false, borderDash: [5, 5] },
                                ticks: { 
                                    color: '#9ca3af',
                                    font: { family: "'Inter', sans-serif", size: 11 },
                                    callback: function(value) { return value >= 1000 ? (value/1000) + 'k' : value; },
                                    maxTicksLimit: 6
                                }
                            },
                            x: {
                                grid: { display: false, drawBorder: false },
                                border: { display: false },
                                ticks: { color: '#9ca3af', font: { family: "'Inter', sans-serif", size: 11 } }
                            }
                        }
                    }
                });
            }

            // Sales Chart (Doughnut Chart)
            const salesCtx = document.getElementById('salesChart');
            if (salesCtx && window.Chart) {
                const sold = {{ $totalTicketsSold }};
                const left = {{ $leftTickets }};
                
                // If 0 total, fake data to make the circle visible
                const chartData = (sold === 0 && left === 0) ? [0, 1] : [sold, left];

                new Chart(salesCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Sold Tickets', 'Left Tickets'],
                        datasets: [{
                            data: chartData,
                            backgroundColor: ['#93c5fd', '#fdba74'], // blue-300, orange-300
                            borderWidth: 0,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '75%', // makes it a thin ring
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let val = context.raw;
                                        if (sold === 0 && left === 0 && context.dataIndex === 1) val = 0; // Fix fake data tooltip
                                        return ' ' + context.label + ': ' + val;
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
</x-organizer-layout>
