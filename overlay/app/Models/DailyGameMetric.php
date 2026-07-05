<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyGameMetric extends Model
{
    protected $fillable = [
        'metric_date', 'game_id', 'plays', 'wins', 'total_stake', 'total_payout', 'system_net',
    ];

    protected function casts(): array
    {
        return [
            'metric_date' => 'date',
            'plays' => 'integer',
            'wins' => 'integer',
            'total_stake' => 'integer',
            'total_payout' => 'integer',
            'system_net' => 'integer',
        ];
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}
