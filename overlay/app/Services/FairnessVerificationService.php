<?php

namespace App\Services;

use App\Models\GameEntry;

final class FairnessVerificationService
{
    public function __construct(
        private readonly ProvablyFairService $fairness,
        private readonly GameEngineRegistry $engines,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function verify(GameEntry $entry): array
    {
        $seed = $entry->fairnessSeed;

        if (! $seed || ! $seed->revealed_server_seed) {
            return [
                'verified' => false,
                'reason' => 'The server seed is still hidden. Rotate the active seed first.',
            ];
        }

        $hashMatches = hash_equals(
            $entry->server_seed_hash,
            $this->fairness->hashServerSeed($seed->revealed_server_seed),
        );

        $recomputed = $this->engines->for($entry->game->code)->play(
            stake: $entry->stake,
            bet: $entry->bet,
            serverSeed: $seed->revealed_server_seed,
            clientSeed: $entry->client_seed,
            nonce: $entry->nonce,
        );

        $resultMatches = $recomputed->result == array_diff_key($entry->result, ['won' => true]);
        $winMatches = $recomputed->won === (bool) ($entry->result['won'] ?? false);
        $payoutMatches = $recomputed->payout === $entry->payout;

        return [
            'verified' => $hashMatches && $resultMatches && $winMatches && $payoutMatches,
            'hash_matches' => $hashMatches,
            'result_matches' => $resultMatches,
            'win_matches' => $winMatches,
            'payout_matches' => $payoutMatches,
            'stored_result' => $entry->result,
            'recomputed_result' => $recomputed->result + ['won' => $recomputed->won],
            'stored_payout' => $entry->payout,
            'recomputed_payout' => $recomputed->payout,
        ];
    }
}
