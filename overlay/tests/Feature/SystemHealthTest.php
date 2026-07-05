<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemHealthTest extends TestCase
{
    use RefreshDatabase;

    public function test_operations_can_view_system_health_but_support_cannot(): void
    {
        $operations = User::factory()->create(['is_admin' => true, 'admin_role' => 'operations']);
        $support = User::factory()->create(['is_admin' => true, 'admin_role' => 'support']);

        $this->actingAs($operations)->get(route('admin.system.index'))->assertOk();
        $this->actingAs($support)->get(route('admin.system.index'))->assertForbidden();
    }
}
