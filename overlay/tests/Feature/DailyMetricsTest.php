<?php

namespace Tests\Feature;

use App\Models\DailyGameMetric;
use App\Models\Game;
use App\Models\GameEntry;
use App\Models\User;
use App\Services\DailyMetricsService;
use App\Services\FairnessSeedService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DailyMetricsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function daily_metrics_are_rebuilt_idempotently(): void
    {
        $user = User::factory()->create();
        $user->wallet()->create(['balance' => 1000]);
        $seed = app(FairnessSeedService::class)->create($user);
        $game = Game::query()->create([
            'code' => 'metrics-test',
            'name' => 'Metrics Test',
            'description' => 'Metrics test game',
            'enabled' => true,
            'min_bet' => 1,
            'max_bet' => 1000,
            'config' => [],
        ]);

        $date = now()->startOfDay()->addHours(12);
        foreach ([[100, 180], [50, 0], [25, 25]] as $nonce => [$stake, $payout]) {
            GameEntry::query()->forceCreate([
                'user_id' => $user->id,
                'game_id' => $game->id,
                'fairness_seed_id' => $seed->id,
                'stake' => $stake,
                'payout' => $payout,
                'net' => $payout - $stake,
                'bet' => [],
                'result' => ['won' => $payout > $stake],
                'client_seed' => 'metrics-client',
                'nonce' => $nonce,
                'server_seed_hash' => str_repeat('a', 64),
                'request_id' => (string) Str::uuid(),
                'status' => 'settled',
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }

        $service = app(DailyMetricsService::class);
        $service->refreshDate($date);
        $service->refreshDate($date);

        $this->assertDatabaseCount('daily_game_metrics', 1);
        $metric = DailyGameMetric::query()->firstOrFail();
        $this->assertSame(3, $metric->plays);
        $this->assertSame(1, $metric->wins);
        $this->assertSame(175, $metric->total_stake);
        $this->assertSame(205, $metric->total_payout);
        $this->assertSame(-30, $metric->system_net);
    }
}
