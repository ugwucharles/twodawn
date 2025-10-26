@extends('layouts.public')

@section('title', 'Scanned people')
@section('robots', 'noindex, nofollow')

@section('content')
<section class="py-8 sm:py-10">
  <div class="max-w-6xl mx-auto px-6">
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-extrabold">Scanned people — {{ $event->title }}</h1>
        <div class="text-zinc-400 text-sm mt-1">Token: {{ $host->label ?? 'Link' }}</div>
      </div>
      <a href="{{ route('host.panel', $host->token) }}" class="text-sm text-zinc-300 hover:text-white">← Back to scanner</a>
    </div>

    <div class="rounded-2xl bg-white/5 ring-1 ring-white/10">
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
@endsection
