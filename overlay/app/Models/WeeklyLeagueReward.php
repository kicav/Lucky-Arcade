<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeeklyLeagueReward extends Model
{
    protected $fillable = ['week_start', 'user_id', 'rank', 'score', 'reward', 'awarded_at'];

    protected function casts(): array
    {
        return [
            'week_start' => 'date', 'rank' => 'integer', 'score' => 'integer',
            'reward' => 'integer', 'awarded_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
