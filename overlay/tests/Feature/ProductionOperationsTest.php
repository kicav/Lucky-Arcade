<?php

namespace Tests\Feature;

use App\Models\OperationRun;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductionOperationsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function doctor_command_succeeds_in_a_healthy_test_runtime(): void
    {
        $this->artisan('arcade:doctor')->assertExitCode(0);
    }

    #[Test]
    public function operations_admin_can_refresh_metrics_from_the_dashboard(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'admin_role' => 'operations',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.system.metrics'))
            ->assertRedirect();

        $this->assertDatabaseHas('operation_runs', [
            'task' => 'metrics.refresh',
            'status' => 'success',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'system.metrics_refresh_requested',
        ]);
    }

    #[Test]
    public function system_page_exposes_queue_and_readiness_information(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'admin_role' => 'operations',
        ]);

        OperationRun::query()->create([
            'task' => 'metrics.refresh',
            'status' => 'success',
            'started_at' => now(),
            'finished_at' => now(),
            'duration_ms' => 5,
            'details' => ['days' => 1],
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.system.index'))
            ->assertOk()
            ->assertViewIs('admin.system.index')
            ->assertViewHasAll([
                'pendingJobs',
                'failedJobs',
                'readinessChecks',
                'operationRuns',
            ])
            ->assertSeeText('System health & jobs')
            ->assertSeeText('Production readiness');

        $operationRuns = $response->viewData('operationRuns');

        $this->assertTrue(
            $operationRuns->contains(fn (OperationRun $run): bool => $run->task === 'metrics.refresh'),
            'The system page did not receive the expected metrics.refresh operation run.',
        );
    }
}
