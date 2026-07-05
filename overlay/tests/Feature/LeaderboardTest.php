<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaderboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_leaderboard_is_publicly_accessible(): void
    {
        $this->get(route('leaderboard'))->assertOk();
    }
}
