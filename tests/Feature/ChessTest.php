<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_play_and_view_leaderboard(): void
    {
        $this->get('/')->assertOk()->assertSee('Playing as guest');
        $this->get('/leaderboard')->assertOk();
    }

    public function test_guest_cannot_save_score(): void
    {
        $this->postJson('/games', ['result' => 'win'])->assertUnauthorized();
    }

    public function test_register_login_and_save_score(): void
    {
        $this->post('/register', [
            'name' => 'Alice', 'email' => 'a@example.com',
            'password' => 'secret123', 'password_confirmation' => 'secret123',
        ])->assertRedirect('/');
        $this->assertAuthenticated();

        $this->postJson('/games', ['result' => 'win', 'player_color' => 'w', 'difficulty' => 3, 'moves' => 30])
            ->assertOk()->assertJson(['points' => 30, 'total' => 30]);

        $this->get('/my-games')->assertOk()->assertSee('30');
        $this->get('/leaderboard')->assertOk()->assertSee('Alice');

        $this->post('/logout');
        $this->assertGuest();
        $this->post('/login', ['email' => 'a@example.com', 'password' => 'secret123'])->assertRedirect('/');
        $this->assertAuthenticated();
    }

    public function test_score_is_computed_server_side(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)
            ->postJson('/games', ['result' => 'draw', 'player_color' => 'b', 'difficulty' => 2, 'moves' => 10, 'points' => 9999])
            ->assertJson(['points' => 6]);
    }
}
