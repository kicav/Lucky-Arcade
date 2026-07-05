<?php

namespace Tests\Feature;

use App\Actions\Games\PlaceBetAction;
use App\Actions\Rewards\ClaimMissionRewardAction;
use App\Models\Game;
use App\Models\User;
use App\Services\FairnessSeedService;
use App\Services\MissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MissionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function play_progresses_and_claims_a_daily_mission_once(): void
    {
        $user = User::factory()->create();
        $user->wallet()->create(['balance' => 1000]);
        app(FairnessSeedService::class)->create($user);
        $game = Game::query()->create([
            'code' => 'coinflip', 'name' => 'Coin Flip', 'description' => 'Test',
            'enabled' => true, 'min_bet' => 10, 'max_bet' => 1000, 'config' => [],
        ]);

        for ($i = 0; $i < 3; $i++) {
            app(PlaceBetAction::class)->execute($user, $game, 10, ['selection' => 'heads'], (string) Str::uuid());
        }

        $mission = app(MissionService::class)->sync($user)->firstWhere('mission_key', 'play_3');
        $this->assertNotNull($mission?->completed_at);

        app(ClaimMissionRewardAction::class)->execute($user, $mission);
        app(ClaimMissionRewardAction::class)->execute($user, $mission);

        $this->assertDatabaseHas('ledger_entries', ['user_id' => $user->id, 'type' => 'mission_reward', 'amount' => 100]);
        $this->assertSame(1, $user->ledgerEntries()->where('type', 'mission_reward')->count());
    }
}
