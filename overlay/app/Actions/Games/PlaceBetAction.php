<?php

namespace App\Actions\Games;

use App\Enums\LedgerDirection;
use App\Models\FairnessSeed;
use App\Models\Game;
use App\Models\GameEntry;
use App\Models\LedgerEntry;
use App\Models\User;
use App\Models\Wallet;
use App\Services\FairnessSeedService;
use App\Services\GameEngineRegistry;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class PlaceBetAction
{
    public function __construct(
        private readonly GameEngineRegistry $engines,
        private readonly FairnessSeedService $seeds,
    ) {
    }

    /**
     * @param array<string, mixed> $bet
     */
    public function execute(
        User $user,
        Game $game,
        int $stake,
        array $bet,
        string $requestId,
    ): GameEntry {
        return DB::transaction(function () use ($user, $game, $stake, $bet, $requestId): GameEntry {
            $existing = GameEntry::query()
                ->where('user_id', $user->id)
                ->where('request_id', $requestId)
                ->first();

            if ($existing) {
                return $existing;
            }

            $lockedGame = Game::query()->whereKey($game->id)->lockForUpdate()->firstOrFail();
            $this->validateGame($lockedGame, $stake);

            $wallet = Wallet::query()
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($wallet->balance < $stake) {
                throw ValidationException::withMessages([
                    'stake' => 'Insufficient virtual credits.',
                ]);
            }

            $seed = FairnessSeed::query()
                ->where('user_id', $user->id)
                ->where('active', true)
                ->lockForUpdate()
                ->first();

            if (! $seed) {
                $seed = $this->seeds->create($user);
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
                'user_id' => $user->id,
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
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'direction' => LedgerDirection::Debit,
                'amount' => $stake,
                'balance_after' => $wallet->balance,
                'type' => 'game_stake',
                'idempotency_key' => "{$user->id}:{$requestId}:stake",
                'reference_type' => GameEntry::class,
                'reference_id' => $entry->id,
                'metadata' => ['game' => $lockedGame->code],
            ]);

            if ($outcome->payout > 0) {
                $wallet->balance += $outcome->payout;
                $wallet->save();

                LedgerEntry::query()->create([
                    'user_id' => $user->id,
                    'wallet_id' => $wallet->id,
                    'direction' => LedgerDirection::Credit,
                    'amount' => $outcome->payout,
                    'balance_after' => $wallet->balance,
                    'type' => 'game_payout',
                    'idempotency_key' => "{$user->id}:{$requestId}:payout",
                    'reference_type' => GameEntry::class,
                    'reference_id' => $entry->id,
                    'metadata' => ['game' => $lockedGame->code],
                ]);
            }

            $seed->increment('nonce');

            return $entry->fresh(['game']);
        }, attempts: 3);
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
