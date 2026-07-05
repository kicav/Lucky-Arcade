@extends('layouts.app')
@section('title', 'Leaderboard')
@section('content')
<div class="page-head">
    <div><span class="eyebrow">SOCIAL LEADERBOARD</span><h1>Community rankings</h1><p>Rankings use virtual credits only and have no cash value.</p></div>
</div>

<div class="grid two">
<section class="panel">
    <h2>Highest balances</h2>
    <div class="table-wrap"><table>
        <thead><tr><th>Rank</th><th>Player</th><th>Balance</th></tr></thead>
        <tbody>
        @forelse($richest as $index => $wallet)
            <tr><td><span class="rank-badge">{{ $index + 1 }}</span></td><td>{{ $wallet->user->name }}</td><td class="positive">{{ number_format($wallet->balance) }}</td></tr>
        @empty
            <tr><td colspan="3">No players yet.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</section>

<section class="panel">
    <h2>Best lifetime net</h2>
    <div class="table-wrap"><table>
        <thead><tr><th>Rank</th><th>Player</th><th>Plays</th><th>Net</th></tr></thead>
        <tbody>
        @forelse($topWinners as $index => $row)
            <tr><td><span class="rank-badge">{{ $index + 1 }}</span></td><td>{{ $row->user->name }}</td><td>{{ number_format($row->plays) }}</td><td class="{{ $row->total_net >= 0 ? 'positive' : 'negative' }}">{{ number_format($row->total_net) }}</td></tr>
        @empty
            <tr><td colspan="4">No plays yet.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</section>
</div>
@endsection
