<?php

namespace App\Http\Controllers;

use App\Services\ReferralCodeService;
use App\Services\ReferralRewardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReferralController extends Controller
{
    public function __invoke(Request $request, ReferralCodeService $codes): View
    {
        $user = $request->user();
        $code = $codes->ensure($user);

        return view('referrals.index', [
            'code' => $code,
            'referralUrl' => route('register', ['ref' => $code]),
            'invitedUsers' => $user->referredUsers()->with('wallet')->latest()->get(),
            'rewards' => $user->referralRewards()->with('referredUser:id,name,email')->latest()->get(),
            'inviterAmount' => ReferralRewardService::INVITER_AMOUNT,
            'referredAmount' => ReferralRewardService::REFERRED_AMOUNT,
        ]);
    }
}
