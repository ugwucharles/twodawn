@extends('layouts.public')

@section('content')
<section class="py-10">
  <div class="max-w-3xl mx-auto px-6">
    <div class="mb-6 flex items-center justify-between print:hidden">
      <a href="{{ url('/') }}" class="text-sm text-zinc-300 hover:text-white underline underline-offset-4">Home</a>
      <a href="{{ route('events.index') }}" class="text-sm text-zinc-300 hover:text-white underline underline-offset-4">All events</a>
    </div>
    <div class="rounded-3xl bg-white/5 ring-1 ring-white/10 p-6">
      <h1 class="text-2xl font-extrabold">Payment Failed</h1>
      <p class="mt-4">{{ $message ?? 'Your payment could not be completed.' }}</p>
      <div class="mt-6 flex items-center gap-3">
        <a href="{{ route('events.index') }}" class="inline-flex items-center px-4 py-2 rounded-full bg-white text-black text-sm font-semibold hover:bg-zinc-100 transition">Browse events</a>
        <a href="{{ url('/events') }}" class="inline-flex items-center px-4 py-2 rounded-full bg-white/10 ring-1 ring-white/15 text-white text-sm hover:bg-white/15 transition">Try another event</a>
      </div>
    </div>
  </div>
</section>
@endsection
