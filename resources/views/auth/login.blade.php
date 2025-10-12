@extends('layouts.public')

@section('content')
<section class="min-h-[80vh] flex items-center justify-center px-6 py-16">
  <div class="w-full max-w-md">
    @if (session('status'))
      <div class="mb-4 p-3 bg-emerald-500/10 text-emerald-300 rounded ring-1 ring-emerald-500/30">
        {{ session('status') }}
      </div>
    @endif

    @if ($errors->any())
      <div class="mb-4 p-3 bg-red-500/10 text-red-300 rounded ring-1 ring-red-500/30">
        <ul class="list-disc list-inside">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <div class="rounded-2xl bg-white/5 ring-1 ring-white/10 p-6">
      <h1 class="text-2xl font-extrabold mb-4">Admin Login</h1>
      <form method="POST" action="{{ route('admin.login.store') }}" class="space-y-4">
        @csrf
        <div>
          <label class="block text-sm text-zinc-300" for="email">Email</label>
          <input id="email" name="email" type="email" required autofocus autocomplete="username" value="{{ old('email') }}" class="mt-1 block w-full rounded-lg bg-black/30 border border-white/10 focus:border-white/30 focus:ring-0 px-3 py-2" />
        </div>
        <div>
          <label class="block text-sm text-zinc-300" for="password">Password</label>
          <input id="password" name="password" type="password" required autocomplete="current-password" class="mt-1 block w-full rounded-lg bg-black/30 border border-white/10 focus:border-white/30 focus:ring-0 px-3 py-2" />
        </div>
        <div class="flex items-center justify-between">
          <label class="inline-flex items-center text-sm text-zinc-300">
            <input type="checkbox" name="remember" class="rounded border-white/10 bg-black/30 text-indigo-500 focus:ring-0 mr-2" /> Remember me
          </label>
          @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}" class="text-sm text-zinc-400 hover:text-white">Forgot?</a>
          @endif
        </div>
        <button class="w-full inline-flex items-center justify-center px-6 py-3 rounded-xl bg-white text-black font-semibold hover:bg-zinc-100 transition">Log in</button>
      </form>
    </div>
  </div>
</section>
@endsection
