<?php

namespace Tests\Feature;

use App\Models\GameMatch;
use App\Models\Player;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatsTest extends TestCase
{
    use RefreshDatabase;

    private Player $yulaw;

    private Player $pan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());

        $this->yulaw = Player::factory()->create(['nombre' => 'Yulaw']);
        $this->pan = Player::factory()->create(['nombre' => 'Pan']);

        $this->match('2025-06-01 10:00:00', $this->yulaw, $this->pan, 5, 1);
        $this->match('2025-06-01 11:00:00', $this->yulaw, $this->pan, 5, 4);
        $this->match('2025-06-01 12:00:00', $this->pan, $this->yulaw, 3, 0);
        $this->match('2026-01-02 09:00:00', $this->yulaw, $this->pan, 3, 2);
    }

    private function match(string $fecha, Player $w, Player $l, int $vg, int $vp): void
    {
        GameMatch::create([
            'fecha' => $fecha, 'ganador_id' => $w->id, 'perdedor_id' => $l->id,
            'victorias_ganador' => $vg, 'victorias_perdedor' => $vp,
        ]);
    }

    public function test_dashboard_renders_season_ranking_and_matches(): void
    {
        $this->get(route('dashboard', ['season' => 2025]))
            ->assertOk()
            ->assertSeeInOrder(['Ranking por puntos - Season 2025', 'Yulaw', 'Pan'])
            ->assertSee('Season 2026')
            ->assertViewHas('matches', fn ($matches) => $matches->count() === 3);
    }

    public function test_season_ranking_uses_points_then_wins(): void
    {
        $ranking = $this->get(route('dashboard', ['season' => 2025]))->viewData('rankings');

        $this->assertSame(['Yulaw', 'Pan'], $ranking->pluck('nombre')->all());
        $this->assertSame([1, 2], $ranking->pluck('ranking')->all());
        // Yulaw: +4, +1, -3 = 2 ; Pan: -4, -1, +3 = -2
        $this->assertSame([2, -2], $ranking->pluck('puntos')->all());
    }

    public function test_season_stats_percentages_and_trend(): void
    {
        $stats = $this->get(route('dashboard', ['season' => 2025]))->viewData('seasonStats');

        $yulaw = $stats->firstWhere('nombre', 'Yulaw');
        $this->assertSame(3, $yulaw['total_match']);
        $this->assertSame(66.67, $yulaw['porcentaje_victoria']);
        $this->assertSame('down', $yulaw['tendencia']);
        $this->assertSame('up', $stats->firstWhere('nombre', 'Pan')['tendencia']);
    }

    public function test_invalid_query_params_fall_back_to_defaults(): void
    {
        $this->get(route('dashboard', ['season' => "2025' OR 1=1", 'report_fecha' => 'nope', 'player_filtro' => 'x']))
            ->assertOk()
            ->assertViewHas('season', now()->format('Y'));
    }

    public function test_filters_by_player_and_date(): void
    {
        $this->get(route('dashboard', ['season' => 2025, 'fecha_filtro' => '2025-06-01', 'player_filtro' => $this->pan->id]))
            ->assertViewHas('matches', fn ($m) => $m->count() === 3);

        $this->get(route('dashboard', ['season' => 2025, 'fecha_filtro' => '2025-06-02']))
            ->assertViewHas('matches', fn ($m) => $m->isEmpty());
    }

    public function test_daily_report(): void
    {
        $response = $this->get(route('dashboard', ['season' => 2025, 'report_fecha' => '2025-06-01']));

        $response->assertViewHas('openDailyReport', true);
        $this->assertSame('Yulaw', $response->viewData('dailyRankings')->first()['nombre']);
        $this->assertCount(3, $response->viewData('dailyMatches'));
    }

    public function test_player_stats_endpoint(): void
    {
        $this->getJson(route('api.player-stats', ['player' => $this->yulaw->id, 'season' => 2025]))
            ->assertOk()
            ->assertJsonPath('posicion', 1)
            ->assertJsonPath('totales.totalMatch', 3)
            ->assertJsonPath('totales.totalVictorias', 2)
            ->assertJsonPath('totales.porcentajeVictoria', 67)
            ->assertJsonPath('data.0.vs_player', 'Pan');

        $this->getJson(route('api.player-stats'))
            ->assertOk()
            ->assertJsonPath('posicion', '-')
            ->assertJsonPath('data', []);
    }

    public function test_versus_endpoint(): void
    {
        $this->getJson(route('api.versus', ['player1' => $this->yulaw->id, 'player2' => $this->pan->id, 'fecha' => '2025-06-01']))
            ->assertOk()
            ->assertJsonPath('stats1.nombre', 'Yulaw')
            ->assertJsonPath('stats1.victorias', 2)
            ->assertJsonPath('stats1.puntos', 2)
            ->assertJsonCount(3, 'h2h')
            ->assertJsonPath('matches1.0.vd', 'D');
    }

    public function test_versus_endpoint_validation(): void
    {
        $this->getJson(route('api.versus', ['player1' => $this->yulaw->id, 'player2' => $this->yulaw->id, 'fecha' => '2025-06-01']))
            ->assertStatus(422)->assertJsonPath('error', 'Selecciona dos players distintos.');

        $this->getJson(route('api.versus', ['player1' => 1, 'player2' => 2, 'fecha' => 'ayer']))
            ->assertStatus(422)->assertJsonStructure(['error']);
    }
}
