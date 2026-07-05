<?php

namespace App\Listeners;

use App\Events\GameSettled;
use App\Models\GameEntry;
use App\Services\DailyMetricsService;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;

final class UpdateDailyGameMetrics implements ShouldQueueAfterCommit
{
    public string $queue = 'analytics';

    public int $tries = 3;

    public int $timeout = 60;

    public function __construct(private readonly DailyMetricsService $metrics)
    {
    }

    public function handle(GameSettled $event): void
    {
        $entry = GameEntry::query()->find($event->entryId);

        if (! $entry) {
            return;
        }

        $this->metrics->refreshDate($entry->created_at);
    }

    /** @return list<int> */
    public function backoff(): array
    {
        return [5, 30, 120];
    }
}
