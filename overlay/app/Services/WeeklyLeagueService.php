<?php

namespace App\Services;

use App\Models\WeeklyLeagueReward;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class WeeklyLeagueService
{
    public function currentWeekStart(): Carbon
    {
        return now()->startOfWeek(Carbon::MONDAY)->startOfDay();
    }

    public function previousWeekStart(): Carbon
    {
        return $this->currentWeekStart()->subWeek();
    }

    /** @return Collection<int, array<string, mixed>> */
    public function standings(Carbon $weekStart, int $limit = 20): Collection
    {
        $start = $weekStart->copy()->startOfDay();
        $end = $start->copy()->addWeek();

        return DB::table('game_entries')
            ->join('users', 'users.id', '=', 'game_entries.user_id')
            ->where('users.is_admin', false)
            ->where('game_entries.created_at', '>=', $start)
            ->where('game_entries.created_at', '<', $end)
            ->groupBy('users.id', 'users.name', 'users.email')
            ->selectRaw('users.id as user_id, users.name, users.email, COUNT(*) as plays, SUM(CASE WHEN game_entries.net > 0 THEN 1 ELSE 0 END) as wins, SUM(game_entries.stake) as total_stake, SUM(game_entries.net) as net')
            ->get()
            ->map(function (object $row): array {
                $plays = (int) $row->plays;
                $wins = (int) $row->wins;
                $stake = (int) $row->total_stake;

                return [
                    'user_id' => (int) $row->user_id,
                    'name' => (string) $row->name,
                    'email' => (string) $row->email,
                    'plays' => $plays,
                    'wins' => $wins,
                    'total_stake' => $stake,
                    'net' => (int) $row->net,
                    'score' => ($plays * 5) + ($wins * 20) + min(500, intdiv($stake, 10)),
                ];
            })
            ->sortBy([
                ['score', 'desc'],
                ['wins', 'desc'],
                ['net', 'desc'],
                ['user_id', 'asc'],
            ])
            ->values()
            ->take($limit)
            ->map(function (array $row, int $index): array {
                $row['rank'] = $index + 1;
                return $row;
            });
    }

    /** @return array<int, int> */
    public function rewards(): array
    {
        return [1 => 1000, 2 => 600, 3 => 300];
    }

    public function userRank(Collection $standings, int $userId): ?array
    {
        return $standings->first(fn (array $row): bool => $row['user_id'] === $userId);
    }

    public function rewardHistory(int $userId): Collection
    {
        return WeeklyLeagueReward::query()
            ->where('user_id', $userId)
            ->latest('week_start')
            ->limit(12)
            ->get();
    }
}
