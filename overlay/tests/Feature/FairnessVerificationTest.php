<?php

namespace Tests\Feature;

use App\Actions\Games\PlaceBetAction;
use App\Models\Game;
use App\Models\User;
use App\Services\FairnessSeedService;
use App\Services\FairnessVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class FairnessVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_revealed_dice_result_can_be_recomputed(): void
    {
        $user = User::factory()->create();
        $user->wallet()->create(['balance' => 1000]);

        $game = Game::query()->create([
            'code' => 'dice',
            'name' => 'Dice',
            'description' => 'Test dice',
            'enabled' => true,
            'min_bet' => 10,
            'max_bet' => 1000,
            'config' => ['house_edge' => 0.01],
        ]);

        app(FairnessSeedService::class)->create($user);

        $entry = app(PlaceBetAction::class)->execute(
            user: $user,
            game: $game,
            stake: 10,
            bet: ['direction' => 'under', 'target' => 50],
            requestId: (string) Str::uuid(),
        );

        app(FairnessSeedService::class)->rotate($user, 'verification-client-seed');

        $result = app(FairnessVerificationService::class)->verify(
            $entry->fresh(['game', 'fairnessSeed']),
        );

        $this->assertTrue($result['verified']);
        $this->assertTrue($result['hash_matches']);
        $this->assertTrue($result['result_matches']);
        $this->assertTrue($result['payout_matches']);
    }
}
