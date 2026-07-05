@extends('layouts.app')
@section('title', 'Admin dashboard')
@section('content')
<div class="page-head"><div><span class="eyebrow">ADMINISTRATION</span><h1>Operational overview</h1></div><div><a class="button secondary" href="{{ route('admin.games.index') }}">Game settings</a> <a class="button secondary" href="{{ route('admin.users.index') }}">Users</a></div></div>
<div class="grid stats">
    <div class="stat"><span>Players</span><strong>{{ number_format($userCount) }}</strong></div>
    <div class="stat"><span>Games</span><strong>{{ number_format($gameCount) }}</strong></div>
    <div class="stat"><span>Plays</span><strong>{{ number_format($entryCount) }}</strong></div>
    <div class="stat"><span>Total stakes</span><strong>{{ number_format($totalStake) }}</strong></div>
    <div class="stat"><span>Total payouts</span><strong>{{ number_format($totalPayout) }}</strong></div>
</div>
<section class="panel">
<h2>Latest plays</h2>
<div class="table-wrap"><table><thead><tr><th>User</th><th>Game</th><th>Stake</th><th>Payout</th><th>Result</th></tr></thead><tbody>
@foreach($latestEntries as $entry)
<tr><td>{{ $entry->user->email }}</td><td>{{ $entry->game->name }}</td><td>{{ number_format($entry->stake) }}</td><td>{{ number_format($entry->payout) }}</td><td><code>{{ json_encode($entry->result) }}</code></td></tr>
@endforeach
</tbody></table></div>
</section>
@endsection
