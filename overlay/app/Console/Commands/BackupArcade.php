<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use SQLite3;
use Throwable;

class BackupArcade extends Command
{
    protected $signature = 'arcade:backup {--keep=10 : Number of recent SQLite backups to retain}';

    protected $description = 'Create a consistent timestamped backup of the Lucky Arcade SQLite database';

    public function handle(): int
    {
        if (config('database.default') !== 'sqlite') {
            $this->error('This command currently supports SQLite only.');
            return self::FAILURE;
        }

        if (! class_exists(SQLite3::class)) {
            $this->error('The sqlite3 PHP extension is required for consistent backups.');
            return self::FAILURE;
        }

        $source = (string) config('database.connections.sqlite.database');
        if (! File::exists($source)) {
            $this->error("SQLite database not found: {$source}");
            return self::FAILURE;
        }

        $directory = storage_path('app/backups');
        File::ensureDirectoryExists($directory);
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
            $this->error('Backup failed: '.$exception->getMessage());
            return self::FAILURE;
        } finally {
            $destinationDatabase?->close();
            $sourceDatabase?->close();
        }

        $keep = max(1, (int) $this->option('keep'));
        collect(File::files($directory))
            ->filter(fn ($file): bool => str_ends_with($file->getFilename(), '.sqlite'))
            ->sortByDesc(fn ($file): int => $file->getMTime())
            ->slice($keep)
            ->each(fn ($file) => File::delete($file->getPathname()));

        $this->info("Backup created: {$destination}");
        return self::SUCCESS;
    }
}
