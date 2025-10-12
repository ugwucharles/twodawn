<x-app-layout>
  <div class="py-6">
    <div class="max-w-4xl mx-auto px-6">
      <div class="bg-white/5 ring-1 ring-white/10 rounded-2xl">
        <div class="p-6 space-y-3">
          <h2 class="text-xl font-bold">{{ $req->event_title }}</h2>
          <div class="text-zinc-300 text-sm">Requested: {{ optional($req->created_at)->format('Y-m-d H:i') }}</div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-2">
            <div>
              <div class="text-zinc-400 text-sm">Name</div>
              <div>{{ $req->name }}</div>
            </div>
            <div>
              <div class="text-zinc-400 text-sm">Email</div>
              <div>{{ $req->email }}</div>
            </div>
            <div>
              <div class="text-zinc-400 text-sm">Phone</div>
              <div>{{ $req->phone ?: '—' }}</div>
            </div>
            <div>
              <div class="text-zinc-400 text-sm">Event date</div>
              <div>{{ optional($req->event_date)->format('Y-m-d H:i') ?: '—' }}</div>
            </div>
            <div>
              <div class="text-zinc-400 text-sm">Venue</div>
              <div>{{ $req->venue ?: '—' }}</div>
            </div>
            <div>
              <div class="text-zinc-400 text-sm">Expected attendees</div>
              <div>{{ $req->expected_attendees ?: '—' }}</div>
            </div>
            <div>
              <div class="text-zinc-400 text-sm">Budget (₦)</div>
              <div>{{ $req->budget_kobo ? number_format($req->budget_kobo/100, 2) : '—' }}</div>
            </div>
            <div>
              <div class="text-zinc-400 text-sm">Status</div>
              <div>{{ ucfirst($req->status) }}</div>
            </div>
          </div>
          @if($req->message)
            <div class="mt-4">
              <div class="text-zinc-400 text-sm">Message</div>
              <div class="whitespace-pre-line">{{ $req->message }}</div>
            </div>
          @endif
          <div class="mt-6 flex items-center justify-between">
            <a href="{{ route('admin.host-requests.index') }}" class="text-zinc-400 hover:text-white text-sm">← Back to requests</a>
            <form method="POST" action="{{ route('admin.host-requests.update', $req) }}" class="flex items-center gap-2">
              @csrf
              @method('PATCH')
              <label for="status" class="text-sm text-zinc-400">Status</label>
              <select id="status" name="status" class="rounded-md bg-black/30 border border-white/10 text-sm focus:border-white/30 focus:ring-0 px-2 py-1">
                @foreach(['new','reviewing','approved','rejected','closed'] as $st)
                  <option value="{{ $st }}" @selected($req->status === $st)>{{ ucfirst($st) }}</option>
                @endforeach
              </select>
              <button class="inline-flex items-center px-3 py-1.5 rounded-md bg-white text-black text-sm font-semibold hover:bg-zinc-100 transition">Update</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>