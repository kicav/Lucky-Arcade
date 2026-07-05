<?php

namespace App\Console\Commands;

use App\Services\LiveEventService;
use App\Services\PresenceService;
use Illuminate\Console\Command;

class PruneLiveExperience extends Command
{
    protected $signature = 'arcade:prune-live {--presence-minutes=1440 : Remove presence rows older than this many minutes}';

    protected $description = 'Prune expired live events and stale presence rows';

    public function handle(LiveEventService $events, PresenceService $presence): int
    {
        $eventCount = $events->prune();
        $presenceCount = $presence->prune((int) $this->option('presence-minutes'));
        $this->info("Pruned {$eventCount} live event(s) and {$presenceCount} stale presence row(s).");

        return self::SUCCESS;
    }
}
