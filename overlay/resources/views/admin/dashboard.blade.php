@extends('layouts.app')
@section('title', 'Admin dashboard')
@section('content')
<div class="page-head">
    <div><span class="eyebrow">ADMINISTRATION · {{ strtoupper(str_replace('_', ' ', auth()->user()->resolvedAdminRole() ?? 'admin')) }}</span><h1>Operational overview</h1></div>
    <div class="dashboard-actions">
        @if(auth()->user()->canAccessAdminArea('analytics'))<a class="button secondary" href="{{ route('admin.analytics') }}">Analytics</a>@endif
        @if(auth()->user()->canAccessAdminArea('games'))<a class="button secondary" href="{{ route('admin.games.index') }}">Game settings</a>@endif
        @if(auth()->user()->canAccessAdminArea('users'))<a class="button secondary" href="{{ route('admin.users.index') }}">Users</a>@endif
        @if(auth()->user()->canAccessAdminArea('entries'))<a class="button secondary" href="{{ route('admin.entries.index') }}">Play history</a>@endif
        @if(auth()->user()->canAccessAdminArea('audit'))<a class="button secondary" href="{{ route('admin.audit.index') }}">Audit log</a>@endif
        @if(auth()->user()->canAccessAdminArea('audit'))<a class="button secondary" href="{{ route('admin.security-events.index') }}">Security events</a>@endif
        @if(auth()->user()->canAccessAdminArea('announcements'))<a class="button secondary" href="{{ route('admin.announcements.index') }}">Announcements</a>@endif
        @if(auth()->user()->canAccessAdminArea('promos'))<a class="button secondary" href="{{ route('admin.promos.index') }}">Promo codes</a>@endif
        @if(auth()->user()->canAccessAdminArea('support'))<a class="button secondary" href="{{ route('admin.support.index') }}">Support</a>@endif
        @if(auth()->user()->canAccessAdminArea('live'))<a class="button secondary" href="{{ route('admin.live.index') }}">Live operations</a>@endif
        @if(auth()->user()->canAccessAdminArea('league'))<a class="button secondary" href="{{ route('admin.league.index') }}">Weekly League</a>@endif
        @if(auth()->user()->canAccessAdminArea('system'))<a class="button secondary" href="{{ route('admin.system.index') }}">System health</a>@endif
        @if(auth()->user()->canAccessAdminArea('access'))<a class="button secondary" href="{{ route('admin.access.index') }}">Admin access</a>@endif
    </div>
</div>
@if(! auth()->user()->hasTwoFactorEnabled())
<div class="alert error"><strong>Security recommendation:</strong> this administrator account does not have two-factor authentication enabled. <a class="text-link" href="{{ route('security.show') }}">Enable it in Security Center.</a></div>
@endif
<div class="grid stats six">
    <div class="stat"><span>Players</span><strong>{{ number_format($userCount) }}</strong></div>
    <div class="stat"><span>Games</span><strong>{{ number_format($gameCount) }}</strong></div>
    <div class="stat"><span>Plays</span><strong>{{ number_format($entryCount) }}</strong></div>
    <div class="stat"><span>Total stakes</span><strong>{{ number_format($totalStake) }}</strong></div>
    <div class="stat"><span>Total payouts</span><strong>{{ number_format($totalPayout) }}</strong></div>
    <div class="stat"><span>System net</span><strong class="{{ $houseNet >= 0 ? 'positive' : 'negative' }}">{{ number_format($houseNet) }}</strong></div>
</div>
<section class="panel">
<h2>Latest plays</h2>
<div class="table-wrap"><table><thead><tr><th>User</th><th>Game</th><th>Stake</th><th>Payout</th><th>Net</th><th>Result</th></tr></thead><tbody>
@forelse($latestEntries as $entry)
<tr><td>{{ $entry->user->email }}</td><td>{{ $entry->game->name }}</td><td>{{ number_format($entry->stake) }}</td><td>{{ number_format($entry->payout) }}</td><td class="{{ $entry->net >= 0 ? 'positive' : 'negative' }}">{{ number_format($entry->net) }}</td><td><code>{{ json_encode($entry->result) }}</code></td></tr>
@empty
<tr><td colspan="6">No plays yet.</td></tr>
@endforelse
</tbody></table></div>
</section>
@endsection
