<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserPresence;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final class PresenceService
{
    public function touch(User $user, Request $request): void
    {
        $cacheKey = 'live-presence-touch:'.$user->id;
        if (! Cache::add($cacheKey, true, 20)) {
            return;
        }

        UserPresence::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'current_path' => mb_substr('/'.ltrim($request->path(), '/'), 0, 500),
                'ip_address' => $request->ip(),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
                'last_seen_at' => now(),
            ],
        );
    }

    /** @return Collection<int, UserPresence> */
    public function online(?int $windowMinutes = null): Collection
    {
        $minutes = $windowMinutes ?? (int) config('live.presence_window_minutes', 5);

        return UserPresence::query()
            ->with('user:id,name,email,is_admin,admin_role')
            ->where('last_seen_at', '>=', now()->subMinutes(max(1, $minutes)))
            ->latest('last_seen_at')
            ->get();
    }

    public function prune(int $olderThanMinutes = 1440): int
    {
        return UserPresence::query()
            ->where('last_seen_at', '<', now()->subMinutes(max(60, $olderThanMinutes)))
            ->delete();
    }
}
