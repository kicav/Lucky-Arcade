<?php

namespace App\Http\Controllers;

use App\Models\LiveEvent;
use App\Services\LiveEventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LiveFeedController extends Controller
{
    public function __invoke(Request $request, LiveEventService $live): JsonResponse
    {
        $data = $request->validate([
            'after' => ['nullable', 'integer', 'min:0'],
        ]);
        $after = (int) ($data['after'] ?? 0);

        if ($after === 0) {
            return response()->json([
                'events' => [],
                'next_after' => $live->latestVisibleId($request->user()),
                'unread_notifications' => $request->user()->userNotifications()->whereNull('read_at')->count(),
                'server_time' => now()->toIso8601String(),
            ]);
        }

        $events = $live->feed($request->user(), $after);

        return response()->json([
            'events' => $events->map(fn (LiveEvent $event): array => [
                'id' => $event->id,
                'topic' => $event->topic,
                'type' => $event->event_type,
                'payload' => $event->payload ?? [],
                'created_at' => $event->created_at?->toIso8601String(),
            ])->values(),
            'next_after' => (int) ($events->last()?->id ?? $after),
            'unread_notifications' => $request->user()->userNotifications()->whereNull('read_at')->count(),
            'server_time' => now()->toIso8601String(),
        ]);
    }
}
