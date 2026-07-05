<?php

namespace App\Http\Controllers;

use App\Actions\Rewards\ClaimDailyRewardAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DailyRewardController extends Controller
{
    public function store(
        Request $request,
        ClaimDailyRewardAction $action,
    ): RedirectResponse {
        $reward = $action->execute($request->user());

        return back()->with('success', "Daily reward claimed: {$reward->amount} virtual credits.");
    }
}
