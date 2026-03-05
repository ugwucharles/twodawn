<x-app-layout>
    <div class="py-6">
        <div class="max-w-4xl mx-auto px-6">
            <!-- Breadcrumbs -->
            <nav class="text-sm text-[#6B7280] mb-4"><a href="{{ route('admin.events.index') }}" class="hover:text-[#111827]">Events</a> <span class="mx-1">/</span> <span class="text-[#111827]">Create</span></nav>

            <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm">
                <div class="p-6">
<form method="POST" action="{{ route('admin.events.store') }}" class="space-y-4" enctype="multipart/form-data">
                        @csrf
                        @include('admin.events._form')
                        <div>
                            <x-primary-button>{{ __('Create') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
