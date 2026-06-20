@extends('layouts.admin-auth')

@section('content')
<div class="admin-card">
    <div class="admin-badge">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        Admin Portal
    </div>
    <h1 class="admin-title">Sign In</h1>
    <p class="admin-subtitle">Access the {{ config('app.name', '2DAWN') }} admin dashboard</p>

    @if (session('status'))
        <div class="alert-success">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert-error">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form class="admin-form" method="POST" action="{{ route('admin.login.store') }}">
        @csrf
        <div>
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="admin@example.com" value="{{ old('email') }}" required autofocus autocomplete="email">
        </div>
        <div>
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="••••••••" required autocomplete="current-password">
        </div>
        <div class="form-row">
            <label>
                <input type="checkbox" name="remember"> Remember me
            </label>
        </div>
        <button class="admin-btn" type="submit">Sign In</button>
    </form>
</div>
@endsection
