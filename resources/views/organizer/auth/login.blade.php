@extends('layouts.auth')

@section('content')
<div class="form-container">
    <div class="logo-container">Organizer Sign In</div>

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

    <form class="form" method="POST" action="{{ route('organizer.login.store') }}">
        @csrf
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" value="{{ old('email') }}" required autofocus autocomplete="email">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
        </div>
        <div class="form-row" style="display:flex;justify-content:space-between;align-items:center;margin-top:4px;">
            <label class="checkbox-label" style="display:flex;align-items:center;gap:6px;font-size:13px;">
                <input type="checkbox" name="remember"> Remember me
            </label>
        </div>
        <button class="form-submit-btn" type="submit">Sign In</button>
    </form>

    <p class="signup-link">
        Don't have an organizer account?
        <a href="{{ route('organizer.register') }}" class="signup-link link">Create one here</a>
    </p>
</div>
@endsection
