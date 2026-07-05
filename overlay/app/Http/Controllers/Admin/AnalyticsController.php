<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyGameMetric;
use App\Models\Game;
use App\Models\GameEntry;
use App\Models\ReferralReward;
use App\Models\UserAchievement;
use Carbon\CarbonImmutable;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function __invoke(): View
    {
        $start = CarbonImmutable::today()->subDays(13);
        $metrics = DailyGameMetric::query()
            ->where('metric_date', '>=', $start->toDateString())
            ->get()
            ->groupBy(fn (DailyGameMetric $metric): string => $metric->metric_date->toDateString());

        $daily = collect(range(0, 13))->map(function (int $offset) use ($start, $metrics): array {
            $date = $start->addDays($offset);
            $rows = $metrics->get($date->toDateString(), collect());

            return [
                'date' => $date,
                'plays' => (int) $rows->sum('plays'),
                'stake' => (int) $rows->sum('total_stake'),
                'payout' => (int) $rows->sum('total_payout'),
                'net' => (int) $rows->sum('system_net'),
            ];
        });

        $gameRows = Game::query()->orderBy('name')->get()->map(function (Game $game): array {
            $query = $game->entries();
            $plays = (int) (clone $query)->count();
            $stake = (int) (clone $query)->sum('stake');
            $payout = (int) (clone $query)->sum('payout');

            return [
                'game' => $game,
                'plays' => $plays,
                'stake' => $stake,
                'payout' => $payout,
                'net' => $stake - $payout,
                'rtp' => $stake > 0 ? round(($payout / $stake) * 100, 2) : 0.0,
            ];
        });

        $latestEntryAt = GameEntry::query()->max('created_at');
        $latestMetricAt = DailyGameMetric::query()->max('updated_at');
        $metricsLagging = $latestEntryAt !== null
            && ($latestMetricAt === null || strtotime((string) $latestMetricAt) < strtotime((string) $latestEntryAt));

        return view('admin.analytics', [
            'daily' => $daily,
            'gameRows' => $gameRows,
            'maxDailyStake' => max(1, (int) $daily->max('stake')),
            'referralRewards' => ReferralReward::query()->count(),
            'achievementUnlocks' => UserAchievement::query()->count(),
            'activePlayers14d' => GameEntry::query()->where('created_at', '>=', $start)->distinct()->count('user_id'),
            'metricsLagging' => $metricsLagging,
            'latestMetricAt' => $latestMetricAt,
        ]);
    }
}
