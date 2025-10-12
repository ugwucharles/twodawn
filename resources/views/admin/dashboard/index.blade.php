<x-app-layout>
  <div class="py-6">
    <div class="max-w-7xl mx-auto px-6">
      <!-- Stat cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="rounded-2xl bg-white/5 ring-1 ring-white/10 p-5">
          <div class="text-zinc-400 text-sm">Total Events</div>
          <div class="mt-2 text-3xl font-extrabold">{{ number_format($stats['events_total']) }}</div>
          <div class="mt-1 text-zinc-400 text-sm">Published: {{ number_format($stats['events_published']) }}</div>
        </div>
        <div class="rounded-2xl bg-white/5 ring-1 ring-white/10 p-5">
          <div class="text-zinc-400 text-sm">Orders Today</div>
          <div class="mt-2 text-3xl font-extrabold">{{ number_format($stats['orders_today']) }}</div>
        </div>
        <div class="rounded-2xl bg-white/5 ring-1 ring-white/10 p-5">
          <div class="text-zinc-400 text-sm">Tickets Today</div>
          <div class="mt-2 text-3xl font-extrabold">{{ number_format($stats['tickets_today']) }}</div>
        </div>
        <div class="rounded-2xl bg-white/5 ring-1 ring-white/10 p-5">
          <div class="text-zinc-400 text-sm">Revenue Today</div>
          <div class="mt-2 text-3xl font-extrabold">₦{{ number_format($stats['revenue_today']/100, 2) }}</div>
        </div>
      </div>

      <!-- Quick links -->
      <div class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-6">
        <a href="{{ route('admin.events.create') }}" class="rounded-2xl bg-white/5 ring-1 ring-white/10 p-6 hover:bg-white/10 transition">
          <div class="text-lg font-semibold">Create Event</div>
          <div class="text-zinc-400 text-sm mt-1">Add a new event to your lineup.</div>
        </a>
        <a href="{{ route('admin.events.index') }}" class="rounded-2xl bg-white/5 ring-1 ring-white/10 p-6 hover:bg-white/10 transition">
          <div class="text-lg font-semibold">Manage Events</div>
          <div class="text-zinc-400 text-sm mt-1">Edit, publish, or remove events.</div>
        </a>
        <a href="{{ route('admin.orders.index') }}" class="rounded-2xl bg-white/5 ring-1 ring-white/10 p-6 hover:bg-white/10 transition">
          <div class="text-lg font-semibold">View Orders</div>
          <div class="text-zinc-400 text-sm mt-1">See sales and order details.</div>
        </a>
      </div>

      <!-- Sales chart -->
      <div class="mt-10 grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 rounded-2xl bg-white/5 ring-1 ring-white/10 p-6">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold">Sales (last 14 days)</h2>
          </div>
          <canvas id="salesChart" height="120"></canvas>
          <script>
            document.addEventListener('DOMContentLoaded', () => {
              const ctx = document.getElementById('salesChart');
              if (!ctx || !window.Chart) return;
              const data = {
                labels: @json($chart['labels']),
                datasets: [
                  {
                    label: 'Tickets',
                    data: @json($chart['tickets']),
                    borderColor: 'rgba(99,102,241,1)',
                    backgroundColor: 'rgba(99,102,241,0.25)',
                    tension: 0.35,
                    fill: true,
                  },
                  {
                    label: 'Revenue (₦)',
                    data: @json(array_map(fn($v)=>$v/100, $chart['revenue'])),
                    borderColor: 'rgba(236,72,153,1)',
                    backgroundColor: 'rgba(236,72,153,0.2)',
                    tension: 0.35,
                    yAxisID: 'y1',
                    fill: true,
                  }
                ]
              };
              const options = {
                responsive: true,
                scales: {
                  y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#d4d4d8' } },
                  y1: { beginAtZero: true, position: 'right', grid: { display: false }, ticks: { color: '#d4d4d8' } },
                  x: { grid: { display: false }, ticks: { color: '#a1a1aa' } }
                },
                plugins: { legend: { labels: { color: '#e4e4e7' } } }
              };
              new Chart(ctx, { type: 'line', data, options });
            });
          </script>
        </div>

        <!-- Upcoming events -->
        <div class="rounded-2xl bg-white/5 ring-1 ring-white/10 p-6">
          <div class="flex items-center justify-between mb-3">
            <h2 class="text-xl font-bold">Upcoming</h2>
            <a href="{{ route('admin.events.index') }}" class="text-sm text-zinc-400 hover:text-white">View all →</a>
          </div>
          <div class="space-y-3">
            @forelse($upcoming as $e)
              <div class="flex items-start justify-between">
                <div>
                  <div class="text-xs uppercase tracking-widest text-zinc-400">{{ optional($e->starts_at)->format('D, M j • g:i A') }}</div>
                  <div class="font-semibold">{{ $e->title }}</div>
                  @if ($e->venue)
                    <div class="text-zinc-400 text-sm">{{ $e->venue }}</div>
                  @endif
                </div>
                <div class="flex items-center gap-2">
                  <a href="{{ route('admin.events.edit', $e->id) }}" class="text-sm text-zinc-200 hover:underline">Edit</a>
                  <form method="POST" action="{{ route('admin.events.toggle', $e->id) }}" class="inline">
                    @csrf
                    @method('PATCH')
                    <button class="text-sm {{ $e->is_published ? 'text-yellow-300' : 'text-green-300' }} hover:underline">
                      {{ $e->is_published ? 'Unpublish' : 'Publish' }}
                    </button>
                  </form>
                </div>
              </div>
            @empty
              <div class="text-center text-zinc-400 py-6">No upcoming events.</div>
            @endforelse
          </div>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>
