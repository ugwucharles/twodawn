<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
                    {{ session('status') }}
                </div>
            @endif

            <div class="mb-4 flex items-center justify-between">
                <h1 class="text-xl font-semibold text-eventbrite-dark">Events</h1>
                <a href="{{ route('admin.events.create') }}" class="inline-flex items-center px-4 py-2 bg-tix-orange border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#e55a2d] transition ease-in-out duration-150">
                    + New event
                </a>
            </div>

            <div class="bg-white border border-eventbrite-gray-100 rounded-2xl shadow-sm">
                <div class="p-4 sm:p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-eventbrite-gray-100 text-sm">
                            <thead>
                                <tr class="text-eventbrite-gray-600 text-[11px] sm:text-xs uppercase tracking-wider">
                                    <th class="px-2 sm:px-3 py-2 text-left">Title</th>
                                    <th class="px-2 sm:px-3 py-2 text-left">Starts</th>
                                    <th class="px-2 sm:px-3 py-2 text-left hidden xs:table-cell">Published</th>
                                    <th class="px-2 sm:px-3 py-2 text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-eventbrite-gray-100">
                                @forelse ($events as $event)
                                    <tr class="align-middle hover:bg-[#f8f7fa]">
                                        <td class="px-2 sm:px-3 py-2 whitespace-nowrap text-eventbrite-dark">{{ $event->title }}</td>
                                        <td class="px-2 sm:px-3 py-2 whitespace-nowrap text-eventbrite-dark">{{ optional($event->starts_at)->format('Y-m-d H:i') }}</td>
                                        <td class="px-2 sm:px-3 py-2 hidden xs:table-cell">
                                            @if($event->is_published)
                                                <span id="published-badge-{{ $event->id }}" class="px-2 py-1 text-[10px] sm:text-xs bg-emerald-50 text-emerald-700 rounded-full border border-emerald-200">Yes</span>
                                            @else
                                                <span id="published-badge-{{ $event->id }}" class="px-2 py-1 text-[10px] sm:text-xs bg-eventbrite-gray-50 text-eventbrite-gray-600 rounded-full border border-eventbrite-gray-100">No</span>
                                            @endif
                                        </td>
                                        <td class="px-2 sm:px-3 py-2 text-right space-x-2 whitespace-nowrap">
                                            <button data-toggle-publish data-id="{{ $event->id }}" data-url="{{ route('admin.events.toggle.json', $event) }}" class="text-sm {{ $event->is_published ? 'text-amber-600' : 'text-emerald-600' }} hover:underline">
                                                {{ $event->is_published ? 'Unpublish' : 'Publish' }}
                                            </button>
                                            <a href="{{ route('admin.events.edit', $event) }}" class="text-sm text-eventbrite-dark hover:text-tix-orange hover:underline">Edit</a>
                                            <form action="{{ route('admin.events.destroy', $event) }}" method="POST" class="inline" onsubmit="return confirm('Delete this event?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-sm text-red-600 hover:underline">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="px-3 py-6 text-center text-eventbrite-gray-600" colspan="4">No events yet.</td>
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
