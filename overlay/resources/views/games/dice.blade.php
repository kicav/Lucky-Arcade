@extends('layouts.app')
@section('title', 'Dice')
@section('content')
<div class="page-head">
    <div><span class="eyebrow">PROVABLY FAIR</span><h1>Dice</h1></div>
    <div class="balance"><span>Balance</span><strong>{{ number_format($wallet->balance) }}</strong></div>
</div>
<div class="game-layout">
<section class="game-stage dice-stage"><div class="big-die">⚄</div><p>Result is generated on the server from your active fairness seed.</p></section>
<section class="panel">
    <form method="post" action="{{ route('games.dice.play', $game) }}" class="stack">
        @csrf
        <input type="hidden" name="request_id" value="{{ $requestId }}">
        <label>Stake<input type="number" name="stake" min="{{ $game->min_bet }}" max="{{ $game->max_bet }}" value="{{ old('stake', $game->min_bet) }}" required></label>
        <label>Direction<select name="direction"><option value="under">Roll under</option><option value="over">Roll over</option></select></label>
        <label>Target<input type="number" name="target" min="2" max="98" value="{{ old('target', 50) }}" required></label>
        <button class="button" type="submit">Roll dice</button>
    </form>
    <hr>
    <div class="seed-box"><small>Server seed hash</small><code>{{ $seed->server_seed_hash }}</code><small>Client seed: {{ $seed->client_seed }} · Nonce: {{ $seed->nonce }}</small></div>
</section>
</div>
@endsection
