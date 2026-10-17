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
        $match = $this->matches->create($data + ['user_id' => $request->user()->id]);
        $this->matches->notifyRegistered($match);

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

    public function destroy(GameMatch $match): RedirectResponse
    {
        $season = $match->fecha->year;
        $this->matches->delete($match);

        return $this->backToSeason("{$season}-01-01")
            ->with('success', 'Match eliminado y estadisticas actualizadas.');
    }

    private function backToSeason(string $fecha): RedirectResponse
    {
        return redirect()->route('dashboard', ['season' => Carbon::parse($fecha)->year]);
    }
}
