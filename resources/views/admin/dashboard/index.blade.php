<x-app-layout>
  <div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      @if (session('status'))
        <div class="mb-4 p-3 bg-emerald-500/10 text-emerald-300 rounded ring-1 ring-emerald-500/30">{{ session('status') }}</div>
      @endif
      @if ($errors->any())
        <div class="mb-4 p-3 bg-red-500/10 text-red-300 rounded ring-1 ring-red-500/30">
          <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif
      <!-- Stat cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="rounded-2xl bg-white border border-eventbrite-gray-100 shadow-sm p-5">
          <div class="text-xs font-medium text-eventbrite-gray-600 uppercase tracking-wide">Total events</div>
          <div class="mt-2 text-3xl font-extrabold text-eventbrite-dark">{{ number_format($stats['events_total']) }}</div>
          <div class="mt-1 text-sm text-eventbrite-gray-600">Published: {{ number_format($stats['events_published']) }}</div>
        </div>
        <div class="rounded-2xl bg-white border border-eventbrite-gray-100 shadow-sm p-5">
          <div class="text-xs font-medium text-eventbrite-gray-600 uppercase tracking-wide">Orders today</div>
          <div class="mt-2 text-3xl font-extrabold text-eventbrite-dark">{{ number_format($stats['orders_today']) }}</div>
        </div>
        <div class="rounded-2xl bg-white border border-eventbrite-gray-100 shadow-sm p-5">
          <div class="text-xs font-medium text-eventbrite-gray-600 uppercase tracking-wide">Tickets today</div>
          <div class="mt-2 text-3xl font-extrabold text-eventbrite-dark">{{ number_format($stats['tickets_today']) }}</div>
        </div>
        <div class="rounded-2xl bg-white border border-eventbrite-gray-100 shadow-sm p-5">
          <div class="text-xs font-medium text-eventbrite-gray-600 uppercase tracking-wide">Revenue today</div>
          <div class="mt-2 text-3xl font-extrabold text-eventbrite-dark">₦{{ number_format($stats['revenue_today']/100, 2) }}</div>
        </div>
      </div>

      <!-- Quick links -->
      <div class="mt-8 grid grid-cols-1 sm:grid-cols-4 gap-6">
        <a href="{{ route('admin.events.create') }}" class="rounded-2xl bg-white border border-eventbrite-gray-100 shadow-sm p-6 hover:border-eventbrite-gray-400 hover:shadow-md transition">
          <div class="text-base font-semibold text-eventbrite-dark">Create event</div>
          <div class="text-sm text-eventbrite-gray-600 mt-1">Add a new event to your lineup.</div>
        </a>
        <a href="{{ route('admin.events.index') }}" class="rounded-2xl bg-white border border-eventbrite-gray-100 shadow-sm p-6 hover:border-eventbrite-gray-400 hover:shadow-md transition">
          <div class="text-base font-semibold text-eventbrite-dark">Manage events</div>
          <div class="text-sm text-eventbrite-gray-600 mt-1">Edit, publish, or remove events.</div>
        </a>
        <a href="{{ route('admin.orders.index') }}" class="rounded-2xl bg-white border border-eventbrite-gray-100 shadow-sm p-6 hover:border-eventbrite-gray-400 hover:shadow-md transition">
          <div class="text-base font-semibold text-eventbrite-dark">View orders</div>
          <div class="text-sm text-eventbrite-gray-600 mt-1">See sales and order details.</div>
        </a>
        <form method="POST" action="{{ route('admin.ops.backup') }}" class="rounded-2xl bg-white border border-eventbrite-gray-100 shadow-sm p-6">
          @csrf
          <div class="text-base font-semibold text-eventbrite-dark">Run backup</div>
          <div class="text-sm text-eventbrite-gray-600 mt-1">Starts a full app + DB backup now.</div>
          <div class="mt-3 flex items-center gap-2">
            <button class="inline-flex items-center px-4 py-2 rounded-md bg-tix-orange text-white text-sm font-semibold hover:bg-[#e55a2d]">Run backup</button>
            <a href="{{ route('admin.backups.index') }}" class="inline-flex items-center px-4 py-2 rounded-md border border-eventbrite-gray-100 bg-white text-sm text-eventbrite-dark hover:border-eventbrite-gray-400 hover:bg-[#f8f7fa]">View backups</a>
          </div>
        </form>
      </div>

      <!-- Sales chart (Simplified) -->
      <div class="mt-10 grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 rounded-2xl bg-white border border-eventbrite-gray-100 shadow-sm p-6">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-eventbrite-dark">Daily Revenue (Last 14 Days)</h2>
          </div>
          <!-- A simpler, larger canvas for a friendly bar chart -->
          <div class="relative h-64 w-full">
            <canvas id="salesChart"></canvas>
          </div>
          <script>
            document.addEventListener('DOMContentLoaded', () => {
              const ctx = document.getElementById('salesChart');
              if (!ctx || !window.Chart) return;
              
              // Only Revenue data to keep it simple
              const labels = @json($chart['labels']);
              const revenueData = @json(array_map(fn($v)=>$v/100, $chart['revenue']));
              
              const data = {
                labels: labels,
                datasets: [
                  {
                    label: 'Revenue (₦)',
                    data: revenueData,
                    backgroundColor: '#e55a2d', // Tix Orange
                    borderRadius: 6, // Rounded bars for a friendly look
                    barPercentage: 0.6,
                  }
                ]
              };
              
              const options = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                  legend: { display: false }, // Hide legend since there's only one metric
                  tooltip: {
                    backgroundColor: '#1e0a3c',
                    titleFont: { size: 14, family: "'Inter', sans-serif" },
                    bodyFont: { size: 16, weight: 'bold', family: "'Inter', sans-serif" },
                    padding: 12,
                    displayColors: false,
                    callbacks: {
                      label: function(context) {
                        return '₦' + context.parsed.y.toLocaleString();
                      }
                    }
                  }
                },
                scales: {
                  y: { 
                    beginAtZero: true, 
                    grid: { color: '#f3f4f6', drawBorder: false }, // Very light grid lines
                    border: { display: false },
                    ticks: { 
                      color: '#6f7287',
                      font: { family: "'Inter', sans-serif" },
                      callback: function(value) { return '₦' + value; },
                      maxTicksLimit: 6
                    } 
                  },
                  x: { 
                    grid: { display: false, drawBorder: false }, 
                    border: { display: false },
                    ticks: { 
                      color: '#6f7287',
                      font: { family: "'Inter', sans-serif" }
                    } 
                  }
                }
              };
              
              new Chart(ctx, { type: 'bar', data, options });
            });
          </script>
        </div>

        <!-- Upcoming events -->
        <div class="rounded-2xl bg-white border border-eventbrite-gray-100 shadow-sm p-6">
          <div class="flex items-center justify-between mb-3">
            <h2 class="text-xl font-bold text-eventbrite-dark">Upcoming</h2>
            <a href="{{ route('admin.events.index') }}" class="text-sm text-eventbrite-gray-600 hover:text-tix-orange">View all →</a>
          </div>
          <div class="space-y-3">
            @forelse($upcoming as $e)
              <div class="flex items-start justify-between gap-3">
                <div>
                  <div class="text-[11px] uppercase tracking-[0.18em] text-eventbrite-gray-600">{{ optional($e->starts_at)->format('D, M j • g:i A') }}</div>
                  <div class="font-semibold text-eventbrite-dark">{{ $e->title }}</div>
                  @if ($e->venue)
                    <div class="text-eventbrite-gray-600 text-sm">{{ $e->venue }}</div>
                  @endif
                </div>
                <div class="flex items-center gap-2">
                  <a href="{{ route('admin.events.edit', $e->id) }}" class="text-sm text-eventbrite-dark hover:text-tix-orange hover:underline">Edit</a>
                  <form method="POST" action="{{ route('admin.events.toggle', $e->id) }}" class="inline">
                    @csrf
                    @method('PATCH')
                    <button class="text-sm {{ $e->is_published ? 'text-amber-600' : 'text-emerald-600' }} hover:underline">
                      {{ $e->is_published ? 'Unpublish' : 'Publish' }}
                    </button>
                  </form>
                </div>
              </div>
            @empty
              <div class="text-center text-eventbrite-gray-600 py-6">No upcoming events.</div>
            @endforelse
          </div>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>
