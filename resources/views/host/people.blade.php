@extends('layouts.public')

@section('title', 'Scanned people')
@section('robots', 'noindex, nofollow')

@section('content')
<section class="py-8 sm:py-10">
  <div class="max-w-6xl mx-auto px-6">
    <div class="mb-6 flex items-start justify-between gap-6">
      <div class="flex items-center gap-3">
        <button id="host-menu-btn" class="md:hidden inline-flex items-center justify-center w-10 h-10 rounded-lg bg-white/10 ring-1 ring-white/15">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <div>
          <h1 class="text-2xl font-extrabold">Scanned people — {{ $event->title }}</h1>
          <div class="text-zinc-400 text-sm mt-1">Token: {{ $host->label ?? 'Link' }}</div>
        </div>
      </div>
    </div>

    <!-- Drawer like panel -->
    <div id="host-menu-overlay" class="hidden fixed inset-0 bg-black/50 z-50"></div>
    <aside id="host-menu" class="hidden fixed inset-y-0 left-0 w-72 max-w-[85vw] bg-zinc-950/95 ring-1 ring-white/10 z-50 p-6">
      <div class="flex items-center justify-between mb-4">
        <div class="font-semibold">Host Panel</div>
        <button id="host-menu-close" class="text-zinc-400 hover:text-white">Close</button>
      </div>
      <div class="grid grid-cols-3 gap-3 text-center mb-4">
        <div class="rounded-xl bg-white/5 ring-1 ring-white/10 p-3"><div class="text-xs text-zinc-400">Sold</div><div class="text-2xl font-bold">{{ $sold }}</div></div>
        <div class="rounded-xl bg-white/5 ring-1 ring-white/10 p-3"><div class="text-xs text-zinc-400">Checked</div><div class="text-2xl font-bold" id="menu-checked">{{ $checked }}</div></div>
        <div class="rounded-xl bg-white/5 ring-1 ring-white/10 p-3"><div class="text-xs text-zinc-400">Remaining</div><div class="text-2xl font-bold" id="menu-remaining">{{ $remaining }}</div></div>
      </div>
      <nav class="grid gap-2 text-sm">
        <a href="{{ route('host.panel', $host->token) }}#scan" class="rounded px-3 py-2 hover:bg-white/5">Scan</a>
        <a href="{{ route('host.panel', $host->token) }}#manual" class="rounded px-3 py-2 hover:bg-white/5">Manual entry</a>
        <a href="{{ route('host.panel', $host->token) }}#recent-card" class="rounded px-3 py-2 hover:bg-white/5">Recent scans</a>
        <a href="{{ route('host.people', $host->token) }}" class="rounded px-3 py-2 hover:bg-white/5">Scanned people</a>
        <button id="copy-link" class="text-left rounded px-3 py-2 hover:bg-white/5">Copy scanner link</button>
      </nav>
    </aside>

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

<script>
const menu = document.getElementById('host-menu');
const overlay = document.getElementById('host-menu-overlay');
const openBtn = document.getElementById('host-menu-btn');
const closeBtn = document.getElementById('host-menu-close');
function openMenu(){ menu.classList.remove('hidden'); overlay.classList.remove('hidden'); document.body.style.overflow='hidden'; }
function closeMenu(){ menu.classList.add('hidden'); overlay.classList.add('hidden'); document.body.style.overflow=''; }
openBtn?.addEventListener('click', openMenu); closeBtn?.addEventListener('click', closeMenu); overlay?.addEventListener('click', closeMenu);
window.addEventListener('keydown', (e)=>{ if(e.key==='Escape') closeMenu(); });

document.getElementById('copy-link')?.addEventListener('click', async ()=>{ try{ await navigator.clipboard.writeText(@json(route('host.panel',$host->token))); alert('Scanner link copied'); } catch{ alert(@json(route('host.panel',$host->token))); } });
</script>
@endsection
