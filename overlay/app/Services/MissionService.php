<?php

namespace App\Services;

use App\Models\GameEntry;
use App\Models\User;
use App\Models\UserMission;
use Illuminate\Support\Collection;

final class MissionService
{
    /** @return array<string, array{title:string,description:string,target:int,reward:int,metric:string}> */
    public function definitions(): array
    {
        return [
            'play_3' => [
                'title' => 'Warm-up round',
                'description' => 'Complete 3 game rounds today.',
                'target' => 3,
                'reward' => 100,
                'metric' => 'plays',
            ],
            'wager_100' => [
                'title' => 'Century wager',
                'description' => 'Stake a total of 100 virtual credits today.',
                'target' => 100,
                'reward' => 150,
                'metric' => 'stake',
            ],
            'win_1' => [
                'title' => 'Find a winner',
                'description' => 'Finish one round with positive net credits.',
                'target' => 1,
                'reward' => 100,
                'metric' => 'wins',
            ],
            'explore_2' => [
                'title' => 'Game explorer',
                'description' => 'Play two different games today.',
                'target' => 2,
                'reward' => 150,
                'metric' => 'unique_games',
            ],
        ];
    }

    /** @return Collection<int, UserMission> */
    public function sync(User $user): Collection
    {
        $missionDate = today()->toDateString();

        $entries = GameEntry::query()
            ->where('user_id', $user->id)
            ->whereDate('created_at', $missionDate)
            ->get(['game_id', 'stake', 'net']);

        $metrics = [
            'plays' => $entries->count(),
            'stake' => (int) $entries->sum('stake'),
            'wins' => $entries->where('net', '>', 0)->count(),
            'unique_games' => $entries->pluck('game_id')->unique()->count(),
        ];

        foreach ($this->definitions() as $key => $definition) {
            // A direct firstOrCreate() comparison on a date-cast attribute can use
            // different SQLite representations (Y-m-d versus Y-m-d 00:00:00).
            // Querying with whereDate() and using insertOrIgnore() keeps this path
            // idempotent and safe when sync() is called repeatedly.
            $mission = UserMission::query()
                ->where('user_id', $user->id)
                ->where('mission_key', $key)
                ->whereDate('mission_date', $missionDate)
                ->first();

            if (! $mission) {
                $timestamp = now();

                UserMission::query()->insertOrIgnore([
                    'user_id' => $user->id,
                    'mission_date' => $missionDate,
                    'mission_key' => $key,
                    'title' => $definition['title'],
                    'description' => $definition['description'],
                    'progress' => 0,
                    'target' => $definition['target'],
                    'reward' => $definition['reward'],
                    'completed_at' => null,
                    'claimed_at' => null,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);

                $mission = UserMission::query()
                    ->where('user_id', $user->id)
                    ->where('mission_key', $key)
                    ->whereDate('mission_date', $missionDate)
                    ->firstOrFail();
            }

            $progress = min($definition['target'], (int) $metrics[$definition['metric']]);
            $mission->progress = $progress;

            if ($progress >= $definition['target'] && $mission->completed_at === null) {
                $mission->completed_at = now();
            }

            $mission->save();
        }

        return UserMission::query()
            ->where('user_id', $user->id)
            ->whereDate('mission_date', $missionDate)
            ->orderBy('id')
            ->get();
    }

    public function recordPlay(User $user): void
    {
        $this->sync($user);
    }
}
