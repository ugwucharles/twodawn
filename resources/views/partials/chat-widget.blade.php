@php $mode = $showMode ?? 'after-host'; @endphp
<div id="chat-root" x-cloak class="fixed bottom-4 right-4 z-[999] print:hidden" x-data="chatWidget()" x-init="init()" data-chat-start="{{ route('chat.start') }}" data-chat-base="{{ url('/chat') }}" data-chat-show="{{ $mode }}" style="position:fixed; bottom:16px; right:16px; z-index:9999; pointer-events:auto;">
  <!-- Closed chat button (Messenger style) -->
  <button x-cloak x-show="headVisible && !open" @click="toggle()" @pointerdown.prevent="startDrag($event)" class="h-14 w-14 rounded-full bg-blue-600 text-white shadow-lg ring-4 ring-blue-500/30 flex items-center justify-center touch-none select-none cursor-pointer" title="Chat with us">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-7 w-7">
      <path d="M4 3h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-6l-5 4v-4H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"/>
    </svg>
  </button>

  <!-- Open chat window -->
  <div x-show="open" x-cloak hidden class="w-[22rem] max-w-[90vw] rounded-2xl bg-zinc-950/95 ring-1 ring-white/10 shadow-2xl overflow-hidden flex flex-col backdrop-blur-md">
    <!-- Header -->
    <div class="flex items-center justify-between px-3 py-2 bg-white/5">
      <div class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-full bg-emerald-500/20 ring-1 ring-emerald-400/30 flex items-center justify-center text-emerald-300 text-sm font-semibold">S</div>
        <div>
          <div class="text-sm font-semibold leading-none">Support</div>
          <div class="text-[10px] text-emerald-400 leading-none mt-0.5">Online</div>
        </div>
      </div>
      <button @click="toggle()" class="p-1.5 text-zinc-300 hover:text-white rounded-full hover:bg-white/10" aria-label="Close chat">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5"><path d="M6.225 4.811 4.811 6.225 10.586 12l-5.775 5.775 1.414 1.414L12 13.414l5.775 5.775 1.414-1.414L13.414 12l5.775-5.775-1.414-1.414L12 10.586 6.225 4.811z"/></svg>
      </button>
    </div>

    <!-- Messages -->
    <div class="h-72 overflow-y-auto no-scrollbar px-3 py-3 space-y-3" x-ref="list">
      <template x-for="m in msgs" :key="m.id">
        <div class="flex items-end" :class="m.sender==='admin' ? 'justify-start' : 'justify-end'">
          <div class="flex items-end gap-2 max-w-[85%]" :class="m.sender==='admin' ? '' : 'flex-row-reverse'">
            <div class="w-7 h-7 rounded-full bg-zinc-800 text-zinc-200 text-[10px] font-semibold flex items-center justify-center select-none" x-show="m.sender==='admin'">S</div>
            <template x-if="m.media_url">
              <div class="overflow-hidden rounded-2xl ring-1 ring-white/10">
                <img :src="m.media_url" alt="image" class="max-h-56">
              </div>
            </template>
            <template x-if="m.body">
              <div class="px-3 py-2 text-sm" :class="m.sender==='admin' ? 'bg-zinc-800 text-zinc-100 rounded-2xl rounded-bl-none' : 'bg-blue-600 text-white rounded-2xl rounded-br-none'">
                <div x-text="m.body"></div>
                <div class="mt-1 text-[10px] opacity-70 text-right" x-text="fmt(m.created_at)"></div>
              </div>
            </template>
          </div>
        </div>
      </template>
    </div>

    <!-- Composer -->
    <form @submit.prevent="send()" class="p-3 border-t border-white/10">
      <div class="flex items-center bg-zinc-900/60 ring-1 ring-white/10 rounded-full px-2">
        <input x-model="body" x-ref="input" :disabled="closed" type="text" autocomplete="off" aria-label="Message" :placeholder="closed ? 'Chat closed' : 'Aa'" class="flex-1 bg-transparent border-0 focus:outline-none focus:ring-0 px-3 py-2 text-sm placeholder:text-zinc-500">
        <input x-ref="file" type="file" accept="image/*" class="hidden" @change="if($event.target.files[0]) sendImage($event.target.files[0]); $event.target.value=''">
        <button type="button" @click="$refs.file.click()" :disabled="closed || sending" class="p-2 rounded-full text-zinc-300 hover:text-white disabled:opacity-50" aria-label="Attach image">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5"><path d="M7 7a5 5 0 015-5h0a5 5 0 015 5v8a7 7 0 11-14 0V9a3 3 0 116 0v6a1 1 0 11-2 0V9a1 1 0 112 0v6a3 3 0 106 0V7a3 3 0 10-6 0v8"/></svg>
        </button>
        <button type="submit" :disabled="closed || sending || !body.trim()" class="ml-1 p-2 rounded-full bg-blue-600 text-white disabled:opacity-50 disabled:cursor-not-allowed hover:bg-blue-500 transition-colors" aria-label="Send">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5"><path d="M2.01 21 23 12 2.01 3 2 10l15 2-15 2z"/></svg>
        </button>
      </div>
    </form>
  </div>
</div>
