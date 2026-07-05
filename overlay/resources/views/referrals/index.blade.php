@extends('layouts.app')
@section('title', 'Referrals')
@section('content')
<div class="page-head"><div><span class="eyebrow">COMMUNITY REWARDS</span><h1>Invite players</h1></div><div class="balance"><span>Earned</span><strong>{{ number_format($rewards->sum('inviter_amount')) }}</strong><small>virtual credits</small></div></div>

<section class="referral-hero">
    <div><span class="eyebrow">YOUR REFERRAL LINK</span><h2>Share a link, reward both accounts</h2><p>You receive {{ number_format($inviterAmount) }} credits and the invited player receives {{ number_format($referredAmount) }} credits after their first completed game.</p></div>
    <div class="copy-box"><code id="referral-url">{{ $referralUrl }}</code><button class="button js-copy" type="button" data-copy-target="referral-url">Copy link</button></div>
</section>

<div class="grid stats"><div class="stat"><span>Referral code</span><strong>{{ $code }}</strong></div><div class="stat"><span>Players invited</span><strong>{{ number_format($invitedUsers->count()) }}</strong></div><div class="stat"><span>Qualified rewards</span><strong>{{ number_format($rewards->count()) }}</strong></div></div>

<div class="grid two">
<section class="panel"><h2>Invited players</h2><div class="table-wrap"><table><thead><tr><th>Player</th><th>Joined</th><th>Status</th></tr></thead><tbody>@forelse($invitedUsers as $player)<tr><td>{{ $player->name }}<br><small>{{ $player->email }}</small></td><td>{{ $player->created_at->format('Y-m-d') }}</td><td>@if($rewards->contains('referred_user_id', $player->id))<span class="status-pill">Qualified</span>@else<span class="status-pill status-muted">Waiting for first game</span>@endif</td></tr>@empty<tr><td colspan="3">No invited players yet.</td></tr>@endforelse</tbody></table></div></section>
<section class="panel"><h2>Reward history</h2><div class="table-wrap"><table><thead><tr><th>Player</th><th>Reward</th><th>Time</th></tr></thead><tbody>@forelse($rewards as $reward)<tr><td>{{ $reward->referredUser?->email ?? 'Deleted account' }}</td><td class="positive">+{{ number_format($reward->inviter_amount) }}</td><td>{{ $reward->created_at->format('Y-m-d H:i') }}</td></tr>@empty<tr><td colspan="3">No referral rewards yet.</td></tr>@endforelse</tbody></table></div></section>
</div>
@endsection
