<?php

namespace App\Http\Controllers;

use App\Services\AchievementService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AchievementController extends Controller
{
    public function __invoke(Request $request, AchievementService $achievements): View
    {
        $user = $request->user();

        return view('achievements.index', [
            'catalog' => $achievements->catalog(),
            'progress' => $achievements->progress($user),
            'unlocked' => $user->achievements()->get()->keyBy('code'),
        ]);
    }
}
