<?php

namespace App\Services;

use App\Contracts\GameEngine;
use App\GameEngines\CoinFlipEngine;
use App\GameEngines\DiceEngine;
use App\GameEngines\HighLowEngine;
use App\GameEngines\RouletteEngine;
use App\GameEngines\SlotsEngine;
use InvalidArgumentException;

final class GameEngineRegistry
{
    public function __construct(
        private readonly DiceEngine $dice,
        private readonly RouletteEngine $roulette,
        private readonly CoinFlipEngine $coinFlip,
        private readonly HighLowEngine $highLow,
        private readonly SlotsEngine $slots,
    ) {
    }

    public function for(string $code): GameEngine
    {
        return match ($code) {
            'dice' => $this->dice,
            'roulette' => $this->roulette,
            'coinflip' => $this->coinFlip,
            'highlow' => $this->highLow,
            'slots' => $this->slots,
            default => throw new InvalidArgumentException("Unsupported game: {$code}"),
        };
    }
}
