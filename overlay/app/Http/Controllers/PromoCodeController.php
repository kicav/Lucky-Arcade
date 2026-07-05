<?php

namespace App\Http\Controllers;

use App\Actions\Rewards\RedeemPromoCodeAction;
use App\Models\PromoCodeRedemption;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromoCodeController extends Controller
{
    public function index(Request $request): View
    {
        return view('promos.index', [
            'redemptions' => PromoCodeRedemption::query()
                ->with('promoCode')
                ->where('user_id', $request->user()->id)
                ->latest('redeemed_at')
                ->get(),
        ]);
    }

    public function redeem(Request $request, RedeemPromoCodeAction $action): RedirectResponse
    {
        $data = $request->validate(['code' => ['required', 'string', 'max:40']]);
        $redemption = $action->execute($request->user(), $data['code']);

        return back()->with('success', "Promo redeemed: +{$redemption->credits} virtual credits.");
    }
}
