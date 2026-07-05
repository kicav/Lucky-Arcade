<?php

namespace Tests\Feature;

use App\Actions\Rewards\SettleWeeklyLeagueAction;
use App\Models\Game;
use App\Models\GameEntry;
use App\Models\User;
use App\Services\FairnessSeedService;
use App\Services\WeeklyLeagueService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WeeklyLeagueTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function admin_settles_previous_week_only_once(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $admin->wallet()->create(['balance' => 0]);
        $players = User::factory()->count(3)->create();
        foreach ($players as $player) {
            $player->wallet()->create(['balance' => 100]);
            app(FairnessSeedService::class)->create($player);
        }
        $game = Game::query()->create(['code' => 'dice', 'name' => 'Dice', 'description' => 'Test', 'enabled' => true, 'min_bet' => 1, 'max_bet' => 1000, 'config' => []]);
        $date = app(WeeklyLeagueService::class)->previousWeekStart()->addDay();

        foreach ($players as $index => $player) {
            for ($i = 0; $i < 3 - $index; $i++) {
                GameEntry::query()->forceCreate([
                    'user_id' => $player->id, 'game_id' => $game->id,
                    'fairness_seed_id' => $player->fairnessSeeds()->where('active', true)->value('id'),
                    'stake' => 100, 'payout' => 200, 'net' => 100, 'bet' => [], 'result' => ['won' => true],
                    'client_seed' => 'client', 'nonce' => $i, 'server_seed_hash' => str_repeat('a', 64),
                    'request_id' => (string) Str::uuid(), 'status' => 'settled', 'created_at' => $date, 'updated_at' => $date,
                ]);
            }
        }

        $request = Request::create('/admin/league/settle', 'POST');
        app(SettleWeeklyLeagueAction::class)->execute($admin, $request);
        app(SettleWeeklyLeagueAction::class)->execute($admin, $request);

        $this->assertDatabaseCount('weekly_league_rewards', 3);
        $this->assertDatabaseCount('weekly_league_settlements', 1);
        $this->assertSame(1, $players[0]->ledgerEntries()->where('type', 'weekly_league_reward')->count());
        $this->assertSame(1100, $players[0]->wallet()->firstOrFail()->balance);
    }
}
