<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('result', ['win', 'loss', 'draw']);
            $table->enum('player_color', ['w', 'b'])->default('w');
            $table->unsignedTinyInteger('difficulty')->default(2);
            $table->unsignedSmallInteger('moves')->default(0);
            $table->unsignedInteger('points')->default(0);
            $table->text('pgn')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'points']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
