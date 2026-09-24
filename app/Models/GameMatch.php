<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameMatch extends Model
{
    use HasFactory;

    protected $table = 'matches';

    public $timestamps = false;

    protected $fillable = [
        'fecha',
        'ganador_id',
        'perdedor_id',
        'victorias_ganador',
        'victorias_perdedor',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'datetime',
            'victorias_ganador' => 'integer',
            'victorias_perdedor' => 'integer',
        ];
    }

    public function ganador(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'ganador_id');
    }

    public function perdedor(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'perdedor_id');
    }

    public function scopeInSeason(Builder $query, int|string $season): Builder
    {
        return $query->whereBetween('fecha', ["{$season}-01-01 00:00:00", "{$season}-12-31 23:59:59"]);
    }

    public function scopeOnDate(Builder $query, string $date): Builder
    {
        return $query->whereBetween('fecha', ["{$date} 00:00:00", "{$date} 23:59:59"]);
    }

    public function scopeBetweenPlayers(Builder $query, int $a, int $b): Builder
    {
        return $query->where(function (Builder $q) use ($a, $b) {
            $q->where(fn (Builder $x) => $x->where('ganador_id', $a)->where('perdedor_id', $b))
                ->orWhere(fn (Builder $x) => $x->where('ganador_id', $b)->where('perdedor_id', $a));
        });
    }
}
