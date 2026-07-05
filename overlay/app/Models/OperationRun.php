<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperationRun extends Model
{
    protected $fillable = [
        'task', 'status', 'started_at', 'finished_at', 'duration_ms', 'details', 'error_message',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'duration_ms' => 'integer',
            'details' => 'array',
        ];
    }
}
