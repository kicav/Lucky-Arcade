<?php

namespace Tests\Feature;

use App\Actions\Admin\GrantPromotionalCreditsAction;
use App\Models\LedgerEntry;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminPlayerActionsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function admin_grant_creates_ledger_notification_and_balance_change(): void
    {
        $this->seed();
        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $player = User::query()->where('email', 'demo@example.com')->firstOrFail();
        $before = $player->wallet->balance;

        app(GrantPromotionalCreditsAction::class)->execute(
            $admin,
            $player,
            500,
            'Test promotion',
            Request::create('/admin/users/'.$player->id.'/grant', 'POST'),
        );

        $this->assertSame($before + 500, $player->wallet()->firstOrFail()->balance);
        $this->assertDatabaseHas('ledger_entries', ['user_id' => $player->id, 'type' => 'admin_promotion', 'amount' => 500]);
        $this->assertDatabaseHas('user_notifications', ['user_id' => $player->id, 'type' => 'promotional_credit']);
    }
}
