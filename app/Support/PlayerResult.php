<?php

namespace App\Support;

use App\Models\GameMatch;
use App\Models\Player;

final readonly class PlayerResult
{
    public function __construct(
        public Player $player,
        public Player $rival,
        public bool $won,
        public int $favor,
        public int $contra,
        public GameMatch $match,
    ) {}

    public function points(): int
    {
        return $this->favor - $this->contra;
    }
}
