<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Services\FairnessSeedService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class GameController extends Controller
{
    public function index(): View
    {
        return view('games.index', [
            'games' => Game::query()->where('enabled', true)->get(),
        ]);
    }

    public function show(Request $request, Game $game, FairnessSeedService $seeds): View
    {
        abort_unless($game->enabled, 404);
        abort_unless(in_array($game->code, ['dice', 'roulette', 'coinflip', 'highlow'], true), 404);

        return view('games.'.$game->code, [
            'game' => $game,
            'wallet' => $request->user()->wallet,
            'seed' => $seeds->active($request->user()),
            'requestId' => (string) Str::uuid(),
        ]);
    }
}
