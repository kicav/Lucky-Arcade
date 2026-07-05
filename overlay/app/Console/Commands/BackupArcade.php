<?php

namespace App\Console\Commands;

use App\Models\OperationRun;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use SQLite3;
use Symfony\Component\Process\Process;
use Throwable;

class BackupArcade extends Command
{
    protected $signature = 'arcade:backup {--keep=10 : Number of recent backups to retain}';

    protected $description = 'Create a consistent timestamped SQLite or PostgreSQL backup';

    public function handle(): int
    {
        $driver = (string) config('database.default');
        $startedAt = now();
        $started = microtime(true);

        try {
            $destination = match ($driver) {
                'sqlite' => $this->backupSqlite(),
                'pgsql' => $this->backupPostgres(),
                default => throw new \RuntimeException("Unsupported database driver: {$driver}"),
            };

            $this->pruneBackups(max(1, (int) $this->option('keep')));
            $duration = (int) round((microtime(true) - $started) * 1000);
            $this->recordRun([
                'task' => 'database.backup',
                'status' => 'success',
                'started_at' => $startedAt,
                'finished_at' => now(),
                'duration_ms' => $duration,
                'details' => ['driver' => $driver, 'file' => basename($destination)],
            ]);
            $this->info("Backup created: {$destination}");
            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->recordRun([
                'task' => 'database.backup',
                'status' => 'failed',
                'started_at' => $startedAt,
                'finished_at' => now(),
                'duration_ms' => (int) round((microtime(true) - $started) * 1000),
                'details' => ['driver' => $driver],
                'error_message' => mb_substr($exception->getMessage(), 0, 4000),
            ]);
            $this->error('Backup failed: '.$exception->getMessage());
            return self::FAILURE;
        }
    }

    /** @param array<string, mixed> $attributes */
    private function recordRun(array $attributes): void
    {
        try {
            if (Schema::hasTable('operation_runs')) {
                OperationRun::query()->create($attributes);
            }
        } catch (Throwable) {
            // A backup failure should still report the original error when the database is unavailable.
        }
    }

    private function backupSqlite(): string
    {
        if (! class_exists(SQLite3::class)) {
            throw new \RuntimeException('The sqlite3 PHP extension is required for consistent backups.');
        }

        $source = (string) config('database.connections.sqlite.database');
        if (! File::exists($source)) {
            throw new \RuntimeException("SQLite database not found: {$source}");
        }

        $directory = $this->backupDirectory();
        $destination = $directory.'/lucky-arcade-'.now()->format('Ymd-His').'.sqlite';
        $sourceDatabase = null;
        $destinationDatabase = null;

        try {
            $sourceDatabase = new SQLite3($source, SQLITE3_OPEN_READONLY);
            $destinationDatabase = new SQLite3($destination);

            if (! $sourceDatabase->backup($destinationDatabase)) {
                throw new \RuntimeException('SQLite backup API returned false.');
            }
        } catch (Throwable $exception) {
            File::delete($destination);
            throw $exception;
        } finally {
            $destinationDatabase?->close();
            $sourceDatabase?->close();
        }

        return $destination;
    }

    private function backupPostgres(): string
    {
        $binary = trim((string) shell_exec('command -v pg_dump 2>/dev/null'));
        if ($binary === '') {
            throw new \RuntimeException('pg_dump is not installed. Install the PostgreSQL client in the runtime image.');
        }

        $connection = config('database.connections.pgsql');
        $database = (string) ($connection['database'] ?? '');
        if ($database === '') {
            throw new \RuntimeException('PostgreSQL database name is missing.');
        }

        $destination = $this->backupDirectory().'/lucky-arcade-'.now()->format('Ymd-His').'.dump';
        $process = new Process([
            $binary,
            '--format=custom',
            '--no-owner',
            '--no-acl',
            '--host='.(string) ($connection['host'] ?? '127.0.0.1'),
            '--port='.(string) ($connection['port'] ?? '5432'),
            '--username='.(string) ($connection['username'] ?? 'postgres'),
            '--file='.$destination,
            $database,
        ]);
        $process->setTimeout(600);
        $process->setEnv(array_filter([
            'PGPASSWORD' => (string) ($connection['password'] ?? ''),
            'PGSSLMODE' => (string) ($connection['sslmode'] ?? 'prefer'),
        ], fn (string $value): bool => $value !== ''));
        $process->run();

        if (! $process->isSuccessful()) {
            File::delete($destination);
            throw new \RuntimeException(trim($process->getErrorOutput() ?: $process->getOutput()));
        }

        return $destination;
    }

    private function backupDirectory(): string
    {
        $directory = storage_path('app/backups');
        File::ensureDirectoryExists($directory);
        return $directory;
    }

    private function pruneBackups(int $keep): void
    {
        collect(File::files($this->backupDirectory()))
            ->filter(fn ($file): bool => in_array(strtolower($file->getExtension()), ['sqlite', 'dump'], true))
            ->sortByDesc(fn ($file): int => $file->getMTime())
            ->slice($keep)
            ->each(fn ($file) => File::delete($file->getPathname()));
    }
}
