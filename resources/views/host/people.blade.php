@extends('layouts.public')

@section('title', 'Scanned people')
@section('chat','off')
@section('robots', 'noindex, nofollow')

@section('content')
<section class="py-8 sm:py-10">
  <div class="max-w-6xl mx-auto px-6">
    <!-- Header -->
    <div class="flex items-start justify-between gap-4 flex-wrap">
      <div>
        <h1 class="text-2xl font-extrabold mt-[5px]">Scanned people — {{ $event->title }}</h1>
        <div class="text-zinc-400 text-sm mt-1">{{ $host->label ? ('Token: '.$host->label) : '' }}</div>
      </div>
      <form method="GET" action="{{ route('host.people.export', $host->token) }}" class="flex items-center gap-2">
        <label for="from" class="sr-only">From date</label>
        <input id="from" type="date" name="from" value="{{ request('from', now()->subMonth()->toDateString()) }}" placeholder="From date" aria-label="From date" class="rounded-md bg-black/30 border border-white/10 px-3 py-2 text-sm placeholder:text-zinc-500 w-40 sm:w-48" />
        <label for="to" class="sr-only">To date</label>
        <input id="to" type="date" name="to" value="{{ request('to', now()->toDateString()) }}" placeholder="To date" aria-label="To date" class="rounded-md bg-black/30 border border-white/10 px-3 py-2 text-sm placeholder:text-zinc-500 w-40 sm:w-48" />
        <button class="inline-flex items-center px-3 py-2 rounded-md bg-white text-black text-sm hover:bg-zinc-100">Export CSV</button>
      </form>
    </div>

    <!-- Stats -->
    <div class="mt-6 grid grid-cols-3 gap-3 sm:gap-4">
      <div class="rounded-2xl bg-white/5 ring-1 ring-white/10 p-4">
        <div class="text-xs text-zinc-400">Sold</div>
        <div class="mt-1 text-2xl font-bold">{{ $sold }}</div>
      </div>
      <div class="rounded-2xl bg-white/5 ring-1 ring-white/10 p-4">
        <div class="text-xs text-zinc-400">Checked in</div>
        <div class="mt-1 text-2xl font-bold">{{ $checked }}</div>
      </div>
      <div class="rounded-2xl bg-white/5 ring-1 ring-white/10 p-4">
        <div class="text-xs text-zinc-400">Remaining</div>
        <div class="mt-1 text-2xl font-bold">{{ $remaining }}</div>
      </div>
    </div>

    <!-- Table -->
    <div class="mt-6 rounded-2xl bg-white/5 ring-1 ring-white/10">
      <div class="p-4 sm:p-6 overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-zinc-400 text-[11px] sm:text-xs uppercase tracking-wider">
              <th class="text-left py-2 pr-4">Time</th>
              <th class="text-left py-2 pr-4">Name</th>
              <th class="text-left py-2 pr-4">Email</th>
              <th class="text-left py-2 pr-4">Reference</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/10">
            @forelse ($checkins as $ci)
              <tr>
                <td class="py-2 pr-4 whitespace-nowrap">{{ $ci->created_at?->format('Y-m-d H:i') }}</td>
                <td class="py-2 pr-4">{{ $ci->order?->buyer_name }}</td>
                <td class="py-2 pr-4">{{ $ci->order?->buyer_email }}</td>
                <td class="py-2 pr-4">{{ $ci->order?->paystack_reference }}</td>
              </tr>
            @empty
              <tr><td class="py-6 text-center text-zinc-400" colspan="4">No check-ins yet.</td></tr>
            @endforelse
          </tbody>
        </table>
        <div class="mt-4">{{ $checkins->withQueryString()->links() }}</div>
      </div>
    </div>
  </div>
</section>

<script>
document.querySelectorAll('[data-copy-link]')?.forEach(el=>{
  el.addEventListener('click', async ()=>{ try{ await navigator.clipboard.writeText(@json(route('host.panel',$host->token))); alert('Scanner link copied'); } catch{ alert(@json(route('host.panel',$host->token))); } });
});
</script>
@endsection
