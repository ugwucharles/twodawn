<footer class="pt-14 pb-24 sm:pb-14 border-t border-white/10">
  <div class="max-w-7xl mx-auto px-6">
    <div class="flex flex-col sm:flex-row items-center justify-between gap-6">
      <div class="flex items-center gap-3 text-zinc-300">
        <span class="text-lg font-extrabold">2<span class="text-indigo-400">DAWN</span></span>
        <span class="hidden sm:inline text-zinc-700">•</span>
        <span class="text-sm">© {{ date('Y') }} All rights reserved.</span>
      </div>

      <!-- Links stacked: main row then secondary row -->
      <div class="flex flex-col items-center gap-2 text-sm text-zinc-300">
        <div class="flex items-center gap-8">
          <a href="{{ url('/') }}" class="hover:text-white">Home</a>
          <a href="{{ route('events.index') }}" class="hover:text-white">Events</a>
          <a href="{{ route('events.recent') }}" class="hover:text-white">Recent</a>
          @auth
            @if (Auth::user()->is_admin)
              <a href="{{ route('admin.events.index') }}" class="hover:text-white">Admin</a>
            @endif
          @endauth
        </div>
        <div class="flex items-center gap-8">
          <a href="{{ url('/#how-to-buy') }}" class="hover:text-white">How it works</a>
          <a href="{{ url('/#host') }}" class="hover:text-white">Host</a>
        </div>
      </div>

      <div class="flex items-center gap-4 text-zinc-300 mb-6 sm:mb-0">
        <a href="#" aria-label="Instagram" class="hover:text-white">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M7 2C4.243 2 2 4.243 2 7v10c0 2.757 2.243 5 5 5h10c2.757 0 5-2.243 5-5V7c0-2.757-2.243-5-5-5H7zm0 2h10c1.654 0 3 1.346 3 3v10c0 1.654-1.346 3-3 3H7c-1.654 0-3-1.346-3-3V7c0-1.654 1.346-3 3-3zm5 3a5 5 0 100 10 5 5 0 000-10zm0 2.5a2.5 2.5 0 110 5 2.5 2.5 0 010-5zm5.25-.75a1 1 0 100 2 1 1 0 000-2z"/></svg>
        </a>
        <a href="#" aria-label="X" class="hover:text-white">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M13.134 10.89l5.774-6.79h-1.366l-5.017 5.9-4.003-5.9H5.006l5.256 7.749L5 20h1.366l5.368-6.316 4.291 6.316h2.516l-5.407-7.11z"/></svg>
        </a>
      </div>
    </div>
  </div>
</footer>