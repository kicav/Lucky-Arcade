<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LiveEvent;
use App\Models\SupportTicket;
use App\Services\PresenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class LiveOperationsController extends Controller
{
    public function index(PresenceService $presence): View
    {
        return view('admin.live.index', $this->snapshot($presence));
    }

    public function data(PresenceService $presence): JsonResponse
    {
        $snapshot = $this->snapshot($presence);

        return response()->json([
            'online_users' => $snapshot['onlineUsers']->map(fn ($presence): array => [
                'id' => $presence->user_id,
                'name' => $presence->user?->name ?? 'Unknown',
                'email' => $presence->user?->email ?? '',
                'role' => $presence->user?->is_admin ? ($presence->user?->resolvedAdminRole() ?? 'admin') : 'player',
                'path' => $presence->current_path,
                'last_seen_at' => $presence->last_seen_at?->toIso8601String(),
            ])->values(),
            'recent_events' => $snapshot['recentEvents']->map(fn (LiveEvent $event): array => [
                'id' => $event->id,
                'audience' => $event->audience,
                'topic' => $event->topic,
                'type' => $event->event_type,
                'created_at' => $event->created_at?->toIso8601String(),
            ])->values(),
            'open_tickets' => $snapshot['openTickets'],
            'pending_tickets' => $snapshot['pendingTickets'],
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    /** @return array<string, mixed> */
    private function snapshot(PresenceService $presence): array
    {
        return [
            'onlineUsers' => $presence->online(),
            'recentEvents' => LiveEvent::query()
                ->where(function ($query): void {
                    $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->latest('id')
                ->limit(40)
                ->get(),
            'openTickets' => SupportTicket::query()->where('status', 'open')->count(),
            'pendingTickets' => SupportTicket::query()->where('status', 'pending')->count(),
        ];
    }
}
