<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameEntry;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EntryController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'email' => ['nullable', 'string', 'max:255'],
            'game' => ['nullable', 'string', 'max:50'],
            'outcome' => ['nullable', 'in:win,loss'],
        ]);

        $entries = GameEntry::query()
            ->with(['user:id,name,email', 'game:id,code,name'])
            ->when($filters['email'] ?? null, function ($query, string $email): void {
                $query->whereHas('user', fn ($userQuery) => $userQuery->where('email', 'like', "%{$email}%"));
            })
            ->when($filters['game'] ?? null, fn ($query, string $game) => $query->whereHas('game', fn ($gameQuery) => $gameQuery->where('code', $game)))
            ->when(($filters['outcome'] ?? null) === 'win', fn ($query) => $query->where('payout', '>', 0))
            ->when(($filters['outcome'] ?? null) === 'loss', fn ($query) => $query->where('payout', '=', 0))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.entries.index', [
            'entries' => $entries,
            'games' => Game::query()->orderBy('name')->get(['code', 'name']),
            'filters' => $filters,
        ]);
    }
}
