<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Game extends Model
{
    protected $fillable = ['result', 'player_color', 'difficulty', 'moves', 'points', 'pgn'];

    /** Points awarded per result, multiplied by difficulty (1-3). */
    public const POINTS = ['win' => 10, 'draw' => 3, 'loss' => 0];

    public static function pointsFor(string $result, int $difficulty): int
    {
        return self::POINTS[$result] * $difficulty;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
