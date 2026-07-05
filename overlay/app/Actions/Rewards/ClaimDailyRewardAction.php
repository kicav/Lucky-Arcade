<?php

namespace App\Actions\Rewards;

use App\Enums\LedgerDirection;
use App\Models\DailyReward;
use App\Models\LedgerEntry;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ClaimDailyRewardAction
{
    public const AMOUNT = 250;

    public function execute(User $user): DailyReward
    {
        return DB::transaction(function () use ($user): DailyReward {
            $today = today()->toDateString();

            $wallet = Wallet::query()
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->firstOrFail();

            $existing = DailyReward::query()
                ->where('user_id', $user->id)
                ->whereDate('reward_date', $today)
                ->first();

            if ($existing) {
                throw ValidationException::withMessages([
                    'daily_reward' => 'Today\'s reward has already been claimed.',
                ]);
            }

            $reward = DailyReward::query()->create([
                'user_id' => $user->id,
                'reward_date' => $today,
                'amount' => self::AMOUNT,
            ]);

            $wallet->balance += self::AMOUNT;
            $wallet->save();

            LedgerEntry::query()->create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'direction' => LedgerDirection::Credit,
                'amount' => self::AMOUNT,
                'balance_after' => $wallet->balance,
                'type' => 'daily_reward',
                'idempotency_key' => "{$user->id}:daily-reward:{$today}",
                'reference_type' => DailyReward::class,
                'reference_id' => $reward->id,
                'metadata' => ['reward_date' => $today],
            ]);

            UserNotification::query()->create([
                'user_id' => $user->id,
                'type' => 'daily_reward',
                'title' => 'Daily reward collected',
                'message' => self::AMOUNT.' virtual credits were added to your wallet.',
                'data' => ['amount' => self::AMOUNT, 'reward_date' => $today],
            ]);

            return $reward;
        }, attempts: 3);
    }
}
