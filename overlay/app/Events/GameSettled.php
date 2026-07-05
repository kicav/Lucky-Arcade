<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class GameSettled
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly int $entryId)
    {
    }
}
