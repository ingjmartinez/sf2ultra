<?php

namespace App\Services;

use App\Mail\MatchRegistered;
use App\Models\GameMatch;
use App\Models\Player;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;

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

    /** Avisa por correo a los jugadores del match (si tienen email) y a los usuarios admin. */
    public function notifyRegistered(GameMatch $match): void
    {
        $match->loadMissing(['ganador', 'perdedor']);

        $recipients = collect([$match->ganador?->email, $match->perdedor?->email])
            ->merge(User::pluck('email'))
            ->filter()
            ->map(fn (string $email) => strtolower($email))
            ->unique();

        foreach ($recipients as $email) {
            try {
                Mail::to($email)->send(new MatchRegistered($match));
            } catch (Throwable $e) {
                report($e);
            }
        }
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
