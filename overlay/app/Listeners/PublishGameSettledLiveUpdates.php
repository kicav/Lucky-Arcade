<?php

namespace App\Listeners;

use App\Events\GameSettled;
use App\Models\GameEntry;
use App\Services\LiveEventService;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;

final class PublishGameSettledLiveUpdates implements ShouldQueueAfterCommit
{
    public string $queue = 'notifications';

    public int $tries = 3;

    public function __construct(private readonly LiveEventService $live)
    {
    }

    public function handle(GameSettled $event): void
    {
        $entry = GameEntry::query()->with(['game', 'user.wallet'])->find($event->entryId);
        if (! $entry) {
            return;
        }

        $this->live->publishForUser($entry->user_id, 'game.settled', [
            'entry_id' => $entry->id,
            'game' => $entry->game->name,
            'game_code' => $entry->game->code,
            'stake' => $entry->stake,
            'payout' => $entry->payout,
            'net' => $entry->net,
            'balance' => $entry->user->wallet?->balance,
        ], topic: 'game', ttlSeconds: 1800);

        $this->live->publishPublic('league.changed', [
            'week' => now()->startOfWeek()->toDateString(),
        ], topic: 'league', ttlSeconds: 600);
    }
}
