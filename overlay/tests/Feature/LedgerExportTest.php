<?php

namespace Tests\Feature;

use App\Enums\LedgerDirection;
use App\Models\LedgerEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LedgerExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_player_can_export_own_ledger_as_csv(): void
    {
        $user = User::factory()->create();
        $wallet = $user->wallet()->create(['balance' => 1250]);

        LedgerEntry::query()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'direction' => LedgerDirection::Credit,
            'amount' => 250,
            'balance_after' => 1250,
            'type' => 'daily_reward',
            'idempotency_key' => 'test-ledger-export',
        ]);

        $response = $this->actingAs($user)->get(route('ledger.export'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('daily_reward', $response->streamedContent());
    }
}
