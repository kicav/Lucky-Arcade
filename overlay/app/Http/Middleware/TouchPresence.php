<?php

namespace App\Http\Middleware;

use App\Services\PresenceService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class TouchPresence
{
    public function __construct(private readonly PresenceService $presence)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->user()) {
            try {
                $this->presence->touch($request->user(), $request);
            } catch (Throwable) {
                // Presence must never prevent a normal application response.
            }
        }

        return $response;
    }
}
