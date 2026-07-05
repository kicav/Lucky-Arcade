<?php

namespace Tests\Feature;

use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LiveSupportTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function support_messages_can_be_polled_and_replied_to_with_json(): void
    {
        $player = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true, 'admin_role' => 'support']);
        $ticket = SupportTicket::query()->create([
            'user_id' => $player->id,
            'subject' => 'Live support test',
            'category' => 'technical',
            'priority' => 'normal',
            'status' => 'open',
            'last_replied_at' => now(),
        ]);
        $first = SupportMessage::query()->create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $player->id,
            'is_admin' => false,
            'body' => 'Initial message',
        ]);

        $this->actingAs($admin)
            ->postJson(route('admin.support.reply', $ticket), ['message' => 'Live admin reply'])
            ->assertCreated()
            ->assertJsonPath('message.body', 'Live admin reply');

        $this->actingAs($player)
            ->getJson(route('support.messages', [$ticket, 'after' => $first->id]))
            ->assertOk()
            ->assertJsonCount(1, 'messages')
            ->assertJsonPath('messages.0.is_admin', true)
            ->assertJsonPath('messages.0.body', 'Live admin reply');

        $this->assertDatabaseHas('live_events', [
            'audience' => 'user',
            'user_id' => $player->id,
            'event_type' => 'support.message.created',
        ]);
    }
}
