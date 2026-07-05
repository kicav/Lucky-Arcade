<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SecurityEvent;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SecurityEventController extends Controller
{
    public function index(Request $request): View
    {
        $query = SecurityEvent::query()->with('user')->latest('created_at');

        if ($request->filled('event')) {
            $query->where('event', 'like', '%'.str_replace(['%', '_'], ['\\%', '\\_'], $request->string('event')->trim()->toString()).'%');
        }
        if ($request->filled('email')) {
            $email = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $request->string('email')->trim()->toString()).'%';
            $query->whereHas('user', fn ($builder) => $builder->where('email', 'like', $email));
        }
        if ($request->filled('ip')) {
            $query->where('ip_address', $request->string('ip')->trim()->toString());
        }

        return view('admin.security-events.index', [
            'events' => $query->paginate(50)->withQueryString(),
        ]);
    }
}
