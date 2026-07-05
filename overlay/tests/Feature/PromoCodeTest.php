<?php

namespace Tests\Feature;

use App\Actions\Rewards\RedeemPromoCodeAction;
use App\Models\PromoCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PromoCodeTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function player_redeems_a_code_once_with_ledger_and_notification(): void
    {
        $user = User::factory()->create();
        $user->wallet()->create(['balance' => 100]);
        $promo = PromoCode::query()->create([
            'code' => 'WELCOME250', 'title' => 'Welcome', 'credits' => 250,
            'active' => true, 'max_redemptions' => 10,
        ]);

        app(RedeemPromoCodeAction::class)->execute($user, 'welcome250');

        $this->assertSame(350, $user->wallet()->firstOrFail()->balance);
        $this->assertDatabaseHas('ledger_entries', ['user_id' => $user->id, 'type' => 'promo_code_reward', 'amount' => 250]);
        $this->assertDatabaseHas('user_notifications', ['user_id' => $user->id, 'type' => 'promo_code']);
        $this->assertSame(1, $promo->fresh()->redemptions_count);

        $this->expectException(ValidationException::class);
        app(RedeemPromoCodeAction::class)->execute($user, 'WELCOME250');
    }
}
