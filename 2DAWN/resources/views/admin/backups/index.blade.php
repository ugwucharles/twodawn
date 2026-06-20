<x-app-layout>
  <div class="py-6">
    <div class="max-w-5xl mx-auto px-6">
      <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-extrabold">Backups</h1>
        <a href="{{ route('admin.dashboard') }}" class="text-sm text-zinc-400 hover:text-white">Dashboard</a>
      </div>

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

      <div class="rounded-2xl bg-white/5 ring-1 ring-white/10 p-4">
        <table class="w-full text-sm">
          <thead>
            <tr class="text-left text-zinc-400">
              <th class="py-2">File</th>
              <th class="py-2">Size</th>
              <th class="py-2">Modified</th>
              <th class="py-2"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/10">
            @forelse($files as $f)
              <tr>
                <td class="py-2 pr-4 font-mono">{{ $f['name'] }}</td>
                <td class="py-2 pr-4">{{ number_format($f['size']/1024, 1) }} KB</td>
                <td class="py-2 pr-4">{{ $f['mtime'] ? date('Y-m-d H:i', $f['mtime']) : '—' }}</td>
                <td class="py-2 pr-4 text-right">
                  <a href="{{ route('admin.backups.download', $f['name']) }}" class="inline-flex items-center px-3 py-1.5 rounded-md bg-white text-black text-xs font-semibold hover:bg-zinc-100">Download</a>
                  <form method="POST" action="{{ route('admin.backups.destroy', $f['name']) }}" class="inline" onsubmit="return confirm('Delete this backup?')">
                    @csrf
                    @method('DELETE')
                    <button class="ml-2 inline-flex items-center px-3 py-1.5 rounded-md bg-red-500/20 ring-1 ring-red-500/30 text-red-200 text-xs font-semibold hover:bg-red-500/30">Delete</button>
                  </form>
                </td>
              </tr>
            @empty
              <tr><td colspan="4" class="py-6 text-center text-zinc-400">No backups yet. Use "Run Backup" on the dashboard.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</x-app-layout>