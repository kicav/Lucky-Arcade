<?php

namespace App\Contracts;

use App\DTO\GameOutcome;

interface GameEngine
{
    public function code(): string;

    /**
     * @param array<string, mixed> $bet
     */
    public function play(
        int $stake,
        array $bet,
        string $serverSeed,
        string $clientSeed,
        int $nonce,
    ): GameOutcome;
}
