@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="page-head"><div><span class="eyebrow">PLAYER DASHBOARD</span><h1>Hello, {{ auth()->user()->name }}</h1></div><div class="balance"><span>Balance</span><strong>{{ number_format($wallet->balance) }}</strong><small>virtual credits</small></div></div>

@if(auth()->user()->isSelfExcluded())<div class="alert error">Self-exclusion is active until {{ auth()->user()->self_excluded_until->format('Y-m-d H:i') }}. Game play is temporarily blocked.</div>@endif

<section class="reward-banner {{ $dailyRewardClaimed ? 'claimed' : '' }}"><div><span class="eyebrow">DAILY REWARD</span><h2>{{ $dailyRewardClaimed ? 'Reward collected for today' : number_format($dailyRewardAmount).' credits are ready' }}</h2><p>{{ $dailyRewardClaimed ? 'Come back after midnight for the next virtual-credit reward.' : 'A once-per-day social reward. It has no cash value.' }}</p></div>@if($dailyRewardClaimed)<span class="status-pill">Claimed</span>@else<form method="post" action="{{ route('daily-reward.store') }}">@csrf<button class="button" type="submit">Claim {{ number_format($dailyRewardAmount) }}</button></form>@endif</section>

<div class="dashboard-actions"><a class="button secondary" href="{{ route('games.index') }}">Play games</a><a class="button secondary" href="{{ route('fairness.show') }}">Verify results</a><a class="button secondary" href="{{ route('ledger.export') }}">Export ledger CSV</a><a class="button secondary" href="{{ route('account.show') }}">Play controls</a><a class="button secondary" href="{{ route('achievements.index') }}">Achievements</a><a class="button secondary" href="{{ route('referrals.index') }}">Invite friends</a></div>

<div class="grid stats"><div class="stat"><span>Today&apos;s stake</span><strong>{{ number_format($todayStake) }}</strong></div><div class="stat"><span>Daily limit</span><strong>{{ auth()->user()->daily_stake_limit ? number_format(auth()->user()->daily_stake_limit) : 'None' }}</strong></div><div class="stat"><span>Unread notices</span><strong>{{ auth()->user()->userNotifications()->whereNull('read_at')->count() }}</strong></div></div>

<div class="grid two">
<section class="panel"><h2>Recent plays</h2><div class="table-wrap"><table><thead><tr><th>Game</th><th>Stake</th><th>Payout</th><th>Net</th><th>Time</th></tr></thead><tbody>@forelse($entries as $entry)<tr><td>{{ $entry->game->name }}</td><td>{{ number_format($entry->stake) }}</td><td>{{ number_format($entry->payout) }}</td><td class="{{ $entry->net >= 0 ? 'positive' : 'negative' }}">{{ number_format($entry->net) }}</td><td>{{ $entry->created_at->format('Y-m-d H:i') }}</td></tr>@empty<tr><td colspan="5">No plays yet.</td></tr>@endforelse</tbody></table></div></section>
<section class="panel"><h2>Wallet ledger</h2><div class="table-wrap"><table><thead><tr><th>Type</th><th>Direction</th><th>Amount</th><th>Balance</th></tr></thead><tbody>@forelse($ledger as $item)<tr><td>{{ str_replace('_', ' ', $item->type) }}</td><td class="{{ $item->direction->value === 'credit' ? 'positive' : 'negative' }}">{{ $item->direction->value }}</td><td>{{ number_format($item->amount) }}</td><td>{{ number_format($item->balance_after) }}</td></tr>@empty<tr><td colspan="4">No ledger entries yet.</td></tr>@endforelse</tbody></table></div></section>
</div>

<section class="panel"><div class="section-head"><h2>Recent notifications</h2><a href="{{ route('notifications.index') }}">View all</a></div>@forelse($notifications as $notification)<div class="mini-notification"><strong>{{ $notification->title }}</strong><span>{{ $notification->message }}</span><time>{{ $notification->created_at->diffForHumans() }}</time></div>@empty<p class="empty-note">No notifications yet.</p>@endforelse</section>
@endsection
