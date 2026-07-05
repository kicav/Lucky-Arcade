<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PlayerStatsController extends Controller
{
    public function __invoke(Request $request): View
    {
        $entries = $request->user()->gameEntries()->with('game')->oldest()->get();

        $summary = [
            'plays' => $entries->count(),
            'wins' => $entries->where('net', '>', 0)->count(),
            'stake' => (int) $entries->sum('stake'),
            'payout' => (int) $entries->sum('payout'),
            'net' => (int) $entries->sum('net'),
            'best_win' => (int) ($entries->max('net') ?? 0),
        ];

        $perGame = $entries
            ->groupBy(fn ($entry): string => $entry->game->name)
            ->map(function (Collection $group, string $name): array {
                $plays = $group->count();
                $stake = (int) $group->sum('stake');
                $payout = (int) $group->sum('payout');

                return [
                    'name' => $name,
                    'plays' => $plays,
                    'wins' => $group->where('net', '>', 0)->count(),
                    'stake' => $stake,
                    'payout' => $payout,
                    'net' => $payout - $stake,
                    'rtp' => $stake > 0 ? round(($payout / $stake) * 100, 2) : 0,
                ];
            })
            ->sortByDesc('plays')
            ->values();

        $start = today()->subDays(13);
        $recent = $entries->filter(fn ($entry): bool => $entry->created_at->greaterThanOrEqualTo($start));
        $daily = collect(range(0, 13))->map(function (int $offset) use ($start, $recent): array {
            $date = $start->copy()->addDays($offset);
            $group = $recent->filter(fn ($entry): bool => $entry->created_at->isSameDay($date));

            return [
                'date' => $date->format('M j'),
                'plays' => $group->count(),
                'net' => (int) $group->sum('net'),
            ];
        });

        return view('stats.index', compact('summary', 'perGame', 'daily'));
    }
}
