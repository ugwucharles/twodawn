@extends('layouts.auth')

@section('content')
<div class="form-container">
    <div class="logo-container">Forgot Password</div>
    @if (session('status'))
        <div class="alert-success">{{ session('status') }}</div>
    @endif
    <form class="form" method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" value="{{ old('email') }}" required>
        </div>
        <button class="form-submit-btn" type="submit">Send Email</button>
    </form>
    <p class="signup-link">
        Remembered? <a href="{{ route('login') }}" class="link">Sign in</a>
    </p>
</div>
@endsection
