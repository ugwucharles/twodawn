<x-organizer-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 animate-fade-in">
        <!-- Header Section -->
        <div class="mb-10">
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Organizer Dashboard</h1>
            <p class="text-gray-500 mt-1 font-medium">Welcome back! Here's what's happening with your events today.</p>
        </div>

        <!-- Stats Grid - 4 Cards Per Row -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <!-- Total Events -->
            <div class="bg-white rounded-2xl p-6 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-gray-100 flex flex-col justify-between min-h-[140px]">
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-2">Total Events</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($totalEvents) }}</p>
                </div>
                @if($upcomingEvents > 0)
                    <div class="mt-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700">{{ $upcomingEvents }} Upcoming</span>
                    </div>
                @endif
            </div>

            <!-- Tickets Sold -->
            <div class="bg-white rounded-2xl p-6 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-gray-100 flex flex-col justify-between min-h-[140px]">
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-2">Tickets Sold</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($totalTicketsSold) }}</p>
                </div>
            </div>

            <!-- Wallet Balance -->
            <div class="bg-white rounded-2xl p-6 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-gray-100 flex flex-col justify-between min-h-[140px]">
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-2">Wallet Balance</p>
                    <div class="flex items-baseline gap-1">
                        <span class="text-xl font-bold text-gray-900">₦</span>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($walletBalance, 2) }}</p>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700">Available</span>
                </div>
            </div>

            <!-- Gross Revenue -->
            <div class="bg-white rounded-2xl p-6 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-gray-100 flex flex-col justify-between min-h-[140px]">
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-2">Gross Revenue</p>
                    <p class="text-3xl font-bold text-gray-900">₦{{ number_format($totalRevenue / 100, 2) }}</p>
                </div>
            </div>
        </div>

        <!-- Charts Central -->
        <div class="mb-10">
            <!-- Capacity Pie Only -->
            <div class="w-full bg-white rounded-2xl p-8 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-gray-100 flex flex-col">
                <h2 class="text-xl font-bold text-gray-900 mb-2">Ticket Analytics</h2>
                <p class="text-sm text-gray-500 font-medium mb-8">Overall sales performance</p>
                
                <div class="flex-1 flex flex-col items-center justify-center relative">
                    <div class="relative h-56 w-56 mx-auto">
                        <canvas id="salesChart"></canvas>
                        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Sold</span>
                            <span class="text-3xl font-bold text-gray-900">
                                @php 
                                    $percent = $totalCapacity > 0 ? round(($totalTicketsSold / $totalCapacity) * 100) : 0;
                                @endphp
                                {{ $percent }}%
                            </span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-8">
                    <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                        <span class="text-xs font-semibold text-gray-500 block mb-1">Sold</span>
                        <span class="text-lg font-bold text-gray-900">{{ number_format($totalTicketsSold) }}</span>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                        <span class="text-xs font-semibold text-gray-500 block mb-1">Left</span>
                        <span class="text-lg font-bold text-gray-900">{{ number_format($leftTickets) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity Sections -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Events Summary -->
            <div class="lg:col-span-5 bg-white rounded-2xl p-8 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-gray-100 animate-slide-up flex flex-col justify-between">
                <div class="flex justify-between items-center mb-8">
                    <h2 class="text-xl font-bold text-gray-900">Your Events</h2>
                    <a href="{{ route('organizer.events.create') }}" class="p-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow-lg shadow-blue-200 hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    </a>
                </div>
                
                <div class="space-y-4">
                    @forelse($events->take(4) as $event)
                    <div class="flex items-center relative p-4 bg-white rounded-xl border border-gray-100 hover:bg-gray-50 transition-colors duration-300">
                        <span class="absolute top-0 right-0 m-2 bg-green-100 text-green-700 text-xs font-semibold px-2 py-0.5 rounded-full">Live</span>
                        <div class="w-12 h-12 rounded-xl bg-gray-50 border border-gray-100 flex flex-col items-center justify-center font-bold text-gray-900">
                            @if($event->starts_at)
                                <span class="text-base">{{ $event->starts_at->format('d') }}</span>
                                <span class="text-[8px] uppercase tracking-tighter -mt-1 opacity-70 text-gray-500">{{ $event->starts_at->format('M') }}</span>
                            @else
                                <span class="text-[10px] text-gray-500">TBD</span>
                            @endif
                        </div>
                        <div class="flex-1 mx-4 min-w-0 text-gray-900">
                            <h3 class="font-bold text-gray-900 text-sm truncate hover:text-blue-600 transition-colors">{{ $event->title }}</h3>
                            <div class="flex items-center gap-3 mt-1">
                                <span class="text-xs font-semibold text-gray-500">{{ $event->orders()->where('status', 'paid')->sum('quantity') }} Sold</span>
                                <div class="w-1.5 h-1.5 bg-gray-300 rounded-full"></div>
                                <span class="text-xs font-semibold text-green-600">Active</span>
                            </div>
                        </div>
                        <a href="{{ route('organizer.events.show', $event) }}" class="p-2.5 rounded-xl bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-500 hover:text-gray-900 transition-all duration-300">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                        </a>
                    </div>
                    @empty
                    <div class="py-12 flex flex-col items-center justify-center grayscale opacity-50">
                        <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">No events yet</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Transactions -->
            <div class="lg:col-span-7 bg-white rounded-2xl shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] overflow-hidden border border-gray-100">
                <div class="p-8 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Recent Transactions</h2>
                        <p class="text-sm text-gray-500 font-medium">Latest payments received</p>
                    </div>
                    <a href="{{ route('organizer.orders.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700 bg-white border border-gray-200 px-4 py-2 rounded-xl shadow-sm transition-all">View All</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50 border-b border-gray-100">
                                <th class="px-8 py-5">Event</th>
                                <th class="px-8 py-5">Date</th>
                                <th class="px-8 py-5 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($recentOrders as $order)
                            <tr class="group hover:bg-gray-50 transition-colors">
                                <td class="px-8 py-6">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-gray-900 leading-tight">{{ $order->event->title ?? 'Deleted Event' }}</span>
                                        <span class="text-xs font-medium text-gray-400 mt-1 uppercase tracking-wider">{{ $order->paystack_reference ?? 'TXN-'.$order->id }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-sm font-medium text-gray-500">{{ $order->created_at->format('M j, Y') }}</td>
                                <td class="px-8 py-6 text-right">
                                    <span class="text-sm font-bold text-gray-900">₦{{ number_format($order->amount / 100, 2) }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-8 py-20 text-center text-gray-500 font-medium italic">No recent transactions.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <style>
        .animate-fade-in { animation: fadeIn 0.8s ease-out forwards; }
        .animate-slide-up { animation: slideUp 0.8s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Chart Defaults
            if (window.Chart) {
                Chart.defaults.font.family = "'Montserrat', sans-serif";
                Chart.defaults.color = '#9ca3af';
                Chart.defaults.font.weight = '600';
                Chart.defaults.font.size = 11;
            }

            // Revenue Chart
            const revCtx = document.getElementById('revenueChart');
            if (revCtx && window.Chart) {
                const labels = @json($months);
                const data = @json($revenueStats);

                const ctx = revCtx.getContext('2d');
                const gradient = ctx.createLinearGradient(0, 0, 0, 320);
                gradient.addColorStop(0, 'rgba(59, 130, 246, 0.15)');
                gradient.addColorStop(0.5, 'rgba(59, 130, 246, 0.05)');
                gradient.addColorStop(1, 'rgba(59, 130, 246, 0)');

                new Chart(revCtx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Revenue',
                            data: data,
                            borderColor: '#3b82f6',
                            backgroundColor: gradient,
                            borderWidth: 4,
                            fill: true,
                            tension: 0.5,
                            pointRadius: 0,
                            pointHoverRadius: 6,
                            pointHoverBackgroundColor: '#3b82f6',
                            pointHoverBorderColor: '#fff',
                            pointHoverBorderWidth: 3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { intersect: false, mode: 'index' },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#fff',
                                titleColor: '#111827',
                                bodyColor: '#3b82f6',
                                bodyFont: { weight: '800', size: 14 },
                                borderColor: '#f3f4f6',
                                borderWidth: 1,
                                padding: 12,
                                displayColors: false,
                                cornerRadius: 16,
                                callbacks: {
                                    label: function(context) { return '₦' + context.parsed.y.toLocaleString(); }
                                }
                            }
                        },
                        scales: {
                            y: {
                                border: { display: false },
                                grid: { color: '#f9fafb' },
                                ticks: { 
                                    padding: 15,
                                    callback: function(value) { return '₦' + (value >= 1000 ? (value/1000) + 'k' : value); }
                                }
                            },
                            x: {
                                border: { display: false },
                                grid: { display: false },
                                ticks: { padding: 10 }
                            }
                        }
                    }
                });
            }

            // Sales Chart
            const salesCtx = document.getElementById('salesChart');
            if (salesCtx && window.Chart) {
                const sold = {{ $totalTicketsSold }};
                const left = {{ $leftTickets }};
                const total = {{ $totalCapacity ?: 1 }};
                
                new Chart(salesCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Sold', 'Left'],
                        datasets: [{
                            data: [sold, left],
                            backgroundColor: ['#3b82f6', '#f3f4f6'],
                            borderWidth: 0,
                            borderRadius: 20,
                            hoverOffset: 10
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '82%',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#111827',
                                padding: 10,
                                cornerRadius: 12,
                                callbacks: {
                                    label: function(context) {
                                        return ' ' + context.label + ': ' + context.raw.toLocaleString();
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
