<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\LiveEventService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LiveFeedTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_player_receives_public_and_personal_events_but_not_another_players_event(): void
    {
        $first = User::factory()->create();
        $second = User::factory()->create();
        $live = app(LiveEventService::class);

        $cursor = $live->publishForUser($second, 'bootstrap.hidden')->id;
        $public = $live->publishPublic('league.changed', ['week' => '2026-07-05'], 'league');
        $personal = $live->publishForUser($first, 'notification.created', ['title' => 'Hello'], 'notification');
        $live->publishForUser($second, 'private.other', ['secret' => true]);
        $live->publishAdmin('support.message.created', ['ticket_id' => 1], 'support');

        $response = $this->actingAs($first)
            ->getJson(route('live.feed', ['after' => $cursor]))
            ->assertOk();

        $types = collect($response->json('events'))->pluck('type');
        $this->assertTrue($types->contains($public->event_type));
        $this->assertTrue($types->contains($personal->event_type));
        $this->assertFalse($types->contains('private.other'));
        $this->assertFalse($types->contains('support.message.created'));
    }

    #[Test]
    public function the_first_poll_bootstraps_a_cursor_without_replaying_old_events(): void
    {
        $user = User::factory()->create();
        app(LiveEventService::class)->publishForUser($user, 'notification.created');

        $this->actingAs($user)
            ->getJson(route('live.feed', ['after' => 0]))
            ->assertOk()
            ->assertJsonCount(0, 'events')
            ->assertJsonPath('next_after', 1);
    }
}
