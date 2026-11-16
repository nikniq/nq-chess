<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\User;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function play()
    {
        return view('play');
    }

    /** Save a finished game for the logged-in user. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'result' => ['required', 'in:win,loss,draw'],
            'player_color' => ['required', 'in:w,b'],
            'difficulty' => ['required', 'integer', 'between:1,3'],
            'moves' => ['required', 'integer', 'min:0', 'max:2000'],
            'pgn' => ['nullable', 'string', 'max:20000'],
        ]);

        $data['points'] = Game::pointsFor($data['result'], $data['difficulty']);
        $game = $request->user()->games()->create($data);

        return response()->json([
            'points' => $game->points,
            'total' => $request->user()->games()->sum('points'),
        ]);
    }

    public function history(Request $request)
    {
        $user = $request->user();
        $games = $user->games()->latest()->paginate(20);
        $stats = $user->games()
            ->selectRaw("COALESCE(SUM(points),0) as total, SUM(result='win') as wins, SUM(result='loss') as losses, SUM(result='draw') as draws")
            ->first();

        return view('history', compact('games', 'stats'));
    }

    public function leaderboard()
    {
        $leaders = User::withSum('games as total_points', 'points')
            ->withCount(['games', 'games as wins' => fn ($q) => $q->where('result', 'win')])
            ->whereHas('games')
            ->orderByDesc('total_points')
            ->limit(25)
            ->get();

        return view('leaderboard', compact('leaders'));
    }
}
