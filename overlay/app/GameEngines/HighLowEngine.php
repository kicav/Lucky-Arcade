<?php

namespace App\GameEngines;

use App\Contracts\GameEngine;
use App\DTO\GameOutcome;
use App\Services\ProvablyFairService;
use Illuminate\Validation\ValidationException;

final class HighLowEngine implements GameEngine
{
    private const MULTIPLIER = 1.98;

    /** @var array<int, string> */
    private const SUITS = ['clubs', 'diamonds', 'hearts', 'spades'];

    public function __construct(private readonly ProvablyFairService $fairness)
    {
    }

    public function code(): string
    {
        return 'highlow';
    }

    public function play(
        int $stake,
        array $bet,
        string $serverSeed,
        string $clientSeed,
        int $nonce,
    ): GameOutcome {
        $selection = (string) ($bet['selection'] ?? '');

        if (! in_array($selection, ['higher', 'lower'], true)) {
            throw ValidationException::withMessages([
                'selection' => 'Choose higher or lower.',
            ]);
        }

        $rank = $this->fairness->uniformInt(
            $serverSeed,
            $clientSeed.':highlow:rank',
            $nonce,
            1,
            13,
        );
        $suitIndex = $this->fairness->uniformInt(
            $serverSeed,
            $clientSeed.':highlow:suit',
            $nonce,
            0,
            3,
        );

        $push = $rank === 7;
        $won = ! $push && (
            ($selection === 'higher' && $rank > 7)
            || ($selection === 'lower' && $rank < 7)
        );

        $payout = $push
            ? $stake
            : ($won ? (int) floor($stake * self::MULTIPLIER) : 0);

        return new GameOutcome(
            won: $won,
            payout: $payout,
            result: [
                'rank' => $rank,
                'rank_label' => $this->rankLabel($rank),
                'suit' => self::SUITS[$suitIndex],
                'selection' => $selection,
                'push' => $push,
                'multiplier' => self::MULTIPLIER,
                'reference_rank' => 7,
            ],
        );
    }

    private function rankLabel(int $rank): string
    {
        return match ($rank) {
            1 => 'A',
            11 => 'J',
            12 => 'Q',
            13 => 'K',
            default => (string) $rank,
        };
    }
}
