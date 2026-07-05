<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        $entries = GameEntry::query()
            ->with('game:id,code,name')
            ->where('created_at', '>=', $start)
            ->get();

        $daily = collect(range(0, 13))->map(function (int $offset) use ($start, $entries): array {
            $date = $start->addDays($offset);
            $rows = $entries->filter(fn (GameEntry $entry) => $entry->created_at->toDateString() === $date->toDateString());

            return [
                'date' => $date,
                'plays' => $rows->count(),
                'stake' => (int) $rows->sum('stake'),
                'payout' => (int) $rows->sum('payout'),
                'net' => (int) $rows->sum('stake') - (int) $rows->sum('payout'),
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

        return view('admin.analytics', [
            'daily' => $daily,
            'gameRows' => $gameRows,
            'maxDailyStake' => max(1, (int) $daily->max('stake')),
            'referralRewards' => ReferralReward::query()->count(),
            'achievementUnlocks' => UserAchievement::query()->count(),
            'activePlayers14d' => GameEntry::query()->where('created_at', '>=', $start)->distinct()->count('user_id'),
        ]);
    }
}
