<?php

namespace App\Services;

use App\Enums\LedgerDirection;
use App\Models\GameEntry;
use App\Models\LedgerEntry;
use App\Models\ReferralReward;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\Wallet;

final class ReferralRewardService
{
    public const INVITER_AMOUNT = 500;
    public const REFERRED_AMOUNT = 500;

    public function __construct(private readonly AchievementService $achievements)
    {
    }

    public function awardForFirstPlay(User $referredUser, Wallet $referredWallet, GameEntry $entry): void
    {
        if (! $referredUser->referred_by_user_id) {
            return;
        }

        if (ReferralReward::query()->where('referred_user_id', $referredUser->id)->exists()) {
            return;
        }

        if ($referredUser->gameEntries()->count() !== 1) {
            return;
        }

        $inviter = User::query()
            ->whereKey($referredUser->referred_by_user_id)
            ->where('is_admin', false)
            ->lockForUpdate()
            ->first();

        if (! $inviter || $inviter->id === $referredUser->id) {
            return;
        }

        $inviterWallet = Wallet::query()->where('user_id', $inviter->id)->lockForUpdate()->firstOrFail();

        $reward = ReferralReward::query()->create([
            'inviter_user_id' => $inviter->id,
            'referred_user_id' => $referredUser->id,
            'triggered_by_entry_id' => $entry->id,
            'inviter_amount' => self::INVITER_AMOUNT,
            'referred_amount' => self::REFERRED_AMOUNT,
        ]);

        $this->credit($inviter, $inviterWallet, self::INVITER_AMOUNT, 'referral_bonus_inviter', $reward, 'inviter');
        $this->credit($referredUser, $referredWallet, self::REFERRED_AMOUNT, 'referral_bonus_referred', $reward, 'referred');

        UserNotification::query()->insert([
            [
                'user_id' => $inviter->id,
                'type' => 'referral_reward',
                'title' => 'Referral reward unlocked',
                'message' => number_format(self::INVITER_AMOUNT).' credits were added after your invited player completed a first game.',
                'data' => json_encode(['reward_id' => $reward->id, 'amount' => self::INVITER_AMOUNT]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $referredUser->id,
                'type' => 'referral_reward',
                'title' => 'Welcome referral reward',
                'message' => number_format(self::REFERRED_AMOUNT).' credits were added after your first completed game.',
                'data' => json_encode(['reward_id' => $reward->id, 'amount' => self::REFERRED_AMOUNT]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->achievements->evaluateReferralMilestones($inviter, $inviterWallet);
    }

    private function credit(
        User $user,
        Wallet $wallet,
        int $amount,
        string $type,
        ReferralReward $reward,
        string $side,
    ): void {
        $wallet->balance += $amount;
        $wallet->save();

        LedgerEntry::query()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'direction' => LedgerDirection::Credit,
            'amount' => $amount,
            'balance_after' => $wallet->balance,
            'type' => $type,
            'idempotency_key' => "referral:{$reward->id}:{$side}",
            'reference_type' => ReferralReward::class,
            'reference_id' => $reward->id,
            'metadata' => ['side' => $side],
        ]);
    }
}
