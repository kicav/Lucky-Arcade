<?php

namespace App\Services;

use App\Models\LiveEvent;
use App\Models\User;
use Illuminate\Support\Collection;

final class LiveEventService
{
    /** @param array<string, mixed> $payload */
    public function publishForUser(User|int $user, string $eventType, array $payload = [], string $topic = 'user', ?int $ttlSeconds = null): LiveEvent
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $this->publish('user', $userId, $topic, $eventType, $payload, $ttlSeconds);
    }

    /** @param array<string, mixed> $payload */
    public function publishAdmin(string $eventType, array $payload = [], string $topic = 'admin', ?int $ttlSeconds = null): LiveEvent
    {
        return $this->publish('admin', null, $topic, $eventType, $payload, $ttlSeconds);
    }

    /** @param array<string, mixed> $payload */
    public function publishPublic(string $eventType, array $payload = [], string $topic = 'public', ?int $ttlSeconds = null): LiveEvent
    {
        return $this->publish('public', null, $topic, $eventType, $payload, $ttlSeconds);
    }

    /** @return Collection<int, LiveEvent> */
    public function feed(User $user, int $afterId, int $limit = 50): Collection
    {
        $limit = max(1, min(100, $limit));

        return $this->visibleQuery($user)
            ->where('id', '>', max(0, $afterId))
            ->orderBy('id')
            ->limit($limit)
            ->get();
    }

    public function latestVisibleId(User $user): int
    {
        return (int) ($this->visibleQuery($user)->max('id') ?? 0);
    }

    public function prune(): int
    {
        return LiveEvent::query()
            ->where(function ($query): void {
                $query->where('expires_at', '<', now())
                    ->orWhere('created_at', '<', now()->subDay());
            })
            ->delete();
    }

    /** @param array<string, mixed> $payload */
    private function publish(string $audience, ?int $userId, string $topic, string $eventType, array $payload, ?int $ttlSeconds): LiveEvent
    {
        $ttl = $ttlSeconds ?? (int) config('live.event_ttl_seconds', 21600);

        return LiveEvent::query()->create([
            'audience' => $audience,
            'user_id' => $userId,
            'topic' => $topic,
            'event_type' => $eventType,
            'payload' => $payload,
            'expires_at' => now()->addSeconds(max(300, $ttl)),
            'created_at' => now(),
        ]);
    }

    private function visibleQuery(User $user)
    {
        $canSeeAdmin = $user->is_admin && (
            $user->canAccessAdminArea('support') ||
            $user->canAccessAdminArea('system') ||
            $user->canAccessAdminArea('live')
        );

        return LiveEvent::query()
            ->where(function ($query) use ($user, $canSeeAdmin): void {
                $query->where('audience', 'public')
                    ->orWhere(function ($userQuery) use ($user): void {
                        $userQuery->where('audience', 'user')->where('user_id', $user->id);
                    });

                if ($canSeeAdmin) {
                    $query->orWhere('audience', 'admin');
                }
            })
            ->where(function ($query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }
}
