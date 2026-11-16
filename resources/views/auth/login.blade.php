@extends('layouts.app')
@section('title', 'Log in · Chess')
@section('content')
<div class="card narrow">
    <h1>Log in</h1>
    <p class="muted">Log in to save your scores and appear on the leaderboard.</p>
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <label>Email <input type="email" name="email" value="{{ old('email') }}" required autofocus></label>
        @error('email')<p class="error">{{ $message }}</p>@enderror
        <label>Password <input type="password" name="password" required></label>
        <label class="check"><input type="checkbox" name="remember"> Remember me</label>
        <button class="btn">Log in</button>
    </form>
    <p class="muted">No account? <a href="{{ route('register') }}">Register</a></p>
</div>
@endsection
