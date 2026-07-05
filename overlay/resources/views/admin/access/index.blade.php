@extends('layouts.app')
@section('title', 'Admin access')
@section('content')
<div class="page-head"><div><span class="eyebrow">SUPER ADMIN</span><h1>Administrator access</h1><p class="hint">Assign the minimum role required. You cannot change your own role from this page.</p></div></div>
<section class="panel">
<form method="get" class="filter-grid users-filter"><label>Search<input type="search" name="q" value="{{ request('q') }}" placeholder="Name or email"></label><div class="filter-action"><button class="button secondary" type="submit">Filter</button></div></form>
<div class="role-legend">
    <article><strong>Operations</strong><span>Games, users, credits, reports, promotions, support and system backups.</span></article>
    <article><strong>Support</strong><span>Player lookup and support ticket handling only.</span></article>
    <article><strong>Analyst</strong><span>Read-only analytics, play history and audit logs.</span></article>
</div>
<div class="table-wrap"><table>
<thead><tr><th>User</th><th>Current access</th><th>Two-factor</th><th>Assign role</th></tr></thead>
<tbody>
@foreach($users as $user)
<tr>
    <td><strong>{{ $user->name }}</strong><br><small>{{ $user->email }}</small></td>
    <td>{{ $user->resolvedAdminRole() ? ($roles[$user->resolvedAdminRole()] ?? $user->resolvedAdminRole()) : 'Player' }}</td>
    <td>{{ $user->hasTwoFactorEnabled() ? 'Enabled' : 'Not enabled' }}</td>
    <td>
        @if($user->is(auth()->user()))
            <span class="hint">Current account — protected</span>
        @else
        <form method="post" action="{{ route('admin.access.update', $user) }}" class="inline-role-form">
            @csrf @method('put')
            <select name="admin_role">
                <option value="">Player / no admin access</option>
                @foreach($roles as $value => $label)<option value="{{ $value }}" @selected($user->resolvedAdminRole() === $value)>{{ $label }}</option>@endforeach
            </select>
            <button class="button secondary small" type="submit">Update</button>
        </form>
        @endif
    </td>
</tr>
@endforeach
</tbody></table></div>
{{ $users->links() }}
</section>
@endsection
