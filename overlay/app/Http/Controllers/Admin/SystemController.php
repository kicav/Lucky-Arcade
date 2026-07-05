<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\DailyGameMetric;
use App\Models\OperationRun;
use App\Models\SecurityEvent;
use App\Services\ProductionReadinessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Throwable;

class SystemController extends Controller
{
    public function index(ProductionReadinessService $readiness): View
    {
        $databaseStatus = 'OK';
        try {
            DB::select('select 1');
        } catch (Throwable $exception) {
            $databaseStatus = 'ERROR: '.$exception->getMessage();
        }

        $databasePath = config('database.default') === 'sqlite'
            ? (string) config('database.connections.sqlite.database')
            : null;
        $backupDirectory = storage_path('app/backups');
        $backups = File::isDirectory($backupDirectory)
            ? collect(File::files($backupDirectory))->sortByDesc(fn ($file) => $file->getMTime())->take(10)
            : collect();

        return view('admin.system.index', [
            'databaseStatus' => $databaseStatus,
            'databasePath' => $databasePath,
            'databaseSize' => $databasePath && File::exists($databasePath) ? File::size($databasePath) : null,
            'storageWritable' => is_writable(storage_path()),
            'backups' => $backups,
            'securityEventCount24h' => SecurityEvent::query()->where('created_at', '>=', now()->subDay())->count(),
            'failedLoginCount24h' => SecurityEvent::query()->where('event', 'login.failed')->where('created_at', '>=', now()->subDay())->count(),
            'pendingJobs' => Schema::hasTable('jobs') ? (int) DB::table('jobs')->count() : null,
            'failedJobs' => Schema::hasTable('failed_jobs') ? (int) DB::table('failed_jobs')->count() : null,
            'latestMetricAt' => Schema::hasTable('daily_game_metrics') ? DailyGameMetric::query()->max('updated_at') : null,
            'operationRuns' => Schema::hasTable('operation_runs')
                ? OperationRun::query()->latest('started_at')->limit(15)->get()
                : collect(),
            'readinessChecks' => $readiness->checks(),
        ]);
    }

    public function backup(Request $request): RedirectResponse
    {
        return $this->runCommand(
            request: $request,
            command: 'arcade:backup',
            arguments: ['--keep' => (int) config('arcade.operations.backup_keep', 14)],
            auditAction: 'system.backup_requested',
            successFallback: 'Backup created.',
        );
    }

    public function reconcile(Request $request): RedirectResponse
    {
        return $this->runCommand(
            request: $request,
            command: 'wallets:reconcile',
            arguments: [],
            auditAction: 'system.wallet_reconciliation_requested',
            successFallback: 'Wallet reconciliation completed.',
        );
    }

    public function metrics(Request $request): RedirectResponse
    {
        return $this->runCommand(
            request: $request,
            command: 'arcade:metrics',
            arguments: ['--days' => 30],
            auditAction: 'system.metrics_refresh_requested',
            successFallback: 'Daily metrics refreshed.',
        );
    }

    public function prune(Request $request): RedirectResponse
    {
        return $this->runCommand(
            request: $request,
            command: 'arcade:prune',
            arguments: [
                '--notifications' => (int) config('arcade.operations.notification_retention_days', 90),
                '--security' => (int) config('arcade.operations.security_event_retention_days', 180),
                '--operations' => (int) config('arcade.operations.operation_run_retention_days', 90),
            ],
            auditAction: 'system.prune_requested',
            successFallback: 'Old operational records pruned.',
        );
    }

    /** @param array<string, int|string|bool> $arguments */
    private function runCommand(
        Request $request,
        string $command,
        array $arguments,
        string $auditAction,
        string $successFallback,
    ): RedirectResponse {
        $exitCode = Artisan::call($command, $arguments);
        $output = trim(Artisan::output());

        AuditLog::query()->create([
            'actor_id' => $request->user()->id,
            'action' => $auditAction,
            'subject_type' => null,
            'subject_id' => null,
            'before' => null,
            'after' => [
                'command' => $command,
                'arguments' => $arguments,
                'exit_code' => $exitCode,
                'output' => mb_substr($output, 0, 2000),
            ],
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            'created_at' => now(),
        ]);

        if ($exitCode !== 0) {
            return back()->withErrors(['operation' => $output ?: "{$command} failed."]);
        }

        return back()->with('success', $output ?: $successFallback);
    }
}
