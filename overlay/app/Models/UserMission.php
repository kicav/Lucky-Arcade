<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserMission extends Model
{
    protected $fillable = [
        'user_id', 'mission_key', 'title', 'description', 'mission_date',
        'progress', 'target', 'reward', 'completed_at', 'claimed_at',
    ];

    protected function casts(): array
    {
        return [
            'mission_date' => 'date',
            'progress' => 'integer',
            'target' => 'integer',
            'reward' => 'integer',
            'completed_at' => 'datetime',
            'claimed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isComplete(): bool
    {
        return $this->progress >= $this->target;
    }
}
