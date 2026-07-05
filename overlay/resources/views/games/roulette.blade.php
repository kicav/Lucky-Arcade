@extends('layouts.app')
@section('title', 'European Roulette')
@section('content')
<div class="page-head">
    <div><span class="eyebrow">SINGLE ZERO</span><h1>European Roulette</h1></div>
    <div class="balance"><span>Balance</span><strong>{{ number_format($wallet->balance) }}</strong></div>
</div>
<div class="game-layout">
<section class="game-stage roulette-stage"><div class="wheel">0</div><p>0–36. Zero is green and loses on even-money outside bets.</p></section>
<section class="panel">
    <form method="post" action="{{ route('games.roulette.play', $game) }}" class="stack">
        @csrf
        <input type="hidden" name="request_id" value="{{ $requestId }}">
        <label>Stake<input type="number" name="stake" min="{{ $game->min_bet }}" max="{{ $game->max_bet }}" value="{{ old('stake', $game->min_bet) }}" required></label>
        <label>Bet type
            <select name="bet_type">
                <option value="straight">Straight number (0–36)</option>
                <option value="color">Color</option>
                <option value="parity">Odd / even</option>
                <option value="range">Low / high</option>
                <option value="dozen">Dozen</option>
            </select>
        </label>
        <label>Selection<input type="text" name="selection" value="{{ old('selection', 'red') }}" placeholder="17, red, odd, low or 1" required></label>
        <p class="hint">Straight: 0–36 · color: red/black · parity: odd/even · range: low/high · dozen: 1/2/3</p>
        <button class="button" type="submit">Spin</button>
    </form>
    <hr>
    <div class="seed-box"><small>Server seed hash</small><code>{{ $seed->server_seed_hash }}</code><small>Client seed: {{ $seed->client_seed }} · Nonce: {{ $seed->nonce }}</small></div>
</section>
</div>
@endsection
