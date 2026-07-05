<?php

namespace App\Console\Commands;

use App\Models\OperationRun;
use App\Services\DailyMetricsService;
use Illuminate\Console\Command;
use Throwable;

class RefreshDailyMetrics extends Command
{
    protected $signature = 'arcade:metrics {--days=14 : Number of recent calendar days to rebuild}';

    protected $description = 'Rebuild idempotent daily game aggregates used by the admin analytics dashboard';

    public function handle(DailyMetricsService $metrics): int
    {
        $days = max(1, min(365, (int) $this->option('days')));
        $started = microtime(true);
        $run = OperationRun::query()->create([
            'task' => 'metrics.refresh',
            'status' => 'running',
            'started_at' => now(),
            'details' => ['days' => $days],
        ]);

        try {
            $dates = $metrics->refreshRange($days);
            $duration = (int) round((microtime(true) - $started) * 1000);
            $run->update([
                'status' => 'success',
                'finished_at' => now(),
                'duration_ms' => $duration,
                'details' => ['days' => $days, 'dates' => $dates->values()->all()],
            ]);
            $this->info("Daily metrics refreshed for {$days} day(s) in {$duration} ms.");
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
