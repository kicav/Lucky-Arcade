<?php

namespace Tests\Feature;

use App\Actions\Games\PlaceBetAction;
use App\Models\Game;
use App\Models\User;
use App\Services\FairnessSeedService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PlayerControlsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function daily_stake_limit_blocks_excess_play(): void
    {
        $this->seed();
        $user = User::query()->where('email', 'demo@example.com')->firstOrFail();
        $user->update(['daily_stake_limit' => 10]);
        $game = Game::query()->where('code', 'dice')->firstOrFail();

        app(PlaceBetAction::class)->execute($user, $game, 10, ['direction' => 'under', 'target' => 50], (string) Str::uuid());

        $this->expectException(ValidationException::class);
        app(PlaceBetAction::class)->execute($user, $game, 10, ['direction' => 'under', 'target' => 50], (string) Str::uuid());
    }

    #[Test]
    public function self_exclusion_blocks_play(): void
    {
        $this->seed();
        $user = User::query()->where('email', 'demo@example.com')->firstOrFail();
        $user->update(['self_excluded_until' => now()->addDay()]);
        $game = Game::query()->where('code', 'dice')->firstOrFail();

        $this->expectException(ValidationException::class);
        app(PlaceBetAction::class)->execute($user, $game, 10, ['direction' => 'under', 'target' => 50], (string) Str::uuid());
    }
}
