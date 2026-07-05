<?php

namespace App\Http\Controllers;

use App\Actions\Rewards\ClaimMissionRewardAction;
use App\Models\UserMission;
use App\Services\MissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MissionController extends Controller
{
    public function index(Request $request, MissionService $missions): View
    {
        return view('missions.index', [
            'missions' => $missions->sync($request->user()),
            'wallet' => $request->user()->wallet,
        ]);
    }

    public function claim(
        Request $request,
        UserMission $mission,
        ClaimMissionRewardAction $action,
    ): RedirectResponse {
        $claimed = $action->execute($request->user(), $mission);

        return back()->with('success', "Claimed {$claimed->reward} mission credits.");
    }
}
