<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameEntry;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'userCount' => User::query()->where('is_admin', false)->count(),
            'gameCount' => Game::query()->count(),
            'entryCount' => GameEntry::query()->count(),
            'totalStake' => GameEntry::query()->sum('stake'),
            'totalPayout' => GameEntry::query()->sum('payout'),
            'latestEntries' => GameEntry::query()->with(['user', 'game'])->latest()->limit(12)->get(),
        ]);
    }
}
