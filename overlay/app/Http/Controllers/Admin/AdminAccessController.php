<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminAccessController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()->orderByDesc('is_admin')->orderBy('email');
        if ($search = trim((string) $request->query('q'))) {
            $query->where(fn ($builder) => $builder
                ->where('email', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%"));
        }

        return view('admin.access.index', [
            'users' => $query->paginate(30)->withQueryString(),
            'roles' => User::ADMIN_ROLES,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_if($user->is($request->user()), 422, 'You cannot change your own administrator access.');

        $data = $request->validate([
            'admin_role' => ['nullable', Rule::in(array_keys(User::ADMIN_ROLES))],
        ]);

        $before = ['is_admin' => $user->is_admin, 'admin_role' => $user->resolvedAdminRole()];
        $role = $data['admin_role'] ?? null;
        $user->forceFill(['is_admin' => $role !== null, 'admin_role' => $role])->save();

        AuditLog::query()->create([
            'actor_id' => $request->user()->id,
            'action' => 'admin_access.updated',
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'before' => $before,
            'after' => ['is_admin' => $user->is_admin, 'admin_role' => $user->resolvedAdminRole()],
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            'created_at' => now(),
        ]);

        return back()->with('success', $role ? 'Administrator role updated.' : 'Administrator access revoked.');
    }
}
