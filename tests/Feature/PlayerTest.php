<?php

namespace Tests\Feature;

use App\Models\Player;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlayerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_a_player_can_be_registered(): void
    {
        $this->post(route('players.store'), ['nombre' => '  Chino  '])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('players', ['nombre' => 'Chino', 'victorias' => 0, 'derrotas' => 0, 'puntos' => 0]);
    }

    public function test_email_is_optional_but_must_be_valid(): void
    {
        $this->post(route('players.store'), ['nombre' => 'Ana', 'email' => 'ana@example.com'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('players', ['nombre' => 'Ana', 'email' => 'ana@example.com']);

        $this->post(route('players.store'), ['nombre' => 'Beto', 'email' => 'no-es-correo'])->assertSessionHasErrors('email');
    }

    public function test_players_index_lists_players(): void
    {
        Player::factory()->create(['nombre' => 'Ken', 'email' => 'ken@example.com']);

        $this->get(route('players.index'))
            ->assertOk()
            ->assertSee('Ken')
            ->assertSee('ken@example.com');
    }

    public function test_a_player_can_be_updated_without_losing_stats(): void
    {
        $player = Player::factory()->create(['nombre' => 'Ryu']);
        $player->forceFill(['victorias' => 4, 'puntos' => 9])->save();

        $this->put(route('players.update', $player), ['nombre' => ' Ryu Hayabusa ', 'email' => 'ryu@example.com'])
            ->assertRedirect(route('players.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('players', ['id' => $player->id, 'nombre' => 'Ryu Hayabusa', 'email' => 'ryu@example.com', 'victorias' => 4, 'puntos' => 9]);
    }

    public function test_update_allows_same_name_but_rejects_another_players_name_and_clears_blank_email(): void
    {
        $a = Player::factory()->create(['nombre' => 'Guile', 'email' => 'g@example.com']);
        Player::factory()->create(['nombre' => 'Blanka']);

        $this->put(route('players.update', $a), ['nombre' => 'Blanka'])->assertSessionHasErrors('nombre');
        $this->put(route('players.update', $a), ['nombre' => 'Guile', 'email' => ''])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('players', ['id' => $a->id, 'nombre' => 'Guile', 'email' => null]);
    }

    public function test_updating_a_missing_player_returns_404(): void
    {
        $this->put('/players/999', ['nombre' => 'Nadie'])->assertNotFound();
    }

    public function test_name_is_required_and_unique_and_not_injectable(): void
    {
        Player::factory()->create(['nombre' => 'Pan']);

        $this->post(route('players.store'), ['nombre' => ''])->assertSessionHasErrors('nombre');
        $this->post(route('players.store'), ['nombre' => 'Pan'])->assertSessionHasErrors('nombre');

        $this->post(route('players.store'), ['nombre' => "x'); DROP TABLE players;--"])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('players', ['nombre' => "x'); DROP TABLE players;--"]);
    }
}
