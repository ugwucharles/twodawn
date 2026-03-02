@extends('layouts.auth')

@section('content')
<div class="form-container">
    <div class="logo-container">Create Organizer Account</div>

    @if ($errors->any())
        <div class="alert-error">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form class="form" method="POST" action="{{ route('organizer.register.store') }}">
        @csrf
        <div class="form-group">
            <label for="name">Full Name / Organization</label>
            <input type="text" id="name" name="name" placeholder="Your Name or Org" value="{{ old('name') }}" required autofocus>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="you@example.com" value="{{ old('email') }}" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Create a password" required>
        </div>
        <div class="form-group">
            <label for="password_confirmation">Confirm Password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Repeat your password" required>
        </div>
        <button class="form-submit-btn" type="submit">Create Account</button>
    </form>

    <p class="signup-link">
        Already have an account?
        <a href="{{ route('organizer.login') }}" class="signup-link link">Sign in here</a>
    </p>
</div>
@endsection
