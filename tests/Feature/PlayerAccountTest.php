<?php

namespace Tests\Feature;

use App\Models\GameMatch;
use App\Models\Player;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PlayerAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_player_with_a_password(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('players.store'), ['nombre' => 'Ken', 'email' => 'ken@example.com', 'password' => 'secreto123'])
            ->assertSessionHasNoErrors();

        $player = Player::firstWhere('nombre', 'Ken');
        $this->assertNotNull($player->user_id);
        $this->assertDatabaseHas('users', ['id' => $player->user_id, 'email' => 'ken@example.com', 'name' => 'Ken', 'is_admin' => false]);
        $this->assertNotSame('secreto123', $player->user->password);
    }

    public function test_the_password_needs_an_email_and_a_minimum_length_and_a_free_email(): void
    {
        $admin = User::factory()->create(['email' => 'admin@example.com']);
        $this->actingAs($admin);

        $this->post(route('players.store'), ['nombre' => 'A', 'password' => 'secreto123'])->assertSessionHasErrors('email');
        $this->post(route('players.store'), ['nombre' => 'B', 'email' => 'b@example.com', 'password' => 'corta'])->assertSessionHasErrors('password');
        $this->post(route('players.store'), ['nombre' => 'C', 'email' => 'admin@example.com', 'password' => 'secreto123'])->assertSessionHasErrors('email');

        $this->assertDatabaseCount('players', 0);
    }

    public function test_admin_can_give_access_later_and_reset_the_password(): void
    {
        $this->actingAs(User::factory()->create());
        $player = Player::factory()->create(['nombre' => 'Ryu', 'email' => 'ryu@example.com']);

        $this->put(route('players.update', $player), ['nombre' => 'Ryu', 'email' => 'ryu@example.com', 'password' => 'primera-clave'])
            ->assertSessionHasNoErrors();
        $userId = $player->fresh()->user_id;
        $this->assertNotNull($userId);

        // sin contrasena no se cambia; con contrasena se reemplaza la misma cuenta
        $this->put(route('players.update', $player), ['nombre' => 'Ryu', 'email' => 'ryu@example.com']);
        $this->put(route('players.update', $player), ['nombre' => 'Ryu', 'email' => 'ryu@example.com', 'password' => 'segunda-clave']);

        $this->assertSame($userId, $player->fresh()->user_id);
        $this->assertDatabaseCount('users', 2);

        $this->post(route('logout'));
        $this->post(route('login.store'), ['email' => 'ryu@example.com', 'password' => 'segunda-clave'])->assertRedirect(route('ranking.index'));
        $this->assertAuthenticatedAs(User::find($userId));
    }

    public function test_a_player_with_access_cannot_clear_the_email(): void
    {
        $this->actingAs(User::factory()->create());
        $player = Player::factory()->create(['email' => 'p@example.com']);
        $this->put(route('players.update', $player), ['nombre' => $player->nombre, 'email' => 'p@example.com', 'password' => 'secreto123']);

        $this->put(route('players.update', $player), ['nombre' => $player->nombre, 'email' => ''])->assertSessionHasErrors('email');
    }

    public function test_a_player_can_view_ranking_and_dashboard_and_register_matches_but_nothing_else(): void
    {
        [$a, $b] = Player::factory()->count(2)->create();
        $match = GameMatch::factory()->create(['ganador_id' => $a->id, 'perdedor_id' => $b->id, 'victorias_ganador' => 5, 'victorias_perdedor' => 1]);
        $this->actingAs(User::factory()->player()->create());

        $this->get(route('ranking.index'))->assertOk();
        $this->get(route('dashboard'))->assertOk()
            ->assertDontSee('Eliminar')
            ->assertDontSee('edit-match-button', false);
        $this->post(route('matches.store'), [
            'fecha' => '2026-05-10T20:30', 'ganador_id' => $a->id, 'perdedor_id' => $b->id,
            'victorias_ganador' => 5, 'victorias_perdedor' => 2,
        ])->assertRedirect();
        $this->assertDatabaseCount('matches', 2);

        $this->put(route('matches.update', $match), [])->assertForbidden();
        $this->delete(route('matches.destroy', $match))->assertForbidden();
        $this->get(route('players.index'))->assertForbidden();
        $this->post(route('players.store'), ['nombre' => 'X'])->assertForbidden();
        $this->put(route('players.update', $a), ['nombre' => 'X'])->assertForbidden();
        $this->get(route('imagenes.index'))->assertForbidden();
        $this->assertDatabaseCount('matches', 2);
    }

    public function test_the_menu_hides_admin_modules_from_players(): void
    {
        $this->actingAs(User::factory()->player()->create());
        $this->get(route('ranking.index'))->assertOk()
            ->assertDontSee(route('players.index'), false)
            ->assertDontSee(route('imagenes.index'), false);

        $this->actingAs(User::factory()->create());
        $this->get(route('ranking.index'))->assertSee(route('players.index'), false)->assertSee(route('imagenes.index'), false);
    }

    public function test_match_registration_sends_no_email_to_player_accounts(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['email' => 'admin@example.com']);
        User::factory()->player()->create(['email' => 'otro-player@example.com']);
        $winner = Player::factory()->create(['email' => 'ganador@example.com']);
        $loser = Player::factory()->create();
        $this->actingAs($admin);

        $this->post(route('matches.store'), [
            'fecha' => '2026-05-10T20:30', 'ganador_id' => $winner->id, 'perdedor_id' => $loser->id,
            'victorias_ganador' => 5, 'victorias_perdedor' => 2,
        ]);

        Mail::assertNothingSent();
    }
}
