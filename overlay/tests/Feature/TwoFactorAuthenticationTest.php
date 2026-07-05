<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\TotpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TwoFactorAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_enabled_two_factor_requires_a_challenge_before_login(): void
    {
        $totp = app(TotpService::class);
        $secret = 'JBSWY3DPEHPK3PXP';
        $user = User::factory()->create([
            'email' => 'secure@example.com',
            'password' => Hash::make('Secret12345'),
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => [],
            'two_factor_confirmed_at' => now(),
        ]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'Secret12345',
        ])->assertRedirect(route('two-factor.challenge'));

        $this->assertGuest();

        $this->post(route('two-factor.challenge.store'), [
            'code' => $totp->currentCode($secret),
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('security_events', [
            'user_id' => $user->id,
            'event' => 'login.success',
        ]);
    }

    public function test_a_user_can_enable_two_factor_from_the_security_center(): void
    {
        $user = User::factory()->create(['password' => Hash::make('Secret12345')]);

        $this->actingAs($user)->post(route('security.two-factor.begin'), [
            'current_password' => 'Secret12345',
        ])->assertRedirect();

        $secret = session('two_factor_setup_secret');
        $this->assertNotEmpty($secret);

        $this->actingAs($user)->post(route('security.two-factor.confirm'), [
            'code' => app(TotpService::class)->currentCode($secret),
        ])->assertRedirect();

        $this->assertTrue($user->fresh()->hasTwoFactorEnabled());
    }
}
