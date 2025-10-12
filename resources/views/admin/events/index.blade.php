<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
                    {{ session('status') }}
                </div>
            @endif

            <div class="mb-4">
                <a href="{{ route('admin.events.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    + New Event
                </a>
            </div>

            <div class="bg-white/5 ring-1 ring-white/10 rounded-2xl">
                <div class="p-4 sm:p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-white/10 text-sm">
                            <thead>
                                <tr class="text-zinc-400 text-[11px] sm:text-xs uppercase tracking-wider">
                                    <th class="px-2 sm:px-3 py-2 text-left">Title</th>
                                    <th class="px-2 sm:px-3 py-2 text-left">Starts</th>
                                    <th class="px-2 sm:px-3 py-2 text-left hidden xs:table-cell">Published</th>
                                    <th class="px-2 sm:px-3 py-2 text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/10">
                                @forelse ($events as $event)
                                    <tr class="align-middle">
                                        <td class="px-2 sm:px-3 py-2 whitespace-nowrap">{{ $event->title }}</td>
                                        <td class="px-2 sm:px-3 py-2 whitespace-nowrap">{{ optional($event->starts_at)->format('Y-m-d H:i') }}</td>
                                        <td class="px-2 sm:px-3 py-2 hidden xs:table-cell">
                                            @if($event->is_published)
                                                <span id="published-badge-{{ $event->id }}" class="px-2 py-1 text-[10px] sm:text-xs bg-green-500/20 text-green-300 rounded">Yes</span>
                                            @else
                                                <span id="published-badge-{{ $event->id }}" class="px-2 py-1 text-[10px] sm:text-xs bg-zinc-500/20 text-zinc-300 rounded">No</span>
                                            @endif
                                        </td>
                                        <td class="px-2 sm:px-3 py-2 text-right space-x-2">
                                            <button data-toggle-publish data-id="{{ $event->id }}" data-url="{{ route('admin.events.toggle.json', $event) }}" class="text-sm {{ $event->is_published ? 'text-yellow-300' : 'text-green-300' }} hover:underline">
                                                {{ $event->is_published ? 'Unpublish' : 'Publish' }}
                                            </button>
                                            <a href="{{ route('admin.events.edit', $event) }}" class="text-indigo-300 hover:underline">Edit</a>
                                            <form action="{{ route('admin.events.destroy', $event) }}" method="POST" class="inline" onsubmit="return confirm('Delete this event?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-300 hover:underline">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="px-3 py-6 text-center text-zinc-400" colspan="4">No events yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $events->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
