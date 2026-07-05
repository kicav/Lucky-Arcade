@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="page-head">
    <div><span class="eyebrow">PLAYER DASHBOARD</span><h1>Hello, {{ auth()->user()->name }}</h1></div>
    <div class="balance"><span>Balance</span><strong>{{ number_format($wallet->balance) }}</strong><small>virtual credits</small></div>
</div>

<div class="grid two">
<section class="panel">
    <h2>Recent plays</h2>
    <div class="table-wrap"><table>
        <thead><tr><th>Game</th><th>Stake</th><th>Payout</th><th>Net</th><th>Time</th></tr></thead>
        <tbody>
        @forelse($entries as $entry)
            <tr>
                <td>{{ $entry->game->name }}</td>
                <td>{{ number_format($entry->stake) }}</td>
                <td>{{ number_format($entry->payout) }}</td>
                <td class="{{ $entry->net >= 0 ? 'positive' : 'negative' }}">{{ number_format($entry->net) }}</td>
                <td>{{ $entry->created_at->format('Y-m-d H:i') }}</td>
            </tr>
        @empty
            <tr><td colspan="5">No plays yet.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</section>

<section class="panel">
    <h2>Wallet ledger</h2>
    <div class="table-wrap"><table>
        <thead><tr><th>Type</th><th>Direction</th><th>Amount</th><th>Balance</th></tr></thead>
        <tbody>
        @forelse($ledger as $item)
            <tr>
                <td>{{ str_replace('_', ' ', $item->type) }}</td>
                <td>{{ $item->direction->value }}</td>
                <td>{{ number_format($item->amount) }}</td>
                <td>{{ number_format($item->balance_after) }}</td>
            </tr>
        @empty
            <tr><td colspan="4">No ledger entries yet.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</section>
</div>
@endsection
