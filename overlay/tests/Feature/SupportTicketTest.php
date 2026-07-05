<?php

namespace Tests\Feature;

use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SupportTicketTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function player_creates_ticket_and_admin_reply_notifies_player(): void
    {
        $player = User::factory()->create();
        $player->wallet()->create(['balance' => 100]);
        $admin = User::factory()->create(['is_admin' => true]);
        $admin->wallet()->create(['balance' => 100]);

        $this->actingAs($player)->post(route('support.store'), [
            'subject' => 'Game question', 'category' => 'game', 'priority' => 'normal',
            'message' => 'Please explain this result.',
        ])->assertRedirect();

        $ticket = SupportTicket::query()->firstOrFail();
        $this->assertSame(1, $ticket->messages()->count());

        $this->actingAs($admin)->post(route('admin.support.reply', $ticket), [
            'message' => 'We checked the round and it is valid.',
        ])->assertRedirect();

        $this->assertSame('pending', $ticket->fresh()->status);
        $this->assertDatabaseHas('user_notifications', ['user_id' => $player->id, 'type' => 'support_reply']);
        $this->assertSame(2, $ticket->messages()->count());
    }
}
