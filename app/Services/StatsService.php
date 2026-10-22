<?php

namespace App\Services;

use App\Models\GameMatch;
use App\Models\Player;
use App\Support\PlayerResult;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class StatsService
{
    /** @return list<string> */
    public function availableSeasons(string $currentSeason, string $selectedSeason): array
    {
        return GameMatch::query()
            ->whereNotNull('fecha')
            ->selectRaw('YEAR(fecha) as season')
            ->distinct()
            ->pluck('season')
            ->map(fn ($season) => (string) $season)
            ->push($currentSeason, $selectedSeason)
            ->unique()
            ->sortDesc()
            ->values()
            ->all();
    }

    /** @return Collection<int, array<string, mixed>> */
    public function seasonRanking(string $season): Collection
    {
        return $this->ranking($this->resultsForSeason($season))
            ->values()
            ->map(fn (array $row, int $index) => $row + ['ranking' => $index + 1]);
    }

    /** @return Collection<int, array<string, mixed>> */
    public function seasonStats(string $season): Collection
    {
        $results = $this->resultsForSeason($season);
        $lastOutcome = $this->latestOutcomeByPlayer($results);

        return $this->percentageTable($results)
            ->map(fn (array $row, int $index) => $row + [
                'ranking_porcentaje' => $index + 1,
                'tendencia' => ($lastOutcome[$row['id']] ?? false) ? 'up' : 'down',
            ]);
    }

    /** @return array{ranking: Collection<int, array<string, mixed>>, matches: Collection<int, GameMatch>} */
    public function dailyReport(string $date): array
    {
        $matches = $this->matchesOnDate($date);

        return [
            'ranking' => $this->ranking($this->toResults($matches), tieBreakByHands: true)->values(),
            'matches' => $matches,
        ];
    }

    /**
     * Evolucion del % de victorias acumulado de la temporada al cierre de cada
     * jornada (dia con matches) del jugador; solo las ultimas $days jornadas.
     *
     * @return array<int, list<array{fecha: string, porcentaje: float, total: int}>> keyed by player id
     */
    public function winPercentTrends(string $season, int $days = 5): array
    {
        $trends = [];

        $this->resultsForSeason($season)
            ->reverse()
            ->groupBy(fn (PlayerResult $r) => $r->player->id)
            ->each(function (Collection $rows, int $playerId) use (&$trends, $days) {
                $wins = 0;
                $total = 0;
                $byDay = [];

                foreach ($rows as $row) {
                    $total++;
                    $wins += $row->won ? 1 : 0;
                    $byDay[$row->match->fecha->format('Y-m-d')] = [
                        'fecha' => $row->match->fecha->format('Y-m-d'),
                        'porcentaje' => round($wins * 100 / $total, 2),
                        'total' => $total,
                    ];
                }

                $trends[$playerId] = array_slice(array_values($byDay), -$days);
            });

        return $trends;
    }

    /** @return array<string, mixed> */
    public function playerVersusSeason(int $playerId, string $season): array
    {
        $results = $this->resultsForSeason($season);

        $position = $this->percentageTable($results)
            ->search(fn (array $row) => $row['id'] === $playerId);

        $data = $results
            ->filter(fn (PlayerResult $r) => $r->player->id === $playerId)
            ->groupBy(fn (PlayerResult $r) => $r->rival->id)
            ->map(function (Collection $rows) {
                /** @var PlayerResult $latest */
                $latest = $rows->first();
                $wins = $rows->where('won', true)->count();
                $total = $rows->count();

                return [
                    'player' => $latest->player->nombre,
                    'vs_player_id' => $latest->rival->id,
                    'vs_player' => $latest->rival->nombre,
                    'total_partidas' => $total,
                    'porcentaje_victorias' => round($wins / $total * 100, 2),
                    'porcentaje_derrotas' => round(($total - $wins) / $total * 100, 2),
                    'promedio_victorias' => $wins,
                    'promedio_derrotas' => $total - $wins,
                    'tendencia_porcentaje' => $latest->won ? 'up' : 'down',
                ];
            })
            ->sortBy([['total_partidas', 'desc'], ['vs_player', 'asc']])
            ->values();

        $totalMatch = $data->sum('total_partidas');
        $totalWins = $data->sum('promedio_victorias');
        $totalLosses = $data->sum('promedio_derrotas');

        return [
            'data' => $data->all(),
            'totales' => [
                'totalMatch' => $totalMatch,
                'totalVictorias' => $totalWins,
                'totalDerrotas' => $totalLosses,
                'porcentajeVictoria' => $totalMatch > 0 ? (int) round($totalWins / $totalMatch * 100) : 0,
                'porcentajeDerrota' => $totalMatch > 0 ? (int) round($totalLosses / $totalMatch * 100) : 0,
            ],
            'posicion' => $position === false ? '-' : $position + 1,
        ];
    }

    /** @return array<string, mixed> */
    public function versusOnDate(int $playerOne, int $playerTwo, string $date): array
    {
        $results = $this->toResults($this->matchesOnDate($date));
        $names = Player::whereIn('id', [$playerOne, $playerTwo])->pluck('nombre', 'id');
        $ranking = $this->ranking($results, tieBreakByHands: true);

        $stats = fn (int $id) => [
            'nombre' => $names[$id] ?? '',
            'victorias' => $ranking[$id]['victorias'] ?? 0,
            'derrotas' => $ranking[$id]['derrotas'] ?? 0,
            'puntos' => $ranking[$id]['puntos'] ?? 0,
            'manos_favor' => $ranking[$id]['manos_favor'] ?? 0,
            'manos_contra' => $ranking[$id]['manos_contra'] ?? 0,
        ];

        $timeline = fn (int $id) => $results
            ->filter(fn (PlayerResult $r) => $r->player->id === $id)
            ->map(fn (PlayerResult $r) => [
                'fecha' => $r->match->fecha->format('Y-m-d H:i:s'),
                'rival' => $r->rival->nombre,
                'vd' => $r->won ? 'V' : 'D',
                'manos_favor' => $r->favor,
                'manos_contra' => $r->contra,
            ])
            ->values()
            ->all();

        $pair = [$playerOne, $playerTwo];

        $headToHead = $results
            ->filter(fn (PlayerResult $r) => $r->won
                && in_array($r->player->id, $pair, true)
                && in_array($r->rival->id, $pair, true))
            ->map(fn (PlayerResult $r) => [
                'fecha' => $r->match->fecha->format('Y-m-d H:i:s'),
                'ganador' => $r->player->nombre,
                'perdedor' => $r->rival->nombre,
                'victorias_ganador' => $r->favor,
                'victorias_perdedor' => $r->contra,
            ])
            ->values()
            ->all();

        return [
            'stats1' => $stats($playerOne),
            'stats2' => $stats($playerTwo),
            'h2h' => $headToHead,
            'matches1' => $timeline($playerOne),
            'matches2' => $timeline($playerTwo),
        ];
    }

    /** @return Collection<int, GameMatch> */
    private function matchesOnDate(string $date): Collection
    {
        return $this->withPlayers(GameMatch::query()->onDate($date))
            ->with(['ganadorImage', 'perdedorImage'])
            ->get();
    }

    /** @return Collection<int, PlayerResult> */
    private function resultsForSeason(string $season): Collection
    {
        return $this->toResults($this->withPlayers(GameMatch::query()->inSeason($season))->get());
    }

    private function withPlayers(Builder $query): Builder
    {
        return $query
            ->with(['ganador:id,nombre', 'perdedor:id,nombre'])
            ->whereNotNull('ganador_id')
            ->whereNotNull('perdedor_id')
            ->orderByDesc('id');
    }

    /**
     * @param  Collection<int, GameMatch>  $matches
     * @return Collection<int, PlayerResult>
     */
    private function toResults(Collection $matches): Collection
    {
        return $matches->flatMap(fn (GameMatch $m) => [
            new PlayerResult($m->ganador, $m->perdedor, true, $m->victorias_ganador, $m->victorias_perdedor, $m),
            new PlayerResult($m->perdedor, $m->ganador, false, $m->victorias_perdedor, $m->victorias_ganador, $m),
        ]);
    }

    /**
     * @param  Collection<int, PlayerResult>  $results
     * @return Collection<int, array<string, mixed>> keyed by player id
     */
    private function ranking(Collection $results, bool $tieBreakByHands = false): Collection
    {
        $order = $tieBreakByHands
            ? [['puntos', 'desc'], ['victorias', 'desc'], ['manos_favor', 'desc'], ['nombre', 'asc']]
            : [['puntos', 'desc'], ['victorias', 'desc'], ['nombre', 'asc']];

        return $results
            ->groupBy(fn (PlayerResult $r) => $r->player->id)
            ->map(fn (Collection $rows) => [
                'id' => $rows->first()->player->id,
                'nombre' => $rows->first()->player->nombre,
                'victorias' => $rows->where('won', true)->count(),
                'derrotas' => $rows->where('won', false)->count(),
                'puntos' => $rows->sum(fn (PlayerResult $r) => $r->points()),
                'manos_favor' => $rows->sum('favor'),
                'manos_contra' => $rows->sum('contra'),
            ])
            ->sortBy($order)
            ->keyBy('id');
    }

    /**
     * @param  Collection<int, PlayerResult>  $results
     * @return Collection<int, array<string, mixed>>
     */
    private function percentageTable(Collection $results): Collection
    {
        return $results
            ->groupBy(fn (PlayerResult $r) => $r->player->id)
            ->map(function (Collection $rows) {
                $total = $rows->count();
                $wins = $rows->where('won', true)->count();

                return [
                    'id' => $rows->first()->player->id,
                    'nombre' => $rows->first()->player->nombre,
                    'total_match' => $total,
                    'total_victorias' => $wins,
                    'total_derrotas' => $total - $wins,
                    'porcentaje_victoria' => round($wins * 100 / $total, 2),
                    'porcentaje_derrota' => round(($total - $wins) * 100 / $total, 2),
                ];
            })
            ->sortBy([['porcentaje_victoria', 'desc'], ['total_victorias', 'desc'], ['nombre', 'asc']])
            ->values();
    }

    /**
     * @param  Collection<int, PlayerResult>  $results  ordered newest first
     * @return array<int, bool> whether each player won their most recent match
     */
    private function latestOutcomeByPlayer(Collection $results): array
    {
        $latest = [];

        foreach ($results as $result) {
            $latest[$result->player->id] ??= $result->won;
        }

        return $latest;
    }
}
