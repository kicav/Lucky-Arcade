<?php

namespace App\Http\Controllers;

use App\Http\Requests\RotateFairnessSeedRequest;
use App\Services\FairnessSeedService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FairnessController extends Controller
{
    public function show(Request $request, FairnessSeedService $seeds): View
    {
        return view('fairness.show', [
            'activeSeed' => $seeds->active($request->user()),
            'oldSeeds' => $request->user()->fairnessSeeds()
                ->where('active', false)
                ->latest('revealed_at')
                ->limit(20)
                ->get(),
        ]);
    }

    public function rotate(
        RotateFairnessSeedRequest $request,
        FairnessSeedService $seeds,
    ): RedirectResponse {
        $seeds->rotate($request->user(), $request->input('client_seed'));

        return back()->with('success', 'Seed rotated. The previous server seed is now revealed.');
    }
}
