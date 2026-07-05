<?php

namespace App\Http\Controllers;

use App\Models\GameEntry;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LeaderboardController extends Controller
{
    public function __invoke(): View
    {
        $richest = Wallet::query()
            ->with('user:id,name')
            ->whereHas('user', fn ($query) => $query->where('is_admin', false))
            ->orderByDesc('balance')
            ->limit(20)
            ->get();

        $topWinners = GameEntry::query()
            ->select('user_id', DB::raw('SUM(net) as total_net'), DB::raw('COUNT(*) as plays'))
            ->with('user:id,name')
            ->whereHas('user', fn ($query) => $query->where('is_admin', false))
            ->groupBy('user_id')
            ->orderByDesc('total_net')
            ->limit(20)
            ->get();

        return view('leaderboard.index', compact('richest', 'topWinners'));
    }
}
