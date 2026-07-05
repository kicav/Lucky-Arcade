<?php

namespace App\Http\Controllers;

use App\Actions\Rewards\ClaimDailyRewardAction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        return view('dashboard', [
            'wallet' => $user->wallet,
            'entries' => $user->gameEntries()->with('game')->latest()->limit(10)->get(),
            'ledger' => $user->ledgerEntries()->latest()->limit(10)->get(),
            'dailyRewardAmount' => ClaimDailyRewardAction::AMOUNT,
            'dailyRewardClaimed' => $user->dailyRewards()->whereDate('reward_date', today())->exists(),
        ]);
    }
}
