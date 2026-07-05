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

class ReferralRewardTest extends TestCase
{
    use RefreshDatabase;


    #[Test]
    public function registration_accepts_a_valid_player_referral_code(): void
    {
        $this->seed();
        $inviter = User::query()->where('email', 'demo@example.com')->firstOrFail();

        $this->post(route('register.store'), [
            'name' => 'Invited Player',
            'email' => 'invited@example.com',
            'password' => 'Referral123',
            'password_confirmation' => 'Referral123',
            'referral_code' => strtolower((string) $inviter->referral_code),
        ])->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', [
            'email' => 'invited@example.com',
            'referred_by_user_id' => $inviter->id,
        ]);
    }

    #[Test]
    public function both_players_are_rewarded_after_the_referred_players_first_game(): void
    {
        $this->seed();
        $inviter = User::query()->where('email', 'demo@example.com')->firstOrFail();
        $game = Game::query()->where('code', 'coinflip')->firstOrFail();

        $referred = User::factory()->create(['referred_by_user_id' => $inviter->id]);
        $referred->wallet()->create(['balance' => 1000]);
        app(FairnessSeedService::class)->create($referred);

        app(PlaceBetAction::class)->execute(
            $referred,
            $game,
            10,
            ['selection' => 'heads'],
            (string) Str::uuid(),
        );

        $this->assertDatabaseHas('referral_rewards', [
            'inviter_user_id' => $inviter->id,
            'referred_user_id' => $referred->id,
            'inviter_amount' => 500,
            'referred_amount' => 500,
        ]);
        $this->assertDatabaseHas('ledger_entries', ['user_id' => $inviter->id, 'type' => 'referral_bonus_inviter', 'amount' => 500]);
        $this->assertDatabaseHas('ledger_entries', ['user_id' => $referred->id, 'type' => 'referral_bonus_referred', 'amount' => 500]);
        $this->assertDatabaseHas('user_achievements', ['user_id' => $inviter->id, 'code' => 'first_referral']);

        app(PlaceBetAction::class)->execute(
            $referred,
            $game,
            10,
            ['selection' => 'tails'],
            (string) Str::uuid(),
        );

        $this->assertDatabaseCount('referral_rewards', 1);
    }
}
