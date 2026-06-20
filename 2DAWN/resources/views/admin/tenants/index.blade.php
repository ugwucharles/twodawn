@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-6">
  <div class="mt-28 bg-white/5 ring-1 ring-white/10 rounded-2xl p-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold">Tenants</h1>
      <a href="{{ route('admin.tenants.create') }}" class="inline-flex items-center px-4 py-2 rounded-full bg-white text-black text-sm font-semibold hover:bg-zinc-100">New Tenant</a>
    </div>
    <div class="mt-4 overflow-x-auto">
      <table class="min-w-full text-sm divide-y divide-white/10">
        <thead class="text-zinc-400">
          <tr><th class="px-3 py-2 text-left">Name</th><th class="px-3 py-2 text-left">Domain</th><th class="px-3 py-2 text-left">Active</th><th class="px-3 py-2 text-left">Brand</th><th class="px-3 py-2"></th></tr>
        </thead>
        <tbody class="divide-y divide-white/10">
          @forelse($tenants as $t)
          <tr>
            <td class="px-3 py-2">{{ $t->name }}</td>
            <td class="px-3 py-2 font-mono text-xs">{{ $t->domain }}</td>
            <td class="px-3 py-2">{!! $t->is_active ? '<span class="text-emerald-300">Yes</span>' : '<span class="text-zinc-400">No</span>' !!}</td>
            <td class="px-3 py-2"><span class="inline-flex items-center gap-2"><span class="w-3 h-3 rounded-full" style="background: {{ $t->brand_color ?? '#999' }}"></span><span class="text-xs text-zinc-300">{{ $t->brand_color ?? '—' }}</span></span></td>
            <td class="px-3 py-2 text-right">
              <a href="{{ route('admin.tenants.edit', $t) }}" class="text-indigo-300 hover:underline">Edit</a>
              <form method="POST" action="{{ route('admin.tenants.destroy', $t) }}" class="inline ml-3" onsubmit="return confirm('Delete tenant?');">
                @csrf @method('DELETE')
                <button class="text-red-300 hover:underline">Delete</button>
              </form>
            </td>
          </tr>
          @empty
          <tr><td colspan="5" class="px-3 py-8 text-center text-zinc-400">No tenants yet.</td></tr>
          @endforelse
        </tbody>
      </table>
      <div class="mt-4">{{ $tenants->links() }}</div>
    </div>
  </div>
</div>
@endsection