<?php

namespace App\Services;

use App\Models\GameMatch;
use App\Models\Player;
use Illuminate\Support\Facades\DB;

class MatchService
{
    public function create(array $data): GameMatch
    {
        return DB::transaction(function () use ($data) {
            $match = GameMatch::create($data);
            $this->applyStats($match);

            return $match;
        });
    }

    public function update(GameMatch $match, array $data): GameMatch
    {
        return DB::transaction(function () use ($match, $data) {
            $match = GameMatch::whereKey($match->getKey())->lockForUpdate()->firstOrFail();

            $this->revertStats($match);
            $match->update($data);
            $this->applyStats($match);

            return $match;
        });
    }

    public function delete(GameMatch $match): void
    {
        DB::transaction(function () use ($match) {
            $match = GameMatch::whereKey($match->getKey())->lockForUpdate()->firstOrFail();

            $this->revertStats($match);
            $match->delete();
        });
    }

    public function recalculateAllPlayerTotals(): void
    {
        Player::query()->update(['victorias' => 0, 'derrotas' => 0, 'puntos' => 0]);

        foreach (GameMatch::query()->cursor() as $match) {
            $this->applyStats($match);
        }
    }

    private function applyStats(GameMatch $match): void
    {
        $diff = $match->victorias_ganador - $match->victorias_perdedor;

        Player::whereKey($match->ganador_id)->update([
            'victorias' => DB::raw('victorias + 1'),
            'puntos' => DB::raw('puntos + '.$diff),
        ]);

        Player::whereKey($match->perdedor_id)->update([
            'derrotas' => DB::raw('derrotas + 1'),
            'puntos' => DB::raw('puntos - '.$diff),
        ]);
    }

    private function revertStats(GameMatch $match): void
    {
        $diff = $match->victorias_ganador - $match->victorias_perdedor;

        Player::whereKey($match->ganador_id)->update([
            'victorias' => DB::raw('GREATEST(victorias - 1, 0)'),
            'puntos' => DB::raw('puntos - '.$diff),
        ]);

        Player::whereKey($match->perdedor_id)->update([
            'derrotas' => DB::raw('GREATEST(derrotas - 1, 0)'),
            'puntos' => DB::raw('puntos + '.$diff),
        ]);
    }
}
