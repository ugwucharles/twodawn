<x-organizer-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 animate-fade-in">
        <!-- Header Section -->
        <div class="mb-10">
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Organizer Dashboard</h1>
            <p class="text-gray-500 mt-1 font-medium">Welcome back! Here's what's happening with your events today.</p>
        </div>

        <!-- Stats Grid - 2 Cards Per Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
            <!-- Total Events -->
            <div class="group relative bg-gradient-to-br from-amber-50 to-amber-100 rounded-3xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-amber-200/50 hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all duration-500">
                <div class="flex items-start justify-between mb-6">
                    <div class="p-4 bg-white rounded-2xl group-hover:bg-amber-200 transition-colors duration-500 shadow-sm">
                        <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    @if($upcomingEvents > 0)
                        <span class="flex items-center text-xs font-bold text-amber-600 bg-white px-3 py-1.5 rounded-full uppercase tracking-wider shadow-sm">{{ $upcomingEvents }} Upcoming</span>
                    @endif
                </div>
                <p class="text-sm font-bold text-amber-400 uppercase tracking-widest mb-2">Total Events</p>
                <p class="text-5xl font-black text-amber-900">{{ number_format($totalEvents) }}</p>
            </div>

            <!-- Tickets Sold -->
            <div class="group relative bg-gradient-to-br from-amber-50 to-amber-100 rounded-3xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-amber-200/50 hover:shadow-[0_8px_30px(rgb(0,0,0,0.08)] transition-all duration-500">
                <div class="flex items-center justify-between mb-6">
                    <div class="p-3 bg-white rounded-2xl group-hover:bg-amber-200 transition-colors duration-500 shadow-sm">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
                    </div>
                </div>
                <p class="text-sm font-bold text-amber-400 uppercase tracking-widest mb-2">Tickets Sold</p>
                <p class="text-4xl font-black text-amber-900">{{ number_format($totalTicketsSold) }}</p>
            </div>

            <!-- Wallet Balance -->
            <div class="group relative bg-gradient-to-br from-amber-50 to-amber-100 rounded-3xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-amber-200/50 hover:shadow-[0_8px_30px(rgb(0,0,0,0.08)] transition-all duration-500">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-white rounded-2xl group-hover:bg-amber-200 transition-colors duration-500 shadow-sm">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                    </div>
                    <span class="text-[10px] font-bold text-amber-600 bg-white px-2 py-1 rounded-full uppercase tracking-wider shadow-sm">Available</span>
                </div>
                <p class="text-sm font-bold text-amber-900 uppercase tracking-widest">Wallet Balance</p>
                <div class="flex items-baseline gap-1 mt-1">
                    <span class="text-xl font-black text-amber-900">₦</span>
                    <p class="text-3xl font-black text-amber-900">{{ number_format($walletBalance, 2) }}</p>
                </div>
            </div>

            <!-- Total Revenue -->
            <div class="group relative bg-gradient-to-br from-amber-50 to-amber-100 rounded-3xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-amber-200/50 hover:shadow-[0_8px_30px(rgb(0,0,0,0.08)] transition-all duration-500 overflow-hidden">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-amber-200 rounded-full blur-3xl opacity-50"></div>
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-white rounded-2xl group-hover:bg-amber-200 transition-colors duration-500 shadow-sm">
                            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                    </div>
                    <p class="text-sm font-bold text-amber-400 uppercase tracking-widest">Gross Revenue</p>
                    <div class="flex items-baseline gap-1 mt-1">
                        <span class="text-xl font-black text-amber-400">₦</span>
                        <p class="text-3xl font-black text-amber-900">{{ number_format($totalRevenue / 100, 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Central -->
        <div class="mb-10">
            <!-- Capacity Pie Only -->
            <div class="w-full bg-gradient-to-br from-amber-50 to-amber-100 rounded-[32px] p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-amber-200/50 flex flex-col">
                <h2 class="text-xl font-black text-gray-900 mb-2">Ticket Analytics</h2>
                <p class="text-sm text-gray-500 font-medium mb-8">Overall sales performance</p>
                
                <div class="flex-1 flex flex-col items-center justify-center relative">
                    <div class="relative h-56 w-56 mx-auto">
                        <canvas id="salesChart"></canvas>
                        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Sold</span>
                            <span class="text-3xl font-black text-gray-900">
                                @php 
                                    $percent = $totalCapacity > 0 ? round(($totalTicketsSold / $totalCapacity) * 100) : 0;
                                @endphp
                                {{ $percent }}%
                            </span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-8">
                    <div class="p-4 bg-white/50 rounded-2xl border border-amber-100/50">
                        <span class="text-[10px] font-black text-amber-400 uppercase tracking-widest block mb-1">Sold</span>
                        <span class="text-lg font-black text-amber-700">{{ number_format($totalTicketsSold) }}</span>
                    </div>
                    <div class="p-4 bg-white/50 rounded-2xl border border-amber-100/50">
                        <span class="text-[10px] font-black text-orange-400 uppercase tracking-widest block mb-1">Left</span>
                        <span class="text-lg font-black text-orange-700">{{ number_format($leftTickets) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity Sections -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Events Summary -->
            <div class="lg:col-span-5 bg-gradient-to-br from-amber-50 to-amber-100 rounded-[32px] p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-amber-200/50 animate-slide-up">
                <div class="flex justify-between items-center mb-8">
                    <h2 class="text-xl font-black text-gray-900">Your Events</h2>
                    <a href="{{ route('organizer.events.create') }}" class="p-2 bg-blue-600 text-white rounded-xl shadow-lg shadow-blue-200 hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    </a>
                </div>
                
                <div class="space-y-4">
                    @forelse($events->take(4) as $event)
                    <div class="group flex items-center p-4 bg-gradient-to-br from-white to-amber-50 rounded-[24px] border border-amber-200/50 hover:border-blue-100 hover:bg-gradient-to-br hover:from-blue-50 hover:to-blue-100 transition-all duration-300">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-100 to-amber-200 shadow-sm flex flex-col items-center justify-center font-black group-hover:bg-gradient-to-br group-hover:from-blue-600 group-hover:to-blue-700 group-hover:text-white transition-all duration-300">
                            @if($event->starts_at)
                                <span class="text-base">{{ $event->starts_at->format('d') }}</span>
                                <span class="text-[8px] uppercase tracking-tighter -mt-1 opacity-70">{{ $event->starts_at->format('M') }}</span>
                            @else
                                <span class="text-[10px]">TBD</span>
                            @endif
                        </div>
                        <div class="flex-1 mx-4 min-w-0">
                            <h3 class="font-bold text-gray-900 text-sm truncate group-hover:text-blue-700 transition-colors">{{ $event->title }}</h3>
                            <div class="flex items-center gap-3 mt-1">
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ $event->orders()->where('status', 'paid')->sum('quantity') }} Sold</span>
                                <div class="w-1 h-1 bg-gray-300 rounded-full"></div>
                                <span class="text-[10px] font-bold text-blue-500 uppercase tracking-widest">Active</span>
                            </div>
                        </div>
                        <a href="{{ route('organizer.events.show', $event) }}" class="p-2.5 rounded-xl bg-gradient-to-br from-amber-100 to-amber-200 border border-amber-200/50 text-gray-400 group-hover:text-blue-600 group-hover:border-blue-100 group-hover:shadow-sm transition-all duration-300">
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
            <div class="lg:col-span-7 bg-gradient-to-br from-amber-50 to-amber-100 rounded-[32px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-amber-200/50 overflow-hidden">
                <div class="p-8 border-b border-amber-200/50 flex justify-between items-center bg-gradient-to-br from-amber-100 to-amber-200">
                    <div>
                        <h2 class="text-xl font-black text-gray-900">Recent Transactions</h2>
                        <p class="text-sm text-gray-500 font-medium">Latest payments received</p>
                    </div>
                    <a href="#" class="text-xs font-bold text-blue-600 hover:text-blue-700 bg-white px-4 py-2 rounded-xl transition-all">View All</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-[11px] font-black text-gray-400 uppercase tracking-widest border-b border-amber-200/50">
                                <th class="px-8 py-5">Event</th>
                                <th class="px-8 py-5">Date</th>
                                <th class="px-8 py-5 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-amber-200/50">
                            @forelse($recentOrders as $order)
                            <tr class="group hover:bg-gradient-to-br hover:from-amber-100 hover:to-amber-200 transition-colors">
                                <td class="px-8 py-6">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-gray-900 leading-tight">{{ $order->event->title ?? 'Deleted Event' }}</span>
                                        <span class="text-[11px] font-bold text-gray-400 mt-1 uppercase tracking-wider">{{ $order->reference ?? 'TXN-'.$order->id }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-sm font-semibold text-gray-500">{{ $order->created_at->format('M j, Y') }}</td>
                                <td class="px-8 py-6 text-right">
                                    <span class="text-sm font-black text-gray-900">₦{{ number_format($order->amount / 100, 2) }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-8 py-20 text-center text-gray-400 font-medium italic">No recent transactions.</td>
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
