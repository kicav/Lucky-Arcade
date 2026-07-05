<?php

namespace App\Actions\Games;

use App\Enums\LedgerDirection;
use App\Events\GameSettled;
use App\Models\FairnessSeed;
use App\Models\Game;
use App\Models\GameEntry;
use App\Models\LedgerEntry;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\Wallet;
use App\Services\AchievementService;
use App\Services\FairnessSeedService;
use App\Services\GameEngineRegistry;
use App\Services\MissionService;
use App\Services\ReferralRewardService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class PlaceBetAction
{
    public function __construct(
        private readonly GameEngineRegistry $engines,
        private readonly FairnessSeedService $seeds,
        private readonly ReferralRewardService $referrals,
        private readonly AchievementService $achievements,
        private readonly MissionService $missions,
    ) {
    }

    /** @param array<string, mixed> $bet */
    public function execute(User $user, Game $game, int $stake, array $bet, string $requestId): GameEntry
    {
        return DB::transaction(function () use ($user, $game, $stake, $bet, $requestId): GameEntry {
            $existing = GameEntry::query()
                ->where('user_id', $user->id)
                ->where('request_id', $requestId)
                ->first();

            if ($existing) {
                return $existing;
            }

            $lockedUser = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $this->validatePlayer($lockedUser, $stake);

            $lockedGame = Game::query()->whereKey($game->id)->lockForUpdate()->firstOrFail();
            $this->validateGame($lockedGame, $stake);

            $wallet = Wallet::query()->where('user_id', $lockedUser->id)->lockForUpdate()->firstOrFail();
            if ($wallet->balance < $stake) {
                throw ValidationException::withMessages(['stake' => 'Insufficient virtual credits.']);
            }

            $seed = FairnessSeed::query()
                ->where('user_id', $lockedUser->id)
                ->where('active', true)
                ->lockForUpdate()
                ->first();

            if (! $seed) {
                $seed = $this->seeds->create($lockedUser);
                $seed = FairnessSeed::query()->whereKey($seed->id)->lockForUpdate()->firstOrFail();
            }

            $outcome = $this->engines->for($lockedGame->code)->play(
                stake: $stake,
                bet: $bet,
                serverSeed: $seed->server_seed,
                clientSeed: $seed->client_seed,
                nonce: $seed->nonce,
            );

            $entry = GameEntry::query()->create([
                'user_id' => $lockedUser->id,
                'game_id' => $lockedGame->id,
                'fairness_seed_id' => $seed->id,
                'stake' => $stake,
                'payout' => $outcome->payout,
                'net' => $outcome->payout - $stake,
                'bet' => $bet,
                'result' => $outcome->result + ['won' => $outcome->won],
                'client_seed' => $seed->client_seed,
                'nonce' => $seed->nonce,
                'server_seed_hash' => $seed->server_seed_hash,
                'request_id' => $requestId,
                'status' => 'settled',
            ]);

            $wallet->balance -= $stake;
            $wallet->save();

            LedgerEntry::query()->create([
                'user_id' => $lockedUser->id,
                'wallet_id' => $wallet->id,
                'direction' => LedgerDirection::Debit,
                'amount' => $stake,
                'balance_after' => $wallet->balance,
                'type' => 'game_stake',
                'idempotency_key' => "{$lockedUser->id}:{$requestId}:stake",
                'reference_type' => GameEntry::class,
                'reference_id' => $entry->id,
                'metadata' => ['game' => $lockedGame->code],
            ]);

            if ($outcome->payout > 0) {
                $wallet->balance += $outcome->payout;
                $wallet->save();

                LedgerEntry::query()->create([
                    'user_id' => $lockedUser->id,
                    'wallet_id' => $wallet->id,
                    'direction' => LedgerDirection::Credit,
                    'amount' => $outcome->payout,
                    'balance_after' => $wallet->balance,
                    'type' => 'game_payout',
                    'idempotency_key' => "{$lockedUser->id}:{$requestId}:payout",
                    'reference_type' => GameEntry::class,
                    'reference_id' => $entry->id,
                    'metadata' => ['game' => $lockedGame->code],
                ]);

                if ($outcome->payout >= max(500, $stake * 3)) {
                    UserNotification::query()->create([
                        'user_id' => $lockedUser->id,
                        'type' => 'big_win',
                        'title' => 'Big virtual-credit win',
                        'message' => "You won {$outcome->payout} credits in {$lockedGame->name}.",
                        'data' => ['entry_id' => $entry->id, 'game' => $lockedGame->code, 'payout' => $outcome->payout],
                    ]);
                }
            }

            $this->referrals->awardForFirstPlay($lockedUser, $wallet, $entry);
            $this->achievements->evaluateAfterPlay($lockedUser, $wallet);
            $this->missions->recordPlay($lockedUser);

            $seed->increment('nonce');

            DB::afterCommit(static function () use ($entry): void {
                GameSettled::dispatch($entry->id);
            });

            return $entry->fresh(['game']);
        }, attempts: 3);
    }

    private function validatePlayer(User $user, int $stake): void
    {
        if ($user->isSuspended()) {
            throw ValidationException::withMessages(['account' => 'This account is suspended.']);
        }

        if ($user->isSelfExcluded()) {
            throw ValidationException::withMessages([
                'account' => 'Self-exclusion is active until '.$user->self_excluded_until->format('Y-m-d H:i').'.',
            ]);
        }

        if ($user->daily_stake_limit !== null) {
            $todayStake = (int) GameEntry::query()
                ->where('user_id', $user->id)
                ->whereDate('created_at', today())
                ->sum('stake');

            if ($todayStake + $stake > $user->daily_stake_limit) {
                $remaining = max(0, $user->daily_stake_limit - $todayStake);
                throw ValidationException::withMessages([
                    'stake' => "Daily stake limit reached. Remaining today: {$remaining} credits.",
                ]);
            }
        }
    }

    private function validateGame(Game $game, int $stake): void
    {
        if (! $game->enabled) {
            throw ValidationException::withMessages(['game' => 'This game is disabled.']);
        }

        if ($stake < $game->min_bet || $stake > $game->max_bet) {
            throw ValidationException::withMessages([
                'stake' => "Stake must be between {$game->min_bet} and {$game->max_bet} credits.",
            ]);
        }
    }
}
