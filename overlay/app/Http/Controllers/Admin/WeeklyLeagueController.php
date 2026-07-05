<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Rewards\SettleWeeklyLeagueAction;
use App\Http\Controllers\Controller;
use App\Models\WeeklyLeagueSettlement;
use App\Services\WeeklyLeagueService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WeeklyLeagueController extends Controller
{
    public function index(WeeklyLeagueService $league): View
    {
        $weekStart = $league->previousWeekStart();

        return view('admin.league.index', [
            'weekStart' => $weekStart,
            'weekEnd' => $weekStart->copy()->addDays(6),
            'standings' => $league->standings($weekStart, 25),
            'rewards' => $league->rewards(),
            'settlement' => WeeklyLeagueSettlement::query()->whereDate('week_start', $weekStart->toDateString())->first(),
        ]);
    }

    public function settle(Request $request, SettleWeeklyLeagueAction $action): RedirectResponse
    {
        $settlement = $action->execute($request->user(), $request);

        return back()->with('success', 'Weekly League settled at '.$settlement->settled_at?->format('Y-m-d H:i').'.');
    }
}
