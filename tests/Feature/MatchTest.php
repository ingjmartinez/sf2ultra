<?php

namespace Tests\Feature;

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

    public function test_the_match_records_who_saved_it_and_the_dashboard_shows_it(): void
    {
        $admin = User::factory()->create(['name' => 'Admin Registrador']);
        $this->actingAs($admin);
        [$a, $b] = Player::factory()->count(2)->create();

        $this->post(route('matches.store'), $this->payload($a, $b));

        $this->assertDatabaseHas('matches', ['ganador_id' => $a->id, 'user_id' => $admin->id]);
        $this->get(route('dashboard', ['season' => 2026]))->assertSee('Registrado por')->assertSee('Admin Registrador');

        // editar no cambia quien lo registro
        $this->actingAs(User::factory()->create());
        $this->put(route('matches.update', GameMatch::firstOrFail()), $this->payload($a, $b, ['victorias_perdedor' => 1]));
        $this->assertDatabaseHas('matches', ['victorias_perdedor' => 1, 'user_id' => $admin->id]);
    }

    public function test_early_morning_matches_are_allowed_but_the_dashboard_carries_the_warning_cutoff(): void
    {
        [$a, $b] = Player::factory()->count(2)->create();

        $this->post(route('matches.store'), $this->payload($a, $b, ['fecha' => '2026-05-11T00:30']))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertDatabaseCount('matches', 1);
        $this->get(route('dashboard', ['season' => 2026]))
            ->assertOk()
            ->assertSee('modalAvisoHora', false)
            ->assertSee('Valida la hora del registro')
            ->assertSee('matchCutoffHour', false);
    }

    public function test_matches_are_listed_by_registration_order_not_by_hour(): void
    {
        [$a, $b] = Player::factory()->count(2)->create();

        // el segundo registro tiene una hora anterior al primero
        $this->post(route('matches.store'), $this->payload($a, $b, ['fecha' => '2026-05-10T22:00']));
        $this->post(route('matches.store'), $this->payload($b, $a, ['fecha' => '2026-05-10T09:00']));
        [$first, $second] = [GameMatch::orderBy('id')->first(), GameMatch::orderByDesc('id')->first()];

        $this->get(route('dashboard', ['season' => 2026]))
            ->assertSeeInOrder(['data-order="'.$second->id.'"', 'data-order="'.$first->id.'"'], false);
    }

    public function test_the_date_and_time_of_a_registered_match_can_be_changed(): void
    {
        [$a, $b] = Player::factory()->count(2)->create();
        $this->post(route('matches.store'), $this->payload($a, $b, ['fecha' => '2026-05-11T20:30']));
        $match = GameMatch::firstOrFail();

        $this->put(route('matches.update', $match), $this->payload($a, $b, ['fecha' => '2026-05-10T23:45']))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('dashboard', ['season' => 2026]));

        $this->assertSame('2026-05-10 23:45:00', $match->fresh()->fecha->format('Y-m-d H:i:s'));

        // los totales de los players no se alteran por cambiar solo la fecha
        $this->assertDatabaseHas('players', ['id' => $a->id, 'victorias' => 1, 'puntos' => 3]);
    }

    public function test_deleting_a_match_reverts_player_totals(): void
    {
        [$a, $b] = Player::factory()->count(2)->create();
        $this->post(route('matches.store'), $this->payload($a, $b));
        $this->post(route('matches.store'), $this->payload($b, $a, ['victorias_ganador' => 3, 'victorias_perdedor' => 0]));
        $wrong = GameMatch::orderBy('id')->firstOrFail();

        $this->delete(route('matches.destroy', $wrong))
            ->assertRedirect(route('dashboard', ['season' => 2026]))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('matches', ['id' => $wrong->id]);
        $this->assertDatabaseCount('matches', 1);
        $this->assertDatabaseHas('players', ['id' => $a->id, 'victorias' => 0, 'derrotas' => 1, 'puntos' => -3]);
        $this->assertDatabaseHas('players', ['id' => $b->id, 'victorias' => 1, 'derrotas' => 0, 'puntos' => 3]);
    }

    public function test_guests_cannot_delete_matches(): void
    {
        [$a, $b] = Player::factory()->count(2)->create();
        $this->post(route('matches.store'), $this->payload($a, $b));
        $match = GameMatch::firstOrFail();

        auth()->logout();

        $this->delete(route('matches.destroy', $match))->assertRedirect(route('login'));
        $this->assertDatabaseCount('matches', 1);
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

    public function test_storing_a_match_does_not_send_email(): void
    {
        Mail::fake();
        $winner = Player::factory()->create(['email' => 'gana@example.com']);
        $loser = Player::factory()->create(['email' => 'pierde@example.com']);

        $this->post(route('matches.store'), $this->payload($winner, $loser))->assertSessionHas('success');

        Mail::assertNothingSent();
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
