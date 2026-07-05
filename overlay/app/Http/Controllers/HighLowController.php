<?php

namespace App\Http\Controllers;

use App\Actions\Games\PlaceBetAction;
use App\Http\Requests\PlayHighLowRequest;
use App\Models\Game;
use Illuminate\Http\RedirectResponse;

class HighLowController extends Controller
{
    public function store(
        PlayHighLowRequest $request,
        Game $game,
        PlaceBetAction $action,
    ): RedirectResponse {
        abort_unless($game->code === 'highlow', 404);

        $entry = $action->execute(
            user: $request->user(),
            game: $game,
            stake: $request->integer('stake'),
            bet: ['selection' => $request->string('selection')->toString()],
            requestId: $request->string('request_id')->toString(),
        );

        $rank = (string) $entry->result['rank_label'];
        $suit = ucfirst((string) $entry->result['suit']);
        $push = (bool) ($entry->result['push'] ?? false);

        $message = $push
            ? "Push. The card was {$rank} of {$suit}; your {$entry->stake} credits were returned."
            : (($entry->result['won'] ?? false)
                ? "You won {$entry->payout} credits. The card was {$rank} of {$suit}."
                : "You lost. The card was {$rank} of {$suit}.");

        return back()->with([
            'result' => $message,
            'game_result' => [
                'game' => $game->code,
                'won' => (bool) ($entry->result['won'] ?? false),
                'push' => $push,
                'payout' => $entry->payout,
                'result' => $entry->result,
            ],
        ]);
    }
}
