<?php

namespace App\Services;

use App\Contracts\GameEngine;
use App\GameEngines\CoinFlipEngine;
use App\GameEngines\DiceEngine;
use App\GameEngines\RouletteEngine;
use InvalidArgumentException;

final class GameEngineRegistry
{
    public function __construct(
        private readonly DiceEngine $dice,
        private readonly RouletteEngine $roulette,
        private readonly CoinFlipEngine $coinFlip,
    ) {
    }

    public function for(string $code): GameEngine
    {
        return match ($code) {
            'dice' => $this->dice,
            'roulette' => $this->roulette,
            'coinflip' => $this->coinFlip,
            default => throw new InvalidArgumentException("Unsupported game: {$code}"),
        };
    }
}
