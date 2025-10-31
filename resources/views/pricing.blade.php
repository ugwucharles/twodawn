@extends('layouts.public')

@section('title', 'Pricing | ' . config('app.name', '2DAWN'))
@section('meta_description', 'Simple, transparent pricing for events — free or 5% + ₦50 per paid ticket.')
@section('canonical', route('pricing'))

@section('content')

<!-- Hero (matches homepage style but shorter) -->
<section class="relative min-h-[40vh] flex items-end pb-0 pt-8 sm:pt-12">
  @include('partials.public-header')
  <div class="absolute inset-0 -z-10">
    <div class="absolute -top-48 -left-32 h-[40rem] w-[40rem] rounded-full blur-3xl opacity-30 bg-gradient-to-br from-indigo-500 via-fuchsia-500 to-rose-500"></div>
    <div class="absolute -bottom-48 -right-32 h-[40rem] w-[40rem] rounded-full blur-3xl opacity-20 bg-gradient-to-tr from-sky-500 to-emerald-400"></div>
  </div><br>
  <br>
  <div class="w-full mx-auto px-6 text-center">
    <h1 class="fluid-title font-extrabold tracking-tight">Transparent pricing</h1>
    <p class="mt-4 text-zinc-300">Only two options. No hidden fees.</p>
  </div>
</section>

<!-- Plans -->
<section class="relative py-16 sm:py-20">
  <div class="max-w-7xl mx-auto px-6">
    <div class="mx-auto max-w-4xl grid grid-cols-1 md:grid-cols-2 gap-[20px] mb-16">
      <!-- Free -->
      <div class="rounded-3xl bg-white/5 ring-1 ring-white/10 p-8 md:p-10 flex flex-col">
        <div class="text-sm uppercase tracking-widest text-zinc-400">Plan</div>
        <h2 class="mt-1 text-2xl font-bold">Free events</h2>
        <p class="mt-2 text-zinc-300">Zero platform fee for ₦0 tickets.</p>
        <div class="mt-6 text-4xl font-extrabold">₦0</div>
        <div class="text-xs text-zinc-400">per ticket</div>
        <ul class="mt-6 space-y-2 text-sm text-zinc-300 list-disc list-inside">
          <li>Guest checkout</li>
          <li>QR tickets + PDF receipts</li>
          <li>Basic analytics</li>
        </ul>
        <div class="mt-auto pt-6"><a href="{{ route('events.index') }}" class="inline-flex px-5 py-2.5 rounded-full bg-white text-black text-sm font-semibold hover:bg-zinc-100">Start free</a></div>
      </div>
      <!-- Paid -->
      <div class="rounded-3xl bg-white/5 ring-2 ring-indigo-400/40 p-8 md:p-10 flex flex-col">
        <div class="text-sm uppercase tracking-widest text-zinc-400">Plan</div>
        <h2 class="mt-1 text-2xl font-bold">Paid events</h2>
        <p class="mt-2 text-zinc-300">Simple fee per ticket. Pass to buyer or absorb.</p>
        <div class="mt-6 text-4xl font-extrabold">5% + ₦50</div>
        <div class="text-xs text-zinc-400">per paid ticket (+ Paystack fees)</div>
        <ul class="mt-6 space-y-2 text-sm text-zinc-300 list-disc list-inside">
          <li>Coupons & capacity limits</li>
          <li>Automatic payouts via Paystack</li>
          <li>Email confirmations</li>
        </ul>
        <div class="mt-auto pt-6"><a href="{{ route('events.index') }}" class="inline-flex px-5 py-2.5 rounded-full bg-white text-black text-sm font-semibold hover:bg-zinc-100">Create an event</a></div>
      </div>
    </div>

    <!-- Add-ons -->
    <div class="rounded-3xl bg-white/5 ring-1 ring-white/10 p-10 md:p-12">
      <h3 class="text-lg font-semibold">Add‑ons</h3>
      <p class="mt-1 text-sm text-zinc-400">Optional services when you need extra help.</p>
      <ul class="mt-4 text-sm text-zinc-300 list-disc list-inside space-y-2">
        <li>Wristband management</li>
        <li>On‑site staffing</li>
        <li>Custom branding</li>
        <li>Reports & exports</li>
      </ul>
      <div class="mt-6"><a href="mailto:{{ env('ADMIN_EMAIL','hello@example.com') }}" class="inline-flex px-5 py-2.5 rounded-full bg-white text-black text-sm font-semibold hover:bg-zinc-100">Contact us</a></div>
    </div>

    <div class="text-center mt-16">
      <a href="{{ route('events.index') }}" class="inline-flex items-center px-6 py-3 rounded-full bg-white text-black font-semibold hover:bg-zinc-100">Browse events</a>
    </div>
  </div>
</section>
@endsection
