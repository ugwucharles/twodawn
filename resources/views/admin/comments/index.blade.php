<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-white leading-tight">
      {{ __('Comments') }}
    </h2>
  </x-slot>

  <div class="py-8">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-zinc-900 overflow-hidden shadow-sm sm:rounded-lg border border-zinc-800">
        <div class="p-6 text-white">
          @if (session('status'))
            <div class="mb-4 p-3 rounded bg-emerald-500/10 ring-1 ring-emerald-500/30 text-emerald-300">{{ session('status') }}</div>
          @endif

          <div class="flex items-center justify-between">
            <div class="text-zinc-300">Moderate event comments</div>
            <div class="flex items-center gap-2">
              <a href="{{ route('admin.comments.index', ['status' => 'pending']) }}" class="px-3 py-1 rounded {{ $status === 'pending' ? 'bg-white text-black' : 'bg-white/5 ring-1 ring-white/10 hover:bg-white/10' }}">Pending</a>
              <a href="{{ route('admin.comments.index', ['status' => 'approved']) }}" class="px-3 py-1 rounded {{ $status === 'approved' ? 'bg-white text-black' : 'bg-white/5 ring-1 ring-white/10 hover:bg-white/10' }}">Approved</a>
            </div>
          </div>

          <div class="mt-4 divide-y divide-white/10">
            @forelse ($comments as $comment)
              <div class="py-4 flex items-start justify-between gap-4">
                <div class="flex-1">
                  <div class="text-sm text-zinc-300 font-semibold">{{ $comment->name }} <span class="text-zinc-600">• {{ $comment->created_at->diffForHumans() }}</span></div>
                  <div class="text-xs text-zinc-500">@if($comment->email) {{ $comment->email }} • @endif Event: <a href="{{ route('events.show', $comment->event) }}" target="_blank" class="underline hover:text-white">{{ $comment->event->title }}</a></div>
                  <div class="mt-2 text-sm text-zinc-200 whitespace-pre-line">{{ $comment->content }}</div>
                </div>
                <div class="flex items-center gap-2">
                  @if(!$comment->approved)
                  <form method="POST" action="{{ route('admin.comments.approve', $comment) }}">
                    @csrf
                    @method('PATCH')
                    <button class="px-3 py-2 rounded bg-white text-black text-sm hover:bg-zinc-100">Approve</button>
                  </form>
                  @endif
                  <form method="POST" action="{{ route('admin.comments.destroy', $comment) }}" onsubmit="return confirm('Delete this comment?');">
                    @csrf
                    @method('DELETE')
                    <button class="px-3 py-2 rounded bg-white/5 ring-1 ring-white/10 text-sm hover:bg-white/10">Delete</button>
                  </form>
                </div>
              </div>
            @empty
              <div class="py-10 text-center text-zinc-400">No comments found.</div>
            @endforelse
          </div>

          <div class="mt-6">{{ $comments->links() }}</div>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>
