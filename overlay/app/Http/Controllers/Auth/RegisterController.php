<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FairnessSeedService;
use App\Services\ReferralCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(Request $request): View
    {
        return view('auth.register', [
            'referralCode' => $request->string('ref')->trim()->upper()->toString(),
        ]);
    }

    public function store(
        Request $request,
        FairnessSeedService $seeds,
        ReferralCodeService $referralCodes,
    ): RedirectResponse {
        $referralCode = strtoupper(trim((string) $request->input('referral_code', '')));
        $request->merge([
            'referral_code' => $referralCode !== '' ? $referralCode : null,
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(10)->letters()->numbers()],
            'referral_code' => [
                'nullable',
                'string',
                'max:20',
                Rule::exists('users', 'referral_code')->where(fn ($query) => $query->where('is_admin', false)),
            ],
        ]);

        $user = DB::transaction(function () use ($data, $seeds, $referralCodes): User {
            $inviter = filled($data['referral_code'] ?? null)
                ? User::query()->where('referral_code', strtoupper($data['referral_code']))->where('is_admin', false)->first()
                : null;

            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'referred_by_user_id' => $inviter?->id,
            ]);
            $referralCodes->ensure($user);
            $user->wallet()->create(['balance' => 10000]);
            $seeds->create($user);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
