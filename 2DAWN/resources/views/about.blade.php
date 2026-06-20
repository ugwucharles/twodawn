@extends('layouts.public')

@section('content')
<section class="relative py-12 sm:py-16">
  <div class="absolute inset-0 -z-10">
    <div class="absolute -top-48 -left-32 h-[40rem] w-[40rem] rounded-full blur-3xl opacity-30 bg-gradient-to-br from-indigo-500 via-fuchsia-500 to-rose-500"></div>
    <div class="absolute -bottom-48 -right-32 h-[40rem] w-[40rem] rounded-full blur-3xl opacity-20 bg-gradient-to-tr from-sky-500 to-emerald-400"></div>
  </div>

  <div class="max-w-4xl mx-auto px-6">
    <div class="mb-8 flex items-center justify-between">
      <a href="{{ url('/') }}" class="inline-flex items-center px-4 py-2 rounded-full bg-white text-black text-sm font-semibold hover:bg-zinc-100 transition">Home</a>
      <a href="{{ route('events.index') }}" class="inline-flex items-center px-4 py-2 rounded-full bg-white/10 ring-1 ring-white/15 text-white text-sm hover:bg-white/15 transition">Events</a>
    </div>

    <header class="mb-8">
      <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight">About {{ config('app.name', '2DAWN') }}</h1>
      <p class="mt-3 text-zinc-300 max-w-2xl">We make it effortless to discover parties and buy tickets in seconds — no accounts, no friction, just vibes.</p>
    </header>

    <div class="rounded-3xl bg-white/5 ring-1 ring-white/10 p-6 sm:p-8">
      <div class="prose prose-invert max-w-none">
        <h2 class="text-2xl font-bold">What we do</h2>
        <p class="mt-2 text-zinc-300">{{ config('app.name', '2DAWN') }} helps hosts sell tickets and fans get in fast. We handle payments, QR tickets, and smooth check-ins so you can focus on the moment.</p>
        <ul class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm text-zinc-200">
          <li class="flex items-start gap-3"><span class="h-2 w-2 mt-2 rounded-full bg-emerald-400"></span> Secure Paystack checkout</li>
          <li class="flex items-start gap-3"><span class="h-2 w-2 mt-2 rounded-full bg-indigo-400"></span> QR-code tickets with receipt PDFs</li>
          <li class="flex items-start gap-3"><span class="h-2 w-2 mt-2 rounded-full bg-pink-400"></span> Early-bird pricing and coupons</li>
          <li class="flex items-start gap-3"><span class="h-2 w-2 mt-2 rounded-full bg-cyan-400"></span> Capacity control and anti-oversell</li>
        </ul>

        <h2 class="mt-8 text-2xl font-bold">Why you'll love it</h2>
        <p class="mt-2 text-zinc-300">Fast checkout, clean design, and reliable receipts. Whether youre organizing a rooftop party or a club night, {{ config('app.name', '2DAWN') }} keeps the flow easy.</p>

        <div class="mt-8 flex flex-wrap gap-3">
          <a href="{{ route('events.index') }}" class="inline-flex items-center px-5 py-2.5 rounded-full bg-white text-black font-semibold hover:bg-zinc-100 transition">Browse Events</a>
          <a href="{{ url('/#host') }}" class="inline-flex items-center px-5 py-2.5 rounded-full bg-white/10 ring-1 ring-white/15 text-white hover:bg-white/15 transition">Host with us</a>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
