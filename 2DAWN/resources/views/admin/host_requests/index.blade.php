<x-app-layout>
  <div class="py-6">
    <div class="max-w-7xl mx-auto px-6">
      <div class="bg-white/5 ring-1 ring-white/10 rounded-2xl">
        <div class="p-6">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-white/10">
              <thead>
                <tr class="text-zinc-400 text-xs uppercase tracking-wider">
                  <th class="px-3 py-2 text-left">Date</th>
                  <th class="px-3 py-2 text-left">Name</th>
                  <th class="px-3 py-2 text-left">Email</th>
                  <th class="px-3 py-2 text-left">Event</th>
                  <th class="px-3 py-2 text-left">Event date</th>
                  <th class="px-3 py-2 text-left">Status</th>
                  <th class="px-3 py-2"></th>
                </tr>
              </thead>
              <tbody class="divide-y divide-white/10">
                @forelse($requests as $r)
                  <tr>
                    <td class="px-3 py-2">{{ optional($r->created_at)->format('Y-m-d H:i') }}</td>
                    <td class="px-3 py-2">{{ $r->name }}</td>
                    <td class="px-3 py-2">{{ $r->email }}</td>
                    <td class="px-3 py-2">{{ $r->event_title }}</td>
                    <td class="px-3 py-2">{{ optional($r->event_date)->format('Y-m-d H:i') }}</td>
                    <td class="px-3 py-2">{{ ucfirst($r->status) }}</td>
                    <td class="px-3 py-2 text-right">
                      <a href="{{ route('admin.host-requests.show', $r) }}" class="text-indigo-300 hover:underline">View</a>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td class="px-3 py-6 text-center text-zinc-400" colspan="7">No requests yet.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
          <div class="mt-4">{{ $requests->links() }}</div>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>