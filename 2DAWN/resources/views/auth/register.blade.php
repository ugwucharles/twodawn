@extends('layouts.auth')

@section('content')
<div class="form-container">
    <div class="logo-container">Sign Up</div>
    @if ($errors->any())
        <div class="alert-error">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif
    <form class="form" method="POST" action="{{ route('register') }}">
        @csrf
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" placeholder="Enter your name" value="{{ old('name') }}" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" value="{{ old('email') }}" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>
        </div>
        <div class="form-group">
            <label for="password_confirmation">Confirm Password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Confirm password" required>
        </div>
        <button class="form-submit-btn" type="submit">Register</button>
    </form>
    <p class="signup-link">
        Already have an account? <a href="{{ route('login') }}" class="link">Sign in</a>
    </p>
</div>
@endsection
