<x-app-layout>
    <div class="py-6">
        <div class="max-w-4xl mx-auto px-6">
            <!-- Breadcrumbs -->
            <nav class="text-sm text-zinc-400 mb-4"><a href="{{ route('admin.events.index') }}" class="hover:text-white">Events</a> <span class="mx-1">/</span> <span class="text-zinc-200">Edit</span></nav>

            <div class="bg-white/5 ring-1 ring-white/10 rounded-2xl">
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
    </div>
</x-app-layout>
