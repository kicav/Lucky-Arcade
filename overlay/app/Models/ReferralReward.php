<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralReward extends Model
{
    protected $fillable = [
        'inviter_user_id', 'referred_user_id', 'triggered_by_entry_id',
        'inviter_amount', 'referred_amount',
    ];

    protected function casts(): array
    {
        return [
            'inviter_amount' => 'integer',
            'referred_amount' => 'integer',
        ];
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inviter_user_id');
    }

    public function referredUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    public function triggeringEntry(): BelongsTo
    {
        return $this->belongsTo(GameEntry::class, 'triggered_by_entry_id');
    }
}
