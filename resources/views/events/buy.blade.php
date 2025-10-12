@extends('layouts.public')

@section('content')
<section class="relative py-12 sm:py-16">
  <div class="max-w-5xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-2 gap-10">
    <div class="space-y-4">
      <div class="aspect-[16/10] overflow-hidden rounded-2xl ring-1 ring-white/10 bg-white/5">
        @if($event->image_path)
          <img src="{{ Storage::url($event->image_path) }}" alt="{{ $event->title }}" class="h-full w-full object-cover"/>
        @else
          <div class="h-full w-full bg-gradient-to-br from-indigo-500 via-fuchsia-500 to-rose-500"></div>
        @endif
      </div>
      <div>
        <h1 class="text-3xl font-extrabold">Buy Tickets — {{ $event->title }}</h1>
        <div class="mt-2 text-zinc-300">{{ optional($event->starts_at)->format('D, M j, Y g:i A') }} @if($event->venue) • {{ $event->venue }} @endif</div>
      </div>
    </div>

    <div>
      @if ($errors->any())
        <div class="mb-4 p-3 bg-red-500/10 text-red-300 rounded ring-1 ring-red-500/30">
          <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form method="POST" action="{{ route('orders.create', $event) }}" class="space-y-4 rounded-2xl bg-white/5 ring-1 ring-white/10 p-6">
        @csrf
        <div>
          <label class="block text-sm text-zinc-300" for="buyer_name">Full name</label>
          <input id="buyer_name" name="buyer_name" type="text" required value="{{ old('buyer_name') }}" class="mt-1 block w-full rounded-lg bg-black/30 border border-white/10 focus:border-white/30 focus:ring-0 px-3 py-2" />
        </div>
        <div>
          <label class="block text-sm text-zinc-300" for="buyer_email">Email</label>
          <input id="buyer_email" name="buyer_email" type="email" required value="{{ old('buyer_email') }}" class="mt-1 block w-full rounded-lg bg-black/30 border border-white/10 focus:border-white/30 focus:ring-0 px-3 py-2" />
        </div>
        <div>
          <label class="block text-sm text-zinc-300" for="buyer_phone">Phone (optional)</label>
          <input id="buyer_phone" name="buyer_phone" type="text" value="{{ old('buyer_phone') }}" class="mt-1 block w-full rounded-lg bg-black/30 border border-white/10 focus:border-white/30 focus:ring-0 px-3 py-2" />
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm text-zinc-300" for="quantity">Quantity</label>
            <input id="quantity" name="quantity" type="number" min="1" step="1" required value="{{ old('quantity', 1) }}" class="mt-1 block w-full rounded-lg bg-black/30 border border-white/10 focus:border-white/30 focus:ring-0 px-3 py-2" />
          </div>
          <div>
            <label class="block text-sm text-zinc-300" for="coupon">Coupon (optional)</label>
            <input id="coupon" name="coupon" type="text" value="{{ old('coupon') }}" class="mt-1 block w-full rounded-lg bg-black/30 border border-white/10 focus:border-white/30 focus:ring-0 px-3 py-2" />
          </div>
        </div>

        <button class="w-full inline-flex items-center justify-center px-6 py-3 rounded-xl bg-white text-black font-semibold hover:bg-zinc-100 transition">Proceed to Paystack</button>
        <a href="{{ route('events.show', $event) }}" class="block text-center text-zinc-400 hover:text-white text-sm">Cancel</a>
      </form>
    </div>
  </div>
</section>
@endsection
