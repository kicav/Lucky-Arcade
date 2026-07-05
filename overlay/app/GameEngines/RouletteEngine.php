<?php

namespace App\GameEngines;

use App\Contracts\GameEngine;
use App\DTO\GameOutcome;
use App\Services\ProvablyFairService;
use Illuminate\Validation\ValidationException;

final class RouletteEngine implements GameEngine
{
    /** @var int[] */
    private const RED = [1,3,5,7,9,12,14,16,18,19,21,23,25,27,30,32,34,36];

    public function __construct(private readonly ProvablyFairService $fairness)
    {
    }

    public function code(): string
    {
        return 'roulette';
    }

    public function play(
        int $stake,
        array $bet,
        string $serverSeed,
        string $clientSeed,
        int $nonce,
    ): GameOutcome {
        $type = (string) ($bet['type'] ?? '');
        $selection = (string) ($bet['selection'] ?? '');
        $number = $this->fairness->uniformInt(
            $serverSeed,
            $clientSeed.':roulette',
            $nonce,
            0,
            36,
        );

        [$won, $multiplier] = $this->evaluate($type, $selection, $number);
        $color = $number === 0 ? 'green' : (in_array($number, self::RED, true) ? 'red' : 'black');

        return new GameOutcome(
            won: $won,
            payout: $won ? $stake * $multiplier : 0,
            result: [
                'number' => $number,
                'color' => $color,
                'bet_type' => $type,
                'selection' => $selection,
                'multiplier' => $multiplier,
            ],
        );
    }

    /** @return array{bool, int} */
    private function evaluate(string $type, string $selection, int $number): array
    {
        return match ($type) {
            'straight' => $this->straight($selection, $number),
            'color' => $this->color($selection, $number),
            'parity' => $this->parity($selection, $number),
            'range' => $this->range($selection, $number),
            'dozen' => $this->dozen($selection, $number),
            default => throw ValidationException::withMessages([
                'bet_type' => 'Unsupported roulette bet type.',
            ]),
        };
    }

    /** @return array{bool, int} */
    private function straight(string $selection, int $number): array
    {
        if (! ctype_digit($selection) || (int) $selection < 0 || (int) $selection > 36) {
            throw ValidationException::withMessages(['selection' => 'Choose a number from 0 to 36.']);
        }

        return [(int) $selection === $number, 36];
    }

    /** @return array{bool, int} */
    private function color(string $selection, int $number): array
    {
        if (! in_array($selection, ['red', 'black'], true)) {
            throw ValidationException::withMessages(['selection' => 'Choose red or black.']);
        }

        if ($number === 0) {
            return [false, 2];
        }

        $actual = in_array($number, self::RED, true) ? 'red' : 'black';

        return [$selection === $actual, 2];
    }

    /** @return array{bool, int} */
    private function parity(string $selection, int $number): array
    {
        if (! in_array($selection, ['odd', 'even'], true)) {
            throw ValidationException::withMessages(['selection' => 'Choose odd or even.']);
        }

        if ($number === 0) {
            return [false, 2];
        }

        $actual = $number % 2 === 0 ? 'even' : 'odd';

        return [$selection === $actual, 2];
    }

    /** @return array{bool, int} */
    private function range(string $selection, int $number): array
    {
        if (! in_array($selection, ['low', 'high'], true)) {
            throw ValidationException::withMessages(['selection' => 'Choose low or high.']);
        }

        if ($number === 0) {
            return [false, 2];
        }

        $actual = $number <= 18 ? 'low' : 'high';

        return [$selection === $actual, 2];
    }

    /** @return array{bool, int} */
    private function dozen(string $selection, int $number): array
    {
        if (! in_array($selection, ['1', '2', '3'], true)) {
            throw ValidationException::withMessages(['selection' => 'Choose dozen 1, 2 or 3.']);
        }

        if ($number === 0) {
            return [false, 3];
        }

        $actual = (string) (int) ceil($number / 12);

        return [$selection === $actual, 3];
    }
}
