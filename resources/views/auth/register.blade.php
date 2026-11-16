@extends('layouts.app')
@section('title', 'Register · Chess')
@section('content')
<div class="card narrow">
    <h1>Create account</h1>
    <form method="POST" action="{{ route('register') }}">
        @csrf
        <label>Name <input name="name" value="{{ old('name') }}" required maxlength="50" autofocus></label>
        @error('name')<p class="error">{{ $message }}</p>@enderror
        <label>Email <input type="email" name="email" value="{{ old('email') }}" required></label>
        @error('email')<p class="error">{{ $message }}</p>@enderror
        <label>Password <input type="password" name="password" required minlength="8"></label>
        @error('password')<p class="error">{{ $message }}</p>@enderror
        <label>Confirm password <input type="password" name="password_confirmation" required></label>
        <button class="btn">Register</button>
    </form>
    <p class="muted">Already registered? <a href="{{ route('login') }}">Log in</a></p>
</div>
@endsection
