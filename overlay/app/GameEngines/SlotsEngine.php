<?php

namespace App\GameEngines;

use App\Contracts\GameEngine;
use App\DTO\GameOutcome;
use App\Services\ProvablyFairService;

final class SlotsEngine implements GameEngine
{
    /** @var array<int, string> */
    private const SYMBOLS = [
        'cherry', 'cherry', 'cherry', 'cherry', 'cherry', 'cherry', 'cherry',
        'lemon', 'lemon', 'lemon', 'lemon', 'lemon',
        'bell', 'bell', 'bell', 'bell',
        'star', 'star', 'star',
        'seven',
    ];

    public function __construct(private readonly ProvablyFairService $fairness)
    {
    }

    public function code(): string
    {
        return 'slots';
    }

    public function play(
        int $stake,
        array $bet,
        string $serverSeed,
        string $clientSeed,
        int $nonce,
    ): GameOutcome {
        $symbols = [];

        for ($reel = 0; $reel < 3; $reel++) {
            $index = $this->fairness->uniformInt(
                $serverSeed,
                $clientSeed.':slots:'.$reel,
                $nonce,
                0,
                count(self::SYMBOLS) - 1,
            );
            $symbols[] = self::SYMBOLS[$index];
        }

        $multiplierTenths = $this->multiplierTenths($symbols);
        $payout = intdiv($stake * $multiplierTenths, 10);

        return new GameOutcome(
            won: $payout > 0,
            payout: $payout,
            result: [
                'symbols' => $symbols,
                'multiplier' => $multiplierTenths / 10,
                'match' => $this->matchLabel($symbols),
            ],
        );
    }

    /** @param array<int, string> $symbols */
    private function multiplierTenths(array $symbols): int
    {
        if (count(array_unique($symbols)) === 1) {
            return match ($symbols[0]) {
                'seven' => 250,
                'star' => 120,
                'bell' => 80,
                'cherry' => 50,
                'lemon' => 40,
                default => 0,
            };
        }

        return count(array_unique($symbols)) === 2 ? 15 : 0;
    }

    /** @param array<int, string> $symbols */
    private function matchLabel(array $symbols): string
    {
        if (count(array_unique($symbols)) === 1) {
            return 'triple';
        }

        if (count(array_unique($symbols)) === 2) {
            return 'pair';
        }

        return 'none';
    }
}
