<?php

namespace App\Services;

use App\Models\DailyGameMetric;
use App\Models\GameEntry;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

final class DailyMetricsService
{
    public function refreshDate(CarbonInterface|string $date): int
    {
        $metricDate = $date instanceof CarbonInterface
            ? CarbonImmutable::instance($date)->toDateString()
            : CarbonImmutable::parse($date)->toDateString();

        $rows = GameEntry::query()
            ->whereDate('created_at', $metricDate)
            ->select('game_id')
            ->selectRaw('COUNT(*) as plays')
            ->selectRaw('SUM(CASE WHEN net > 0 THEN 1 ELSE 0 END) as wins')
            ->selectRaw('COALESCE(SUM(stake), 0) as total_stake')
            ->selectRaw('COALESCE(SUM(payout), 0) as total_payout')
            ->groupBy('game_id')
            ->get();

        $gameIds = $rows->pluck('game_id')->map(fn ($id): int => (int) $id)->all();

        if ($gameIds === []) {
            DailyGameMetric::query()->whereDate('metric_date', $metricDate)->delete();
            return 0;
        }

        DailyGameMetric::query()
            ->whereDate('metric_date', $metricDate)
            ->whereNotIn('game_id', $gameIds)
            ->delete();

        foreach ($rows as $row) {
            $gameId = (int) $row->game_id;
            $stake = (int) $row->total_stake;
            $payout = (int) $row->total_payout;
            $metric = DailyGameMetric::query()
                ->where('game_id', $gameId)
                ->whereDate('metric_date', $metricDate)
                ->first();

            if (! $metric) {
                $timestamp = now();
                DailyGameMetric::query()->insertOrIgnore([
                    'metric_date' => $metricDate,
                    'game_id' => $gameId,
                    'plays' => 0,
                    'wins' => 0,
                    'total_stake' => 0,
                    'total_payout' => 0,
                    'system_net' => 0,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);

                $metric = DailyGameMetric::query()
                    ->where('game_id', $gameId)
                    ->whereDate('metric_date', $metricDate)
                    ->firstOrFail();
            }

            $metric->fill([
                'plays' => (int) $row->plays,
                'wins' => (int) $row->wins,
                'total_stake' => $stake,
                'total_payout' => $payout,
                'system_net' => $stake - $payout,
            ])->save();
        }

        return $rows->count();
    }

    /** @return Collection<int, string> */
    public function refreshRange(int $days): Collection
    {
        $days = max(1, min(365, $days));
        $dates = collect();

        for ($offset = $days - 1; $offset >= 0; $offset--) {
            $date = CarbonImmutable::today()->subDays($offset);
            $this->refreshDate($date);
            $dates->push($date->toDateString());
        }

        return $dates;
    }
}
