<?php

namespace App\Services;

use App\Models\DailyGameMetric;
use App\Models\GameEntry;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Throwable;

final class ProductionReadinessService
{
    /**
     * @return array<int, array{key:string,label:string,status:string,message:string}>
     */
    public function checks(): array
    {
        $checks = [];
        $production = app()->environment('production');

        $checks[] = $this->check(
            'php',
            'PHP version',
            PHP_VERSION_ID >= 80300,
            'PHP '.PHP_VERSION,
            'PHP 8.3 or newer is required.',
        );

        $key = (string) config('app.key');
        $checks[] = $this->check(
            'app_key',
            'Application key',
            filled($key) && mb_strlen($key) >= 32,
            'Configured',
            'APP_KEY is missing or too short.',
        );

        try {
            DB::select('select 1');
            $checks[] = $this->row('database', 'Database connection', 'ok', config('database.default').' is reachable.');
        } catch (Throwable $exception) {
            $checks[] = $this->row('database', 'Database connection', 'error', $exception->getMessage());
        }

        $checks[] = $this->check(
            'storage',
            'Writable storage',
            is_writable(storage_path()) && is_writable(storage_path('framework')),
            'Storage directories are writable.',
            'Laravel cannot write to storage.',
        );

        try {
            $probe = 'arcade-health-'.bin2hex(random_bytes(6));
            Cache::put($probe, 'ok', 30);
            $cacheWorks = Cache::get($probe) === 'ok';
            Cache::forget($probe);
            $checks[] = $this->check(
                'cache',
                'Cache store',
                $cacheWorks,
                config('cache.default').' cache is working.',
                'Cache write/read verification failed.',
            );
        } catch (Throwable $exception) {
            $checks[] = $this->row('cache', 'Cache store', 'error', $exception->getMessage());
        }

        $databaseDriver = (string) config('database.default');
        $checks[] = $this->row(
            'production_database',
            'Production database',
            $production && $databaseDriver === 'sqlite' ? 'warning' : 'ok',
            $production && $databaseDriver === 'sqlite'
                ? 'SQLite is suitable for demos, not a multi-instance production deployment.'
                : "Database driver: {$databaseDriver}.",
        );

        $queueDriver = (string) config('queue.default');
        $checks[] = $this->row(
            'queue_driver',
            'Queue connection',
            $production && $queueDriver === 'sync' ? 'error' : ($queueDriver === 'sync' ? 'warning' : 'ok'),
            $queueDriver === 'sync'
                ? 'Queued work executes inside web requests.'
                : "Queue driver: {$queueDriver}.",
        );

        $checks[] = $this->row(
            'debug',
            'Debug mode',
            $production && (bool) config('app.debug') ? 'error' : 'ok',
            config('app.debug') ? 'APP_DEBUG is enabled.' : 'APP_DEBUG is disabled.',
        );

        $url = (string) config('app.url');
        $checks[] = $this->row(
            'https',
            'Application URL',
            $production && ! str_starts_with($url, 'https://') ? 'error' : 'ok',
            $url,
        );

        if (Schema::hasTable('jobs')) {
            $pending = (int) DB::table('jobs')->count();
            $threshold = (int) config('arcade.operations.queue_warning_threshold', 100);
            $checks[] = $this->row(
                'pending_jobs',
                'Pending jobs',
                $pending > $threshold ? 'warning' : 'ok',
                number_format($pending).' pending job(s).',
            );
        }

        if (Schema::hasTable('failed_jobs')) {
            $failed = (int) DB::table('failed_jobs')->count();
            $checks[] = $this->row(
                'failed_jobs',
                'Failed jobs',
                $failed > 0 ? 'warning' : 'ok',
                number_format($failed).' failed job(s).',
            );
        }

        if (Schema::hasTable('daily_game_metrics') && Schema::hasTable('game_entries')) {
            $latestEntry = GameEntry::query()->max('created_at');
            $latestMetric = DailyGameMetric::query()->max('updated_at');
            $lagging = $latestEntry !== null && ($latestMetric === null || strtotime((string) $latestMetric) < strtotime((string) $latestEntry));
            $checks[] = $this->row(
                'metrics',
                'Analytics metrics',
                $lagging ? 'warning' : 'ok',
                $lagging ? 'Metrics are older than the newest game entry.' : 'Daily metrics are current.',
            );
        }

        $backupDirectory = storage_path('app/backups');
        $latestBackup = File::isDirectory($backupDirectory)
            ? collect(File::files($backupDirectory))->sortByDesc(fn ($file): int => $file->getMTime())->first()
            : null;
        $backupAgeHours = $latestBackup ? (int) floor((time() - $latestBackup->getMTime()) / 3600) : null;
        $checks[] = $this->row(
            'backup',
            'Recent backup',
            $latestBackup === null ? 'warning' : (($backupAgeHours ?? 0) > 48 ? 'warning' : 'ok'),
            $latestBackup === null
                ? 'No local operational backup was found.'
                : $latestBackup->getFilename()." ({$backupAgeHours}h old).",
        );

        return $checks;
    }

    /** @param array<int, array{status:string}> $checks */
    public function hasErrors(array $checks): bool
    {
        return collect($checks)->contains(fn (array $check): bool => $check['status'] === 'error');
    }

    /** @param array<int, array{status:string}> $checks */
    public function hasWarnings(array $checks): bool
    {
        return collect($checks)->contains(fn (array $check): bool => $check['status'] === 'warning');
    }

    /** @return array{key:string,label:string,status:string,message:string} */
    private function check(string $key, string $label, bool $passes, string $success, string $failure): array
    {
        return $this->row($key, $label, $passes ? 'ok' : 'error', $passes ? $success : $failure);
    }

    /** @return array{key:string,label:string,status:string,message:string} */
    private function row(string $key, string $label, string $status, string $message): array
    {
        return compact('key', 'label', 'status', 'message');
    }
}
