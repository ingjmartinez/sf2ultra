<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StatsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PlayerStatsController extends Controller
{
    public function __construct(private readonly StatsService $stats) {}

    public function __invoke(Request $request): JsonResponse
    {
        $season = $request->query('season');
        $season = is_string($season) && Validator::make(['s' => $season], ['s' => 'digits:4'])->passes()
            ? $season
            : now()->format('Y');

        $playerId = (int) $request->query('player');

        if ($playerId <= 0) {
            return response()->json([
                'data' => [],
                'totales' => [
                    'totalMatch' => 0,
                    'totalVictorias' => 0,
                    'totalDerrotas' => 0,
                    'porcentajeVictoria' => 0,
                    'porcentajeDerrota' => 0,
                ],
                'posicion' => '-',
            ]);
        }

        return response()->json($this->stats->playerVersusSeason($playerId, $season));
    }
}
