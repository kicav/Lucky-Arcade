<?php

namespace App\Console\Commands;

use App\Models\OperationRun;
use App\Models\SecurityEvent;
use App\Models\UserNotification;
use Illuminate\Console\Command;
use Throwable;

class PruneOperationalData extends Command
{
    protected $signature = 'arcade:prune
        {--notifications=90 : Retain read notifications for this many days}
        {--security=180 : Retain security events for this many days}
        {--operations=90 : Retain operation runs for this many days}';

    protected $description = 'Prune old operational records while retaining unread player notifications';

    public function handle(): int
    {
        $started = microtime(true);
        $notificationDays = max(7, (int) $this->option('notifications'));
        $securityDays = max(30, (int) $this->option('security'));
        $operationDays = max(30, (int) $this->option('operations'));
        $run = OperationRun::query()->create([
            'task' => 'operations.prune',
            'status' => 'running',
            'started_at' => now(),
            'details' => compact('notificationDays', 'securityDays', 'operationDays'),
        ]);

        try {
            $notifications = UserNotification::query()
                ->whereNotNull('read_at')
                ->where('created_at', '<', now()->subDays($notificationDays))
                ->delete();
            $security = SecurityEvent::query()
                ->where('created_at', '<', now()->subDays($securityDays))
                ->delete();
            $operations = OperationRun::query()
                ->where('id', '!=', $run->id)
                ->where('created_at', '<', now()->subDays($operationDays))
                ->delete();

            $details = compact('notifications', 'security', 'operations');
            $duration = (int) round((microtime(true) - $started) * 1000);
            $run->update([
                'status' => 'success',
                'finished_at' => now(),
                'duration_ms' => $duration,
                'details' => $details,
            ]);
            $this->info("Pruned {$notifications} notification(s), {$security} security event(s), and {$operations} operation run(s).");
            return self::SUCCESS;
        } catch (Throwable $exception) {
            $run->update([
                'status' => 'failed',
                'finished_at' => now(),
                'duration_ms' => (int) round((microtime(true) - $started) * 1000),
                'error_message' => mb_substr($exception->getMessage(), 0, 4000),
            ]);
            $this->error($exception->getMessage());
            return self::FAILURE;
        }
    }
}
