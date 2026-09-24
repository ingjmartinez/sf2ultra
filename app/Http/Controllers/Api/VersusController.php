<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StatsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VersusController extends Controller
{
    public function __construct(private readonly StatsService $stats) {}

    public function __invoke(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'fecha' => ['required', 'date_format:Y-m-d'],
            'player1' => ['required', 'integer', 'min:1', 'exists:players,id'],
            'player2' => ['required', 'integer', 'min:1', 'different:player1', 'exists:players,id'],
        ], [
            'fecha.required' => 'Selecciona una fecha.',
            'fecha.date_format' => 'Fecha invalida.',
            'player1.required' => 'Selecciona ambos players.',
            'player2.required' => 'Selecciona ambos players.',
            'player1.exists' => 'El player 1 no existe.',
            'player2.exists' => 'El player 2 no existe.',
            'player2.different' => 'Selecciona dos players distintos.',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $data = $validator->validated();

        return response()->json($this->stats->versusOnDate((int) $data['player1'], (int) $data['player2'], $data['fecha']));
    }
}
