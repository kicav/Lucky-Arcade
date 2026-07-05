@extends('layouts.app')
@section('title', 'Player statistics')
@section('content')
<div class="page-head"><div><span class="eyebrow">PLAYER ANALYTICS</span><h1>Your performance</h1></div></div>
<div class="grid stats six">
    <div class="stat"><span>Plays</span><strong>{{ number_format($summary['plays']) }}</strong></div>
    <div class="stat"><span>Wins</span><strong>{{ number_format($summary['wins']) }}</strong></div>
    <div class="stat"><span>Total stake</span><strong>{{ number_format($summary['stake']) }}</strong></div>
    <div class="stat"><span>Total payout</span><strong>{{ number_format($summary['payout']) }}</strong></div>
    <div class="stat"><span>Lifetime net</span><strong class="{{ $summary['net'] >= 0 ? 'positive' : 'negative' }}">{{ number_format($summary['net']) }}</strong></div>
    <div class="stat"><span>Best round</span><strong class="positive">{{ number_format($summary['best_win']) }}</strong></div>
</div>
<section class="panel">
    <div class="section-head"><h2>Last 14 days</h2><span class="hint">Bars show number of plays; label shows daily net.</span></div>
    @php($maxPlays = max(1, (int) $daily->max('plays')))
    <div class="bar-chart stats-chart">
        @foreach($daily as $day)
            <div class="bar-column"><span class="bar-value {{ $day['net'] >= 0 ? 'positive' : 'negative' }}">{{ $day['net'] >= 0 ? '+' : '' }}{{ $day['net'] }}</span><span class="bar" style="height:{{ max(4, (int) round(($day['plays'] / $maxPlays) * 180)) }}px"></span><small>{{ $day['date'] }}</small></div>
        @endforeach
    </div>
</section>
<section class="panel"><h2>By game</h2><div class="table-wrap"><table><thead><tr><th>Game</th><th>Plays</th><th>Wins</th><th>Stake</th><th>Payout</th><th>Net</th><th>Observed RTP</th></tr></thead><tbody>
@forelse($perGame as $row)
<tr><td>{{ $row['name'] }}</td><td>{{ number_format($row['plays']) }}</td><td>{{ number_format($row['wins']) }}</td><td>{{ number_format($row['stake']) }}</td><td>{{ number_format($row['payout']) }}</td><td class="{{ $row['net'] >= 0 ? 'positive' : 'negative' }}">{{ number_format($row['net']) }}</td><td>{{ number_format($row['rtp'], 2) }}%</td></tr>
@empty<tr><td colspan="7">Play a game to start building statistics.</td></tr>@endforelse
</tbody></table></div></section>
@endsection
