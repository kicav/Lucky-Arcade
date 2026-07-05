<?php

namespace Tests\Unit;

use App\GameEngines\HighLowEngine;
use App\Services\ProvablyFairService;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HighLowEngineTest extends TestCase
{
    #[Test]
    public function it_is_deterministic_and_returns_a_valid_card(): void
    {
        $engine = new HighLowEngine(new ProvablyFairService());
        $first = $engine->play(100, ['selection' => 'higher'], 'server', 'client', 9);
        $second = $engine->play(100, ['selection' => 'higher'], 'server', 'client', 9);

        $this->assertSame($first->result, $second->result);
        $this->assertSame($first->payout, $second->payout);
        $this->assertContains($first->result['rank'], range(1, 13));
        $this->assertContains($first->result['suit'], ['clubs', 'diamonds', 'hearts', 'spades']);
        $this->assertContains($first->payout, [0, 100, 198]);
    }

    #[Test]
    public function it_rejects_an_invalid_prediction(): void
    {
        $this->expectException(ValidationException::class);
        (new HighLowEngine(new ProvablyFairService()))
            ->play(100, ['selection' => 'sideways'], 'server', 'client', 0);
    }
}
