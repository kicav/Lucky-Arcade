<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserPresence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PresenceAndLiveOperationsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function authenticated_live_poll_updates_presence(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->getJson(route('live.feed', ['after' => 0]))->assertOk();

        $this->assertDatabaseHas('user_presences', [
            'user_id' => $user->id,
            'current_path' => '/live/feed',
        ]);
    }

    #[Test]
    public function operations_admin_can_view_live_operations_data(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'admin_role' => 'operations']);
        UserPresence::query()->create([
            'user_id' => $admin->id,
            'current_path' => '/admin/live',
            'last_seen_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.live.index'))
            ->assertOk()
            ->assertViewIs('admin.live.index')
            ->assertSeeText('Live activity');

        $this->actingAs($admin)
            ->getJson(route('admin.live.data'))
            ->assertOk()
            ->assertJsonPath('online_users.0.email', $admin->email);
    }
}
