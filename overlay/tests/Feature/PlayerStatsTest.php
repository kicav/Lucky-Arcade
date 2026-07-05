<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PlayerStatsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function an_authenticated_player_can_view_statistics(): void
    {
        $user = User::factory()->create();
        $user->wallet()->create(['balance' => 1000]);

        $this->actingAs($user)->get(route('stats.index'))
            ->assertOk()
            ->assertSee('Your performance');
    }
}
