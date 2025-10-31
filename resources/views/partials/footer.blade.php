<footer class="py-12">
  <div class="max-w-7xl mx-auto px-6">
    <div class="flex flex-col items-center text-center gap-4">
      <div class="flex items-center gap-3 text-zinc-300">
        <span class="text-lg font-extrabold">2<span class="text-indigo-400">DAWN</span></span>
      </div>
      <nav class="flex flex-wrap items-center justify-center gap-x-[5px] gap-y-[5px] text-sm text-zinc-300">
        <a href="{{ url('/') }}" class="px-0 hover:text-white">Home</a>
        <span aria-hidden class="mx-[5px] opacity-40">|</span>
        <a href="{{ route('events.index') }}" class="px-0 hover:text-white">Events</a>
        <span aria-hidden class="mx-[5px] opacity-40">|</span>
        <a href="{{ route('events.recent') }}" class="px-0 hover:text-white">Recent</a>
        <span aria-hidden class="mx-[5px] opacity-40">|</span>
        <a href="{{ route('pricing') }}" class="px-0 hover:text-white">Pricing</a>
        <span aria-hidden class="mx-[5px] opacity-40">|</span>
        <a href="{{ url('/#how-to-buy') }}" class="px-0 hover:text-white">How it works</a>
        <span aria-hidden class="mx-[5px] opacity-40">|</span>
        <a href="{{ url('/#host') }}" class="px-0 hover:text-white">Host</a>
      </nav>
      <div class="text-xs text-zinc-500">© {{ date('Y') }} All rights reserved.</div>
    </div>
  </div>
</footer>
