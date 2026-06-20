@extends('layouts.auth')

@section('content')
<div class="w-full max-w-md bg-white rounded-3xl shadow-xl border border-gray-100 p-8 sm:p-10 my-8">
    <div class="flex flex-col items-center gap-2 mb-8">
        <a href="{{ url('/') }}" class="inline-block" style="text-decoration:none;">
            <span class="inline-flex items-center font-black text-3xl tracking-tighter select-none" style="font-family:'Taskor', sans-serif;">
                <span style="color:#000000;margin-right:2px;">2</span>
                <span style="color:#8b5cf6;">DAWN</span>
            </span>
        </a>
        <h2 class="text-xl font-bold text-gray-900 mt-2">Create Organizer Account</h2>
    </div>

    @if ($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-red-50 text-red-700 text-sm font-bold">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('organizer.register.store') }}" class="flex flex-col gap-4">
        @csrf
        <div>
            <label for="name" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Full Name / Organization</label>
            <input type="text" id="name" name="name" placeholder="Your Name or Org" value="{{ old('name') }}" required autofocus
                   class="w-full rounded-2xl bg-gray-50 border-gray-200 focus:border-[#8b5cf6] focus:ring-[#8b5cf6] px-4 py-3 text-gray-900 font-bold">
        </div>
        <div>
            <label for="email" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Email</label>
            <input type="email" id="email" name="email" placeholder="you@example.com" value="{{ old('email') }}" required
                   class="w-full rounded-2xl bg-gray-50 border-gray-200 focus:border-[#8b5cf6] focus:ring-[#8b5cf6] px-4 py-3 text-gray-900 font-bold">
        </div>
        <div>
            <label for="password" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Password</label>
            <input type="password" id="password" name="password" placeholder="Create a password" required
                   class="w-full rounded-2xl bg-gray-50 border-gray-200 focus:border-[#8b5cf6] focus:ring-[#8b5cf6] px-4 py-3 text-gray-900 font-bold">
        </div>
        <div>
            <label for="password_confirmation" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Confirm Password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Repeat your password" required
                   class="w-full rounded-2xl bg-gray-50 border-gray-200 focus:border-[#8b5cf6] focus:ring-[#8b5cf6] px-4 py-3 text-gray-900 font-bold">
        </div>
        <button type="submit" class="w-full mt-4 py-4 rounded-2xl bg-black text-white font-black hover:bg-gray-900 hover:scale-[1.02] transition-all shadow-md">
            Create Account
        </button>
    </form>

    <p class="mt-8 text-center text-sm font-medium text-gray-600">
        Already have an account?<br>
        <a href="{{ route('organizer.login') }}" class="text-[#8b5cf6] font-bold hover:underline mt-1 inline-block">Sign in here</a>
    </p>
</div>
@endsection
