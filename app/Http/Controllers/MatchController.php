<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveMatchRequest;
use App\Models\GameMatch;
use App\Services\MatchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;

class MatchController extends Controller
{
    public function __construct(private readonly MatchService $matches) {}

    public function store(SaveMatchRequest $request): RedirectResponse
    {
        $data = $request->matchData();
        $this->matches->create($data);

        return $this->backToSeason($data['fecha'])
            ->with('success', 'Match registrado y estadisticas actualizadas.');
    }

    public function update(SaveMatchRequest $request, GameMatch $match): RedirectResponse
    {
        $data = $request->matchData();
        $this->matches->update($match, $data);

        return $this->backToSeason($data['fecha'])
            ->with('success', 'Match actualizado correctamente.');
    }

    private function backToSeason(string $fecha): RedirectResponse
    {
        return redirect()->route('dashboard', ['season' => Carbon::parse($fecha)->year]);
    }
}
