<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_support_role_can_open_support_but_not_analytics(): void
    {
        $support = User::factory()->create(['is_admin' => true, 'admin_role' => 'support']);

        $this->actingAs($support)->get(route('admin.support.index'))->assertOk();
        $this->actingAs($support)->get(route('admin.analytics'))->assertForbidden();
    }

    public function test_only_super_admin_can_assign_administrator_roles(): void
    {
        $super = User::factory()->create(['is_admin' => true, 'admin_role' => 'super_admin']);
        $operations = User::factory()->create(['is_admin' => true, 'admin_role' => 'operations']);
        $player = User::factory()->create();

        $this->actingAs($operations)->get(route('admin.access.index'))->assertForbidden();

        $this->actingAs($super)->put(route('admin.access.update', $player), [
            'admin_role' => 'support',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $player->id,
            'is_admin' => true,
            'admin_role' => 'support',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $super->id,
            'action' => 'admin_access.updated',
            'subject_id' => $player->id,
        ]);
    }
}
