<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyRewardTest extends TestCase
{
    use RefreshDatabase;

    public function test_player_can_claim_one_daily_reward(): void
    {
        $user = User::factory()->create();
        $user->wallet()->create(['balance' => 1000]);

        $this->actingAs($user)
            ->post(route('daily-reward.store'))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('daily_rewards', [
            'user_id' => $user->id,
            'amount' => 250,
        ]);

        $this->assertDatabaseHas('ledger_entries', [
            'user_id' => $user->id,
            'type' => 'daily_reward',
            'amount' => 250,
            'balance_after' => 1250,
        ]);

        $this->assertSame(1250, $user->wallet()->value('balance'));

        $this->actingAs($user)
            ->post(route('daily-reward.store'))
            ->assertSessionHasErrors('daily_reward');

        $this->assertSame(1250, $user->wallet()->value('balance'));
    }
}
