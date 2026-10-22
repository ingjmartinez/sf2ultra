<?php

namespace Tests\Feature;

use App\Models\GameMatch;
use App\Models\Player;
use App\Models\User;
use App\Services\StatsService;
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

    public function test_ranking_page_lists_season_ranking(): void
    {
        $this->get(route('ranking.index', ['season' => 2025]))
            ->assertOk()
            ->assertSee('Ranking - Season 2025')
            ->assertSeeInOrder(['Yulaw', '66.67%', 'Pan', '33.33%']);
    }

    public function test_win_percent_trends_follow_the_last_matchdays(): void
    {
        $stats = app(StatsService::class);
        $trend = $stats->winPercentTrends('2025')[$this->yulaw->id];

        // 3 matches el mismo dia: gana, gana, pierde -> 66.67% al cierre de la jornada
        $this->assertSame([['fecha' => '2025-06-01', 'porcentaje' => 66.67, 'total' => 3]], $trend);

        // el ultimo punto coincide con el % del ranking
        $this->assertEquals(
            end($trend)['porcentaje'],
            $stats->seasonStats('2025')->firstWhere('id', $this->yulaw->id)['porcentaje_victoria']
        );
    }

    public function test_win_percent_trends_keep_only_five_matchdays(): void
    {
        foreach (range(2, 8) as $day) {
            $this->match("2025-07-0{$day} 10:00:00", $this->pan, $this->yulaw, 3, 1);
        }

        $trend = app(StatsService::class)->winPercentTrends('2025')[$this->pan->id];

        $this->assertCount(5, $trend);
        $this->assertSame('2025-07-08', end($trend)['fecha']);
    }

    public function test_ranking_percent_is_colored_by_change_against_previous_matchday(): void
    {
        // Yulaw baja (66.67% -> 50%) y Pan sube (33.33% -> 50%)
        $this->match('2025-06-02 10:00:00', $this->pan, $this->yulaw, 3, 1);

        $html = $this->get(route('ranking.index', ['season' => 2025]))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/text-danger">50\.00%/', $html);
        $this->assertMatchesRegularExpression('/text-success">50\.00%/', $html);
    }

    public function test_sidebar_modal_links_lead_to_the_dashboard_from_other_modules(): void
    {
        $this->get(route('ranking.index'))
            ->assertOk()
            ->assertSee(route('dashboard', ['abrir' => 'modalVSDia']), false)
            ->assertDontSee('data-bs-target="#modalVSDia"', false);

        $this->get(route('dashboard'))->assertSee('data-bs-target="#modalVSDia"', false);
    }

    public function test_ranking_page_requires_authentication(): void
    {
        auth()->logout();

        $this->get(route('ranking.index'))->assertRedirect(route('login'));
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

    public function test_daily_report_orders_by_match_wins_before_points(): void
    {
        $third = Player::factory()->create(['nombre' => 'Chun']);
        $this->match('2025-07-01 10:00:00', $this->yulaw, $this->pan, 5, 4);
        $this->match('2025-07-01 11:00:00', $this->yulaw, $this->pan, 5, 4);
        $this->match('2025-07-01 12:00:00', $this->pan, $third, 5, 0);

        $ranking = $this->get(route('dashboard', ['report_fecha' => '2025-07-01']))->viewData('dailyRankings');

        $this->assertSame(['Yulaw', 'Pan', 'Chun'], $ranking->pluck('nombre')->all());
        $this->assertSame([2, 3], $ranking->take(2)->pluck('puntos')->all());
    }

    public function test_daily_report_uses_direct_win_rate_to_break_a_tie_in_victories(): void
    {
        $third = Player::factory()->create(['nombre' => 'Chun']);
        $this->match('2025-07-02 10:00:00', $this->yulaw, $this->pan, 5, 4);
        $this->match('2025-07-02 11:00:00', $this->yulaw, $this->pan, 5, 4);
        $this->match('2025-07-02 12:00:00', $this->pan, $this->yulaw, 5, 0);
        $this->match('2025-07-02 13:00:00', $third, $this->yulaw, 5, 0);
        $this->match('2025-07-02 14:00:00', $third, $this->yulaw, 5, 0);
        $this->match('2025-07-02 15:00:00', $third, $this->yulaw, 5, 0);
        $this->match('2025-07-02 16:00:00', $this->pan, $third, 5, 0);

        $ranking = $this->get(route('dashboard', ['report_fecha' => '2025-07-02']))->viewData('dailyRankings');

        $this->assertSame(['Chun', 'Yulaw', 'Pan'], $ranking->pluck('nombre')->all());
        $this->assertSame(33.33, $ranking->firstWhere('nombre', 'Yulaw')['win_rate']);
        $this->assertSame(50.0, $ranking->firstWhere('nombre', 'Pan')['win_rate']);
    }

    public function test_daily_report_uses_daily_win_rate_when_tied_players_did_not_face_each_other(): void
    {
        $third = Player::factory()->create(['nombre' => 'Chun']);
        $fourth = Player::factory()->create(['nombre' => 'Ken']);
        $this->match('2025-07-03 10:00:00', $this->yulaw, $third, 5, 4);
        $this->match('2025-07-03 11:00:00', $third, $this->yulaw, 5, 4);
        $this->match('2025-07-03 12:00:00', $this->pan, $fourth, 5, 4);
        $this->match('2025-07-03 13:00:00', $fourth, $this->pan, 5, 4);
        $this->match('2025-07-03 14:00:00', $fourth, $this->pan, 5, 4);

        $ranking = $this->get(route('dashboard', ['report_fecha' => '2025-07-03']))->viewData('dailyRankings');

        $this->assertSame(['Ken', 'Chun', 'Yulaw', 'Pan'], $ranking->pluck('nombre')->all());
    }

    public function test_daily_report_uses_daily_win_rate_when_direct_matches_are_tied(): void
    {
        $third = Player::factory()->create(['nombre' => 'Chun']);
        $fourth = Player::factory()->create(['nombre' => 'Ken']);
        $this->match('2025-07-04 10:00:00', $this->yulaw, $this->pan, 5, 4);
        $this->match('2025-07-04 11:00:00', $this->pan, $this->yulaw, 5, 4);
        $this->match('2025-07-04 12:00:00', $this->yulaw, $third, 5, 4);
        $this->match('2025-07-04 13:00:00', $this->pan, $fourth, 5, 4);
        $this->match('2025-07-04 14:00:00', $fourth, $this->pan, 5, 4);

        $ranking = $this->get(route('dashboard', ['report_fecha' => '2025-07-04']))->viewData('dailyRankings');

        $this->assertSame(['Yulaw', 'Pan'], $ranking->take(2)->pluck('nombre')->all());
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
