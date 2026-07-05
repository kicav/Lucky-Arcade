<?php

namespace Tests\Feature;

use App\Actions\Games\PlaceBetAction;
use App\Models\Game;
use App\Models\User;
use App\Services\FairnessSeedService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AchievementTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function first_play_unlocks_once_and_creates_a_ledger_reward(): void
    {
        $user = User::factory()->create();
        $user->wallet()->create(['balance' => 1000]);
        app(FairnessSeedService::class)->create($user);
        $game = Game::query()->create([
            'code' => 'coinflip', 'name' => 'Coin Flip', 'description' => 'Test',
            'enabled' => true, 'min_bet' => 10, 'max_bet' => 1000,
            'config' => ['multiplier' => 1.98],
        ]);

        app(PlaceBetAction::class)->execute($user, $game, 10, ['selection' => 'heads'], (string) Str::uuid());

        $this->assertDatabaseHas('user_achievements', ['user_id' => $user->id, 'code' => 'first_play', 'reward' => 100]);
        $this->assertDatabaseHas('ledger_entries', ['user_id' => $user->id, 'type' => 'achievement_reward', 'amount' => 100]);

        app(PlaceBetAction::class)->execute($user, $game, 10, ['selection' => 'tails'], (string) Str::uuid());

        $this->assertSame(1, $user->achievements()->where('code', 'first_play')->count());
    }
}
