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

    protected $fillable = [
        'name', 'email', 'password', 'is_admin', 'referral_code', 'referred_by_user_id', 'daily_stake_limit',
        'self_excluded_until', 'suspended_at', 'suspension_reason',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'daily_stake_limit' => 'integer',
            'self_excluded_until' => 'datetime',
            'suspended_at' => 'datetime',
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

    public function isSelfExcluded(): bool
    {
        return $this->self_excluded_until !== null && $this->self_excluded_until->isFuture();
    }

    public function isSuspended(): bool
    {
        return $this->suspended_at !== null;
    }
}
