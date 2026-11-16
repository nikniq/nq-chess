<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Chess')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<header class="topbar">
    <a href="{{ route('home') }}" class="brand">♞ Chess</a>
    <nav>
        <a href="{{ route('home') }}">Play</a>
        <a href="{{ route('leaderboard') }}">Leaderboard</a>
        @auth
            <a href="{{ route('history') }}">My games</a>
            <span class="who">{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}" class="inline">@csrf<button class="link">Log out</button></form>
        @else
            <a href="{{ route('login') }}">Log in</a>
            <a href="{{ route('register') }}" class="btn small">Register</a>
        @endauth
    </nav>
</header>
<main class="container">
    @yield('content')
</main>
<footer class="footer">
    A <a href="https://nikniq.com" target="_blank" rel="noopener">nikniq.com</a> game
</footer>
@stack('scripts')
</body>
</html>
