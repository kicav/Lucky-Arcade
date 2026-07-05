<?php

namespace App\Services;

use App\Enums\LedgerDirection;
use App\Models\GameEntry;
use App\Models\LedgerEntry;
use App\Models\ReferralReward;
use App\Models\User;
use App\Models\UserAchievement;
use App\Models\UserNotification;
use App\Models\Wallet;

final class AchievementService
{
    /** @return array<string, array{title:string, description:string, reward:int, target:int, metric:string}> */
    public function catalog(): array
    {
        return [
            'first_play' => [
                'title' => 'First Steps',
                'description' => 'Complete your first game.',
                'reward' => 100,
                'target' => 1,
                'metric' => 'plays',
            ],
            'first_win' => [
                'title' => 'Winner',
                'description' => 'Finish a game with a positive net result.',
                'reward' => 150,
                'target' => 1,
                'metric' => 'wins',
            ],
            'ten_plays' => [
                'title' => 'Regular Player',
                'description' => 'Complete 10 games.',
                'reward' => 250,
                'target' => 10,
                'metric' => 'plays',
            ],
            'game_explorer' => [
                'title' => 'Game Explorer',
                'description' => 'Play every available game type.',
                'reward' => 300,
                'target' => 4,
                'metric' => 'games',
            ],
            'stake_5000' => [
                'title' => 'Five Thousand Club',
                'description' => 'Reach 5,000 total virtual credits staked.',
                'reward' => 500,
                'target' => 5000,
                'metric' => 'stake',
            ],
            'first_referral' => [
                'title' => 'Community Builder',
                'description' => 'Invite a player who completes their first game.',
                'reward' => 300,
                'target' => 1,
                'metric' => 'referrals',
            ],
        ];
    }

    /** @return array{plays:int,wins:int,games:int,stake:int,referrals:int} */
    public function progress(User $user): array
    {
        $base = fn () => GameEntry::query()->where('user_id', $user->id);

        return [
            'plays' => (int) $base()->count(),
            'wins' => (int) $base()->where('net', '>', 0)->count(),
            'games' => (int) $base()->distinct()->count('game_id'),
            'stake' => (int) $base()->sum('stake'),
            'referrals' => (int) ReferralReward::query()->where('inviter_user_id', $user->id)->count(),
        ];
    }

    public function evaluateAfterPlay(User $user, Wallet $wallet): void
    {
        $progress = $this->progress($user);

        foreach ($this->catalog() as $code => $definition) {
            if ($code === 'first_referral') {
                continue;
            }

            if ($progress[$definition['metric']] >= $definition['target']) {
                $this->unlock($user, $wallet, $code, $definition);
            }
        }
    }

    public function evaluateReferralMilestones(User $user, Wallet $wallet): void
    {
        $definition = $this->catalog()['first_referral'];
        if (ReferralReward::query()->where('inviter_user_id', $user->id)->count() >= $definition['target']) {
            $this->unlock($user, $wallet, 'first_referral', $definition);
        }
    }

    /** @param array{title:string, description:string, reward:int, target:int, metric:string} $definition */
    private function unlock(User $user, Wallet $wallet, string $code, array $definition): void
    {
        $achievement = UserAchievement::query()->firstOrCreate(
            ['user_id' => $user->id, 'code' => $code],
            [
                'title' => $definition['title'],
                'description' => $definition['description'],
                'reward' => $definition['reward'],
                'unlocked_at' => now(),
            ],
        );

        if (! $achievement->wasRecentlyCreated) {
            return;
        }

        if ($definition['reward'] > 0) {
            $wallet->balance += $definition['reward'];
            $wallet->save();

            LedgerEntry::query()->create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'direction' => LedgerDirection::Credit,
                'amount' => $definition['reward'],
                'balance_after' => $wallet->balance,
                'type' => 'achievement_reward',
                'idempotency_key' => "achievement:{$achievement->id}",
                'reference_type' => UserAchievement::class,
                'reference_id' => $achievement->id,
                'metadata' => ['code' => $code],
            ]);
        }

        UserNotification::query()->create([
            'user_id' => $user->id,
            'type' => 'achievement_unlocked',
            'title' => 'Achievement unlocked: '.$definition['title'],
            'message' => $definition['description'].' Reward: '.number_format($definition['reward']).' credits.',
            'data' => ['code' => $code, 'reward' => $definition['reward']],
        ]);
    }
}
