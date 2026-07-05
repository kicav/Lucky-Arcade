<?php

namespace App\Actions\Rewards;

use App\Enums\LedgerDirection;
use App\Models\AuditLog;
use App\Models\LedgerEntry;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\Wallet;
use App\Models\WeeklyLeagueReward;
use App\Models\WeeklyLeagueSettlement;
use App\Services\WeeklyLeagueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class SettleWeeklyLeagueAction
{
    public function __construct(private readonly WeeklyLeagueService $league)
    {
    }

    public function execute(User $admin, Request $request): WeeklyLeagueSettlement
    {
        $weekStart = $this->league->previousWeekStart()->toDateString();

        return DB::transaction(function () use ($admin, $request, $weekStart): WeeklyLeagueSettlement {
            $settlement = WeeklyLeagueSettlement::query()
                ->whereDate('week_start', $weekStart)
                ->lockForUpdate()
                ->first();

            if (! $settlement) {
                WeeklyLeagueSettlement::query()->insertOrIgnore([
                    'week_start' => $weekStart,
                    'settled_by' => null,
                    'settled_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $settlement = WeeklyLeagueSettlement::query()
                    ->whereDate('week_start', $weekStart)
                    ->lockForUpdate()
                    ->firstOrFail();
            }

            if ($settlement->settled_at !== null) {
                return $settlement;
            }

            $standings = $this->league->standings($this->league->previousWeekStart(), 3);
            $rewards = $this->league->rewards();

            foreach ($standings as $row) {
                $rank = (int) $row['rank'];
                $rewardAmount = $rewards[$rank] ?? 0;
                if ($rewardAmount <= 0) {
                    continue;
                }

                $reward = WeeklyLeagueReward::query()->create([
                    'week_start' => $weekStart,
                    'user_id' => $row['user_id'],
                    'rank' => $rank,
                    'score' => $row['score'],
                    'reward' => $rewardAmount,
                    'awarded_at' => now(),
                ]);

                $wallet = Wallet::query()->where('user_id', $row['user_id'])->lockForUpdate()->firstOrFail();
                $wallet->balance += $rewardAmount;
                $wallet->save();

                LedgerEntry::query()->create([
                    'user_id' => $row['user_id'],
                    'wallet_id' => $wallet->id,
                    'direction' => LedgerDirection::Credit,
                    'amount' => $rewardAmount,
                    'balance_after' => $wallet->balance,
                    'type' => 'weekly_league_reward',
                    'idempotency_key' => "weekly-league:{$weekStart}:user:{$row['user_id']}",
                    'reference_type' => WeeklyLeagueReward::class,
                    'reference_id' => $reward->id,
                    'metadata' => ['rank' => $rank, 'score' => $row['score']],
                ]);

                UserNotification::query()->create([
                    'user_id' => $row['user_id'],
                    'type' => 'weekly_league',
                    'title' => 'Weekly League reward',
                    'message' => "You placed #{$rank} and received {$rewardAmount} virtual credits.",
                    'data' => ['week_start' => $weekStart, 'rank' => $rank, 'reward' => $rewardAmount],
                ]);
            }

            $settlement->update(['settled_by' => $admin->id, 'settled_at' => now()]);

            AuditLog::query()->create([
                'actor_id' => $admin->id,
                'action' => 'weekly_league.settled',
                'subject_type' => WeeklyLeagueSettlement::class,
                'subject_id' => $settlement->id,
                'before' => null,
                'after' => ['week_start' => $weekStart, 'reward_count' => $standings->count()],
                'ip_address' => $request->ip(),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
                'created_at' => now(),
            ]);

            return $settlement->fresh();
        }, attempts: 3);
    }
}
