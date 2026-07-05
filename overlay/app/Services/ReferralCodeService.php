<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

final class ReferralCodeService
{
    public function ensure(User $user): string
    {
        if ($user->referral_code) {
            return $user->referral_code;
        }

        do {
            $code = 'LA'.Str::upper(Str::random(10));
        } while (User::query()->where('referral_code', $code)->exists());

        $user->forceFill(['referral_code' => $code])->save();

        return $code;
    }
}
