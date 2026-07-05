<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameEntry extends Model
{
    protected $fillable = [
        'user_id', 'game_id', 'fairness_seed_id', 'stake', 'payout', 'net',
        'bet', 'result', 'client_seed', 'nonce', 'server_seed_hash',
        'request_id', 'status',
    ];

    protected function casts(): array
    {
        return [
            'stake' => 'integer',
            'payout' => 'integer',
            'net' => 'integer',
            'bet' => 'array',
            'result' => 'array',
            'nonce' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function fairnessSeed(): BelongsTo
    {
        return $this->belongsTo(FairnessSeed::class);
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class, 'reference_id')
            ->where('reference_type', self::class);
    }
}
