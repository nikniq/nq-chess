@extends('layouts.app')
@section('content')
<div class="game">
    <div class="board-wrap">
        <div id="status" class="status">White to move</div>
        <div id="board" class="board" aria-label="Chess board"></div>
    </div>
    <aside class="panel card">
        <h2>New game</h2>
        <label>Play as
            <select id="color"><option value="w">White</option><option value="b">Black</option></select>
        </label>
        <label>Difficulty
            <select id="difficulty">
                <option value="1">Easy (×1 points)</option>
                <option value="2" selected>Medium (×2 points)</option>
                <option value="3">Hard (×3 points)</option>
            </select>
        </label>
        <div class="row">
            <button id="newGame" class="btn">Start</button>
            <button id="undo" class="btn ghost">Undo</button>
            <button id="resign" class="btn ghost">Resign</button>
        </div>
        <p class="muted small">Scoring: win 10, draw 3, loss 0 — multiplied by difficulty.</p>
        @guest
            <p class="notice">Playing as guest. <a href="{{ route('login') }}">Log in</a> or <a href="{{ route('register') }}">register</a> to save your scores.</p>
        @endguest
        <div id="result" class="result" hidden></div>
        <h3>Moves</h3>
        <ol id="moves" class="moves"></ol>
    </aside>
</div>
@endsection
@push('scripts')
<script>
    window.CHESS = {
        loggedIn: @json(auth()->check()),
        saveUrl: @json(route('games.store')),
        loginUrl: @json(route('login')),
    };
</script>
<script type="module" src="{{ asset('js/game.js') }}"></script>
@endpush
