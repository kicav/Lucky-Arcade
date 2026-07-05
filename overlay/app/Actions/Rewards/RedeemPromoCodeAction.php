<?php

namespace App\Actions\Rewards;

use App\Enums\LedgerDirection;
use App\Models\LedgerEntry;
use App\Models\PromoCode;
use App\Models\PromoCodeRedemption;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class RedeemPromoCodeAction
{
    public function execute(User $user, string $rawCode): PromoCodeRedemption
    {
        $code = mb_strtoupper(trim($rawCode));

        return DB::transaction(function () use ($user, $code): PromoCodeRedemption {
            $promo = PromoCode::query()->where('code', $code)->lockForUpdate()->first();

            if (! $promo || ! $promo->isAvailable()) {
                throw ValidationException::withMessages(['code' => 'This promo code is invalid, inactive, expired or fully redeemed.']);
            }

            $existing = PromoCodeRedemption::query()
                ->where('promo_code_id', $promo->id)
                ->where('user_id', $user->id)
                ->first();

            if ($existing) {
                throw ValidationException::withMessages(['code' => 'You have already redeemed this promo code.']);
            }

            $wallet = Wallet::query()->where('user_id', $user->id)->lockForUpdate()->firstOrFail();
            $redemption = PromoCodeRedemption::query()->create([
                'promo_code_id' => $promo->id,
                'user_id' => $user->id,
                'credits' => $promo->credits,
                'redeemed_at' => now(),
            ]);

            $wallet->balance += $promo->credits;
            $wallet->save();

            LedgerEntry::query()->create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'direction' => LedgerDirection::Credit,
                'amount' => $promo->credits,
                'balance_after' => $wallet->balance,
                'type' => 'promo_code_reward',
                'idempotency_key' => "promo:{$promo->id}:user:{$user->id}",
                'reference_type' => PromoCodeRedemption::class,
                'reference_id' => $redemption->id,
                'metadata' => ['promo_code' => $promo->code],
            ]);

            $promo->increment('redemptions_count');

            UserNotification::query()->create([
                'user_id' => $user->id,
                'type' => 'promo_code',
                'title' => 'Promo code redeemed',
                'message' => "You received {$promo->credits} virtual credits from {$promo->code}.",
                'data' => ['promo_code_id' => $promo->id, 'credits' => $promo->credits],
            ]);

            return $redemption;
        }, attempts: 3);
    }
}
