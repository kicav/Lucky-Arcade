@extends('layouts.app')
@section('title', 'Player details')
@section('content')
<div class="page-head">
    <div><span class="eyebrow">ADMIN · PLAYER</span><h1>{{ $player->name }}</h1><p class="hint">{{ $player->email }}</p></div>
    <div class="balance"><span>Balance</span><strong>{{ number_format($player->wallet?->balance ?? 0) }}</strong></div>
</div>

<div class="grid stats">
    <div class="stat"><span>Status</span><strong>{{ $player->suspended_at ? 'Suspended' : 'Active' }}</strong></div>
    <div class="stat"><span>Plays</span><strong>{{ number_format($player->gameEntries()->count()) }}</strong></div>
    <div class="stat"><span>Total stake</span><strong>{{ number_format($player->gameEntries()->sum('stake')) }}</strong></div>
    <div class="stat"><span>Daily limit</span><strong>{{ $player->daily_stake_limit ? number_format($player->daily_stake_limit) : 'None' }}</strong></div>
    <div class="stat"><span>Self-excluded</span><strong>{{ $player->self_excluded_until && $player->self_excluded_until->isFuture() ? 'Yes' : 'No' }}</strong></div>
</div>

@if(auth()->user()->canAccessAdminArea('user_actions'))
<div class="grid two">
<section class="panel">
    <h2>Promotional credits</h2>
    <p class="hint">Creates an immutable ledger credit and audit record. It does not edit wallet history.</p>
    <form method="post" action="{{ route('admin.users.grant', $player) }}" class="stack">
        @csrf
        <label>Amount<input type="number" name="amount" min="10" max="100000" required></label>
        <label>Reason<input type="text" name="reason" minlength="5" maxlength="255" required></label>
        <button class="button" type="submit">Grant credits</button>
    </form>
</section>
<section class="panel">
    <h2>Account status</h2>
    @if($player->suspended_at)
        <p>Suspended at {{ $player->suspended_at->format('Y-m-d H:i') }}</p>
        <p class="hint">Reason: {{ $player->suspension_reason }}</p>
        <form method="post" action="{{ route('admin.users.unsuspend', $player) }}">@csrf<button class="button" type="submit">Reactivate account</button></form>
    @else
        <form method="post" action="{{ route('admin.users.suspend', $player) }}" class="stack">
            @csrf
            <label>Suspension reason<input type="text" name="reason" minlength="5" maxlength="255" required></label>
            <button class="button danger" type="submit">Suspend account</button>
        </form>
    @endif
</section>
</div>
@else
<section class="panel"><p class="hint">Your administrator role has read-only access to player data.</p></section>
@endif

<div class="grid two">
<section class="panel"><h2>Recent plays</h2><div class="table-wrap"><table><thead><tr><th>Game</th><th>Stake</th><th>Payout</th><th>Net</th><th>Time</th></tr></thead><tbody>@forelse($entries as $entry)<tr><td>{{ $entry->game->name }}</td><td>{{ number_format($entry->stake) }}</td><td>{{ number_format($entry->payout) }}</td><td class="{{ $entry->net >= 0 ? 'positive' : 'negative' }}">{{ number_format($entry->net) }}</td><td>{{ $entry->created_at->format('Y-m-d H:i') }}</td></tr>@empty<tr><td colspan="5">No plays.</td></tr>@endforelse</tbody></table></div></section>
<section class="panel"><h2>Recent ledger</h2><div class="table-wrap"><table><thead><tr><th>Type</th><th>Direction</th><th>Amount</th><th>Balance</th></tr></thead><tbody>@forelse($ledger as $item)<tr><td>{{ str_replace('_', ' ', $item->type) }}</td><td>{{ $item->direction->value }}</td><td>{{ number_format($item->amount) }}</td><td>{{ number_format($item->balance_after) }}</td></tr>@empty<tr><td colspan="4">No entries.</td></tr>@endforelse</tbody></table></div></section>
</div>
@endsection
