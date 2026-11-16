@extends('layouts.app')
@section('title', 'My games · Chess')
@section('content')
<h1>My games</h1>
<div class="stats">
    <div class="card stat"><b>{{ (int) $stats->total }}</b><span>Total points</span></div>
    <div class="card stat"><b>{{ (int) $stats->wins }}</b><span>Wins</span></div>
    <div class="card stat"><b>{{ (int) $stats->draws }}</b><span>Draws</span></div>
    <div class="card stat"><b>{{ (int) $stats->losses }}</b><span>Losses</span></div>
</div>
<div class="card">
@if ($games->isEmpty())
    <p class="muted">No games yet. <a href="{{ route('home') }}">Play one!</a></p>
@else
    <table>
        <thead><tr><th>Date</th><th>Result</th><th>Colour</th><th>Difficulty</th><th>Moves</th><th>Points</th></tr></thead>
        <tbody>
        @foreach ($games as $g)
            <tr>
                <td>{{ $g->created_at->format('Y-m-d H:i') }}</td>
                <td class="res-{{ $g->result }}">{{ ucfirst($g->result) }}</td>
                <td>{{ $g->player_color === 'w' ? 'White' : 'Black' }}</td>
                <td>{{ ['', 'Easy', 'Medium', 'Hard'][$g->difficulty] }}</td>
                <td>{{ $g->moves }}</td>
                <td>{{ $g->points }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    {{ $games->links() }}
@endif
</div>
@endsection
