<x-app-layout>
  <div class="py-6">
    <div class="max-w-4xl mx-auto px-6">
      <div class="bg-white/5 ring-1 ring-white/10 rounded-2xl">
        <div class="p-6">
<h1 class="text-2xl font-bold mb-4">Conversation
            @if($conversation->closed_at)
              <span class="ml-2 px-2 py-0.5 rounded-full text-xs bg-red-500/20 ring-1 ring-red-500/30 text-red-300 align-middle">Closed</span>
            @endif
          </h1>
          <div id="chat-list" class="rounded-xl bg-zinc-900/40 ring-1 ring-white/10 p-3 h-[420px] overflow-y-auto space-y-2">
            @foreach($messages as $m)
              <div class="{{ $m->sender==='admin' ? 'text-left' : 'text-right' }}">
                @if(!empty($m->media_path))
                  <div class="inline-block max-w-[80%]">
                    <img src="{{ \Storage::disk('public')->url($m->media_path) }}" alt="image" class="rounded-lg max-h-48">
                  </div>
                @endif
                @if($m->body)
                  <span class="{{ $m->sender==='admin' ? 'inline-block max-w-[80%] text-sm bg-white/10 ring-1 ring-white/10 rounded px-2 py-1' : 'inline-block max-w-[80%] text-sm bg-white text-black rounded px-2 py-1' }}">{{ $m->body }}</span>
                @endif
                <div class="text-[10px] text-zinc-500 mt-0.5">{{ $m->created_at->format('Y-m-d H:i') }}</div>
              </div>
            @endforeach
          </div>
          <div class="mt-3 flex items-center justify-between gap-2">
            <form method="POST" action="{{ route('admin.chat.reply', $conversation) }}" class="flex-1 flex gap-2">
              @csrf
              <input name="body" type="text" placeholder="Type a reply" class="flex-1 rounded bg-black/30 border border-white/10 px-3 py-2 text-sm focus:border-white/30 focus:ring-0" {{ $conversation->closed_at ? 'disabled' : '' }}>
              <button class="rounded bg-white text-black px-4 py-2 text-sm" {{ $conversation->closed_at ? 'disabled' : '' }}>Send</button>
            </form>
            @if($conversation->closed_at)
              <form method="POST" action="{{ route('admin.chat.reopen', $conversation) }}">@csrf<button class="px-3 py-2 rounded bg-emerald-600 text-white text-sm">Reopen</button></form>
            @else
              <form method="POST" action="{{ route('admin.chat.close', $conversation) }}">@csrf<button class="px-3 py-2 rounded bg-red-600 text-white text-sm">Close</button></form>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
  <script>
    (function(){
      const list=document.getElementById('chat-list');
      let lastId={{ $messages->last()->id ?? 0 }};
      async function poll(){
        try{
          const url='{{ route('admin.chat.messages', $conversation) }}?after_id='+lastId;
          const res=await fetch(url,{headers:{'Accept':'application/json'}}); if(!res.ok) return;
          const data=await res.json(); if(data&&data.messages&&data.messages.length){
            data.messages.forEach(m=>{
              const wrap=document.createElement('div'); wrap.className=(m.sender==='admin')?'text-left':'text-right';
              const span=document.createElement('span'); span.className=(m.sender==='admin')?'inline-block max-w-[80%] text-sm bg-white/10 ring-1 ring-white/10 rounded px-2 py-1':'inline-block max-w-[80%] text-sm bg-white text-black rounded px-2 py-1';
              span.textContent=m.body; wrap.appendChild(span);
              const meta=document.createElement('div'); meta.className='text-[10px] text-zinc-500 mt-0.5'; meta.textContent=new Date(m.created_at).toLocaleString(); wrap.appendChild(meta);
              list.appendChild(wrap); list.scrollTop=list.scrollHeight; lastId=Math.max(lastId, m.id);
            });
          }
        }catch(e){}
        setTimeout(poll, 4000);
      }
      poll();
    })();
  </script>
</x-app-layout>
