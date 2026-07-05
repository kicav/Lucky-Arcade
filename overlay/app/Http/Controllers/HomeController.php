<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('home', [
            'games' => Game::query()->where('enabled', true)->get(),
        ]);
    }
}
