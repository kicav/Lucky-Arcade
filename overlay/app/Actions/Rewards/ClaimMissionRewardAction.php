<?php

namespace App\Actions\Rewards;

use App\Enums\LedgerDirection;
use App\Models\LedgerEntry;
use App\Models\User;
use App\Models\UserMission;
use App\Models\UserNotification;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ClaimMissionRewardAction
{
    public function execute(User $user, UserMission $mission): UserMission
    {
        return DB::transaction(function () use ($user, $mission): UserMission {
            $locked = UserMission::query()->whereKey($mission->id)->lockForUpdate()->firstOrFail();

            if ($locked->user_id !== $user->id || ! $locked->mission_date->isToday()) {
                abort(404);
            }

            if (! $locked->isComplete()) {
                throw ValidationException::withMessages(['mission' => 'Complete the mission before claiming its reward.']);
            }

            if ($locked->claimed_at !== null) {
                return $locked;
            }

            $wallet = Wallet::query()->where('user_id', $user->id)->lockForUpdate()->firstOrFail();
            $wallet->balance += $locked->reward;
            $wallet->save();

            LedgerEntry::query()->create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'direction' => LedgerDirection::Credit,
                'amount' => $locked->reward,
                'balance_after' => $wallet->balance,
                'type' => 'mission_reward',
                'idempotency_key' => "mission:{$locked->id}:reward",
                'reference_type' => UserMission::class,
                'reference_id' => $locked->id,
                'metadata' => ['mission_key' => $locked->mission_key],
            ]);

            $locked->claimed_at = now();
            $locked->save();

            UserNotification::query()->create([
                'user_id' => $user->id,
                'type' => 'mission_reward',
                'title' => 'Daily mission reward claimed',
                'message' => "You received {$locked->reward} credits for {$locked->title}.",
                'data' => ['mission_id' => $locked->id, 'reward' => $locked->reward],
            ]);

            return $locked;
        }, attempts: 3);
    }
}
