@extends('layouts.app')
@section('title', 'Admin dashboard')
@section('content')
<div class="page-head">
    <div><span class="eyebrow">ADMINISTRATION</span><h1>Operational overview</h1></div>
    <div class="dashboard-actions">
        <a class="button secondary" href="{{ route('admin.analytics') }}">Analytics</a>
        <a class="button secondary" href="{{ route('admin.games.index') }}">Game settings</a>
        <a class="button secondary" href="{{ route('admin.users.index') }}">Users</a>
        <a class="button secondary" href="{{ route('admin.entries.index') }}">Play history</a>
        <a class="button secondary" href="{{ route('admin.audit.index') }}">Audit log</a>
        <a class="button secondary" href="{{ route('admin.announcements.index') }}">Announcements</a>
        <a class="button secondary" href="{{ route('admin.promos.index') }}">Promo codes</a>
        <a class="button secondary" href="{{ route('admin.support.index') }}">Support</a>
        <a class="button secondary" href="{{ route('admin.league.index') }}">Weekly League</a>
    </div>
</div>
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
