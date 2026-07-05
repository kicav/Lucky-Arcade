<?php

namespace App\Http\Controllers;

use App\Services\WeeklyLeagueService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WeeklyLeagueController extends Controller
{
    public function __invoke(Request $request, WeeklyLeagueService $league): View
    {
        $weekStart = $league->currentWeekStart();
        $standings = $league->standings($weekStart, 25);

        return view('league.index', [
            'weekStart' => $weekStart,
            'weekEnd' => $weekStart->copy()->addDays(6),
            'standings' => $standings,
            'myStanding' => $league->userRank($standings, $request->user()->id),
            'rewards' => $league->rewards(),
            'rewardHistory' => $league->rewardHistory($request->user()->id),
        ]);
    }
}
