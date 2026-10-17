<?php

namespace Tests\Feature;

use App\Mail\MatchRegistered;
use App\Models\GameMatch;
use App\Models\Player;
use App\Models\User;
use App\Services\MatchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MatchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    private function payload(Player $winner, Player $loser, array $overrides = []): array
    {
        return array_merge([
            'fecha' => '2026-05-10T20:30',
            'ganador_id' => $winner->id,
            'perdedor_id' => $loser->id,
            'victorias_ganador' => 5,
            'victorias_perdedor' => 2,
        ], $overrides);
    }

    public function test_storing_a_match_updates_player_totals(): void
    {
        [$winner, $loser] = Player::factory()->count(2)->create();

        $this->post(route('matches.store'), $this->payload($winner, $loser))
            ->assertRedirect(route('dashboard', ['season' => 2026]))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('matches', ['ganador_id' => $winner->id, 'perdedor_id' => $loser->id, 'victorias_perdedor' => 2]);
        $this->assertDatabaseHas('players', ['id' => $winner->id, 'victorias' => 1, 'derrotas' => 0, 'puntos' => 3]);
        $this->assertDatabaseHas('players', ['id' => $loser->id, 'victorias' => 0, 'derrotas' => 1, 'puntos' => -3]);
    }

    public function test_updating_a_match_reverts_old_totals_and_applies_new_ones(): void
    {
        [$a, $b] = Player::factory()->count(2)->create();
        $this->post(route('matches.store'), $this->payload($a, $b));
        $match = GameMatch::firstOrFail();

        $this->put(route('matches.update', $match), $this->payload($b, $a, ['victorias_ganador' => 3, 'victorias_perdedor' => 1]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('matches', ['id' => $match->id, 'ganador_id' => $b->id, 'victorias_ganador' => 3]);
        $this->assertDatabaseHas('players', ['id' => $a->id, 'victorias' => 0, 'derrotas' => 1, 'puntos' => -2]);
        $this->assertDatabaseHas('players', ['id' => $b->id, 'victorias' => 1, 'derrotas' => 0, 'puntos' => 2]);
    }

    public function test_recalculating_totals_matches_incremental_updates(): void
    {
        [$a, $b] = Player::factory()->count(2)->create();
        $this->post(route('matches.store'), $this->payload($a, $b));
        $this->post(route('matches.store'), $this->payload($b, $a, ['victorias_ganador' => 3, 'victorias_perdedor' => 0]));

        $before = Player::orderBy('id')->get(['victorias', 'derrotas', 'puntos'])->toArray();
        app(MatchService::class)->recalculateAllPlayerTotals();

        $this->assertSame($before, Player::orderBy('id')->get(['victorias', 'derrotas', 'puntos'])->toArray());
    }

    public function test_it_rejects_invalid_matches(): void
    {
        [$a, $b] = Player::factory()->count(2)->create();

        $this->post(route('matches.store'), $this->payload($a, $a))->assertSessionHasErrors('perdedor_id');
        $this->post(route('matches.store'), $this->payload($a, $b, ['victorias_ganador' => 4]))->assertSessionHasErrors('victorias_ganador');
        $this->post(route('matches.store'), $this->payload($a, $b, ['victorias_ganador' => 3, 'victorias_perdedor' => 3]))->assertSessionHasErrors('victorias_perdedor');
        $this->post(route('matches.store'), $this->payload($a, $b, ['ganador_id' => 9999]))->assertSessionHasErrors('ganador_id');
        $this->post(route('matches.store'), $this->payload($a, $b, ['fecha' => '']))->assertSessionHasErrors('fecha');

        $this->assertDatabaseCount('matches', 0);
        $this->assertDatabaseHas('players', ['id' => $a->id, 'victorias' => 0, 'puntos' => 0]);
    }

    public function test_storing_a_match_emails_players_and_admins_once_each(): void
    {
        Mail::fake();
        $admin = User::first();
        $winner = Player::factory()->create(['email' => 'gana@example.com']);
        $loser = Player::factory()->create(['email' => strtoupper($admin->email)]);
        Player::factory()->create(['email' => 'otro@example.com']);

        $this->post(route('matches.store'), $this->payload($winner, $loser))->assertSessionHas('success');

        Mail::assertSent(MatchRegistered::class, 2);
        Mail::assertSent(MatchRegistered::class, fn ($m) => $m->hasTo('gana@example.com'));
        Mail::assertSent(MatchRegistered::class, fn ($m) => $m->hasTo(strtolower($admin->email)));
        Mail::assertNotSent(MatchRegistered::class, fn ($m) => $m->hasTo('otro@example.com'));
    }

    public function test_a_mail_failure_does_not_break_match_registration(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('smtp down'));
        [$winner, $loser] = Player::factory()->count(2)->create();

        $this->post(route('matches.store'), $this->payload($winner, $loser))->assertSessionHas('success');

        $this->assertDatabaseCount('matches', 1);
    }

    public function test_updating_a_match_does_not_send_email(): void
    {
        [$a, $b] = Player::factory()->count(2)->create(['email' => 'x@example.com']);
        $this->post(route('matches.store'), $this->payload($a, $b));
        Mail::fake();

        $this->put(route('matches.update', GameMatch::first()), $this->payload($a, $b, ['victorias_ganador' => 6]));

        Mail::assertNothingSent();
    }

    public function test_updating_a_missing_match_returns_404(): void
    {
        [$a, $b] = Player::factory()->count(2)->create();

        $this->put('/matches/999', $this->payload($a, $b))->assertNotFound();
    }
}
