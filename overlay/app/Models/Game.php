<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    protected $fillable = [
        'code', 'name', 'description', 'enabled', 'min_bet', 'max_bet', 'config',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'min_bet' => 'integer',
            'max_bet' => 'integer',
            'config' => 'array',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }

    public function entries(): HasMany
    {
        return $this->hasMany(GameEntry::class);
    }
}
