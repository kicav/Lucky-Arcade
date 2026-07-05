<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AnnouncementTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function an_admin_can_publish_an_announcement_that_appears_on_the_homepage(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('admin.announcements.store'), [
            'title' => 'Scheduled maintenance',
            'body' => 'The demo will restart later today.',
            'active' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('announcements', ['title' => 'Scheduled maintenance', 'active' => true]);
        $this->get(route('home'))->assertOk()->assertSee('Scheduled maintenance');
    }
}
