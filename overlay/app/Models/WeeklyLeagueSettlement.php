<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeeklyLeagueSettlement extends Model
{
    protected $fillable = ['week_start', 'settled_by', 'settled_at'];

    protected function casts(): array
    {
        return ['week_start' => 'date', 'settled_at' => 'datetime'];
    }

    public function settler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'settled_by');
    }
}
