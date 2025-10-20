<footer class="pt-14 pb-24 sm:pb-14 border-t border-white/10">
  <div class="max-w-7xl mx-auto px-6">
    <div class="grid grid-cols-1 md:grid-cols-3 items-center gap-6">
      <div class="flex items-center md:justify-start justify-center gap-3 text-zinc-300 order-2 md:order-1">
        <span class="text-lg font-extrabold">2<span class="text-indigo-400">DAWN</span></span>
        <span class="hidden md:inline text-zinc-700">•</span>
        <span class="text-sm">© {{ date('Y') }} All rights reserved.</span>
      </div>
      <nav class="order-1 md:order-2 flex items-center justify-center gap-6 text-sm text-zinc-300">
        <a href="{{ url('/') }}" class="hover:text-white">Home</a>
        <a href="{{ route('events.index') }}" class="hover:text-white">Events</a>
        <a href="{{ route('events.recent') }}" class="hover:text-white">Recent</a>
        <a href="{{ url('/#how-to-buy') }}" class="hover:text-white">How it works</a>
        <a href="{{ url('/#host') }}" class="hover:text-white">Host</a>
      </nav>
      <div class="order-3 flex items-center md:justify-end justify-center text-zinc-400 text-xs">
        <span>Built with ❤️</span>
      </div>
    </div>
  </div>
</footer>
