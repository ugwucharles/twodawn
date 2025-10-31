<x-app-layout>
  <div class="py-6">
    <div class="max-w-7xl mx-auto px-6">
      <div class="bg-white/5 ring-1 ring-white/10 rounded-2xl">
        <div class="p-6">
          <h1 class="text-2xl font-bold mb-4">Live chat</h1>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-white/10 text-sm">
              <thead class="text-zinc-400 text-xs uppercase tracking-wider">
                <tr><th class="px-3 py-2 text-left">Last message</th><th class="px-3 py-2 text-left">Name/Email</th><th class="px-3 py-2 text-left">Token</th><th class="px-3 py-2"></th></tr>
              </thead>
              <tbody class="divide-y divide-white/10">
                @forelse($conversations as $c)
                  <tr>
                    <td class="px-3 py-2">{{ optional($c->last_message_at ?? $c->updated_at)->diffForHumans() }}</td>
                    <td class="px-3 py-2">{{ $c->name ?? 'Guest' }} <div class="text-xs text-zinc-400">{{ $c->email }}</div></td>
                    <td class="px-3 py-2 font-mono text-xs">{{ $c->token }}</td>
                    <td class="px-3 py-2 text-right"><a href="{{ route('admin.chat.show', $c) }}" class="text-indigo-300 hover:underline">Open</a></td>
                  </tr>
                @empty
                  <tr><td colspan="4" class="px-3 py-6 text-center text-zinc-400">No conversations yet.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
          <div class="mt-4">{{ $conversations->links() }}</div>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>
