<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ADMIN_ROLES = [
        'super_admin' => 'Super admin',
        'operations' => 'Operations',
        'support' => 'Support',
        'analyst' => 'Analyst',
    ];

    private const ADMIN_AREA_ACCESS = [
        'super_admin' => ['*'],
        'operations' => ['dashboard', 'analytics', 'games', 'users', 'user_actions', 'entries', 'audit', 'announcements', 'promos', 'support', 'league', 'system', 'live'],
        'support' => ['dashboard', 'users', 'support', 'live'],
        'analyst' => ['dashboard', 'analytics', 'entries', 'audit'],
    ];


    protected $fillable = [
        'name', 'email', 'password', 'is_admin', 'admin_role', 'referral_code', 'referred_by_user_id', 'daily_stake_limit',
        'self_excluded_until', 'suspended_at', 'suspension_reason', 'two_factor_secret',
        'two_factor_recovery_codes', 'two_factor_confirmed_at',
    ];

    protected $hidden = ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'daily_stake_limit' => 'integer',
            'self_excluded_until' => 'datetime',
            'suspended_at' => 'datetime',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:array',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function fairnessSeeds(): HasMany
    {
        return $this->hasMany(FairnessSeed::class);
    }

    public function gameEntries(): HasMany
    {
        return $this->hasMany(GameEntry::class);
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class);
    }

    public function dailyRewards(): HasMany
    {
        return $this->hasMany(DailyReward::class);
    }

    public function userNotifications(): HasMany
    {
        return $this->hasMany(UserNotification::class);
    }


    public function referrer(): BelongsTo
    {
        return $this->belongsTo(self::class, 'referred_by_user_id');
    }

    public function referredUsers(): HasMany
    {
        return $this->hasMany(self::class, 'referred_by_user_id');
    }

    public function referralRewards(): HasMany
    {
        return $this->hasMany(ReferralReward::class, 'inviter_user_id');
    }

    public function achievements(): HasMany
    {
        return $this->hasMany(UserAchievement::class);
    }

    public function missions(): HasMany
    {
        return $this->hasMany(UserMission::class);
    }

    public function promoRedemptions(): HasMany
    {
        return $this->hasMany(PromoCodeRedemption::class);
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function weeklyLeagueRewards(): HasMany
    {
        return $this->hasMany(WeeklyLeagueReward::class);
    }


    public function securityEvents(): HasMany
    {
        return $this->hasMany(SecurityEvent::class);
    }

    public function presence(): HasOne
    {
        return $this->hasOne(UserPresence::class);
    }

    public function liveEvents(): HasMany
    {
        return $this->hasMany(LiveEvent::class);
    }

    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_confirmed_at !== null && filled($this->two_factor_secret);
    }

    public function resolvedAdminRole(): ?string
    {
        if (! $this->is_admin) {
            return null;
        }

        return array_key_exists((string) $this->admin_role, self::ADMIN_ROLES)
            ? $this->admin_role
            : 'super_admin';
    }

    public function canAccessAdminArea(string $area): bool
    {
        $role = $this->resolvedAdminRole();
        if ($role === null) {
            return false;
        }

        $areas = self::ADMIN_AREA_ACCESS[$role] ?? [];

        return in_array('*', $areas, true) || in_array($area, $areas, true);
    }

    public function isSelfExcluded(): bool
    {
        return $this->self_excluded_until !== null && $this->self_excluded_until->isFuture();
    }

    public function isSuspended(): bool
    {
        return $this->suspended_at !== null;
    }
}
