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

    public function test_name_is_required_and_unique_and_not_injectable(): void
    {
        Player::factory()->create(['nombre' => 'Pan']);

        $this->post(route('players.store'), ['nombre' => ''])->assertSessionHasErrors('nombre');
        $this->post(route('players.store'), ['nombre' => 'Pan'])->assertSessionHasErrors('nombre');

        $this->post(route('players.store'), ['nombre' => "x'); DROP TABLE players;--"])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('players', ['nombre' => "x'); DROP TABLE players;--"]);
    }
}
