<x-app-layout>
    <div class="py-6">
        <div class="max-w-4xl mx-auto px-6">
            <!-- Breadcrumbs -->
            <nav class="text-sm text-[#6B7280] mb-4"><a href="{{ route('admin.events.index') }}" class="hover:text-[#111827]">Events</a> <span class="mx-1">/</span> <span class="text-[#111827]">Edit</span></nav>

            <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm">
                <div class="p-6">
<form method="POST" action="{{ route('admin.events.update', $event) }}" class="space-y-4" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        @include('admin.events._form')
                        <div class="flex items-center gap-2">
                            <x-primary-button>{{ __('Save') }}</x-primary-button>
                            <a href="{{ route('admin.events.index') }}" class="text-gray-600 hover:underline">{{ __('Cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
            </div>

            <!-- Host Panel Links -->
            <div class="mt-6 bg-white border border-[#E5E7EB] rounded-2xl shadow-sm">
              <div class="p-6">
                <h2 class="text-lg font-semibold text-[#111827] mb-3">Host panel links</h2>
                <form method="POST" action="{{ route('admin.events.tokens.store', $event) }}" class="flex items-center gap-2 mb-4">
                  @csrf
                  <input type="text" name="label" placeholder="Label (e.g., Gate A)" class="rounded-md bg-[#F9FAFB] border border-[#D1D5DB] px-3 py-2 text-sm text-[#111827] placeholder-[#9CA3AF] flex-1 focus:border-[#6366F1] focus:ring-2 focus:ring-[rgba(99,102,241,0.2)]" />
                  <button class="px-4 py-2 rounded-md bg-[#6366F1] text-white text-sm hover:bg-[#4F46E5]">Create link</button>
                </form>
                <div class="overflow-x-auto">
                  <table class="min-w-full text-sm divide-y divide-[#E5E7EB]">
                    <thead><tr class="text-[#6B7280] text-[11px] uppercase"><th class="text-left py-2">Label</th><th class="text-left py-2">URL</th><th class="text-left py-2">Active</th><th class="text-left py-2">Expires</th><th class="py-2"></th></tr></thead>
                    <tbody class="divide-y divide-[#E5E7EB]">
                      @forelse($tokens ?? [] as $t)
                        <tr>
                          <td class="py-2 pr-4">{{ $t->label ?? '—' }}</td>
                          <td class="py-2 pr-4"><a href="{{ url('/h/'.$t->token) }}" target="_blank" class="text-[#6366F1] hover:underline">{{ url('/h/'.$t->token) }}</a></td>
                          <td class="py-2 pr-4">{!! $t->active ? '<span class="px-2 py-1 text-[10px] bg-emerald-500/20 text-emerald-300 rounded">Yes</span>' : '<span class="px-2 py-1 text-[10px] bg-zinc-500/20 text-zinc-300 rounded">No</span>' !!}</td>
                          <td class="py-2 pr-4">{{ optional($t->expires_at)->format('Y-m-d H:i') }}</td>
                          <td class="py-2 space-x-2">
                            <form method="POST" action="{{ route('admin.tokens.toggle', $t) }}" class="inline">@csrf @method('PATCH')<button class="text-xs text-zinc-200 hover:underline">{{ $t->active ? 'Deactivate' : 'Activate' }}</button></form>
                            <form method="POST" action="{{ route('admin.tokens.destroy', $t) }}" class="inline" onsubmit="return confirm('Revoke this link?')">@csrf @method('DELETE')<button class="text-xs text-red-300 hover:underline">Revoke</button></form>
                          </td>
                        </tr>
                      @empty
                        <tr><td colspan="5" class="py-4 text-[#9CA3AF]">No links yet.</td></tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
        </div>
    </div>
</x-app-layout>
