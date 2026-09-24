<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Player extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['nombre'];

    public function victoriasComoGanador(): HasMany
    {
        return $this->hasMany(GameMatch::class, 'ganador_id');
    }

    public function derrotasComoPerdedor(): HasMany
    {
        return $this->hasMany(GameMatch::class, 'perdedor_id');
    }
}
