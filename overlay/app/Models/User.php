<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'is_admin', 'daily_stake_limit',
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

    public function isSelfExcluded(): bool
    {
        return $this->self_excluded_until !== null && $this->self_excluded_until->isFuture();
    }

    public function isSuspended(): bool
    {
        return $this->suspended_at !== null;
    }
}
