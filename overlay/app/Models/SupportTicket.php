<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    protected $fillable = [
        'user_id', 'subject', 'category', 'priority', 'status', 'last_replied_at', 'closed_at',
    ];

    protected function casts(): array
    {
        return ['last_replied_at' => 'datetime', 'closed_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportMessage::class)->orderBy('id');
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }
}
