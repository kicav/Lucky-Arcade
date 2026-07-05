@extends('layouts.app')
@section('title', 'High Low')
@section('content')
@php($gameResult = session('game_result'))
@php($suit = data_get($gameResult, 'result.suit', 'spades'))
@php($suitSymbol = match($suit) {'hearts' => '♥', 'diamonds' => '♦', 'clubs' => '♣', default => '♠'})
@php($redSuit = in_array($suit, ['hearts', 'diamonds'], true))
<div class="page-head">
    <div><span class="eyebrow">PROVABLY FAIR</span><h1>High Low</h1></div>
    <div class="balance"><span>Balance</span><strong data-live-balance>{{ number_format($wallet->balance) }}</strong></div>
</div>
<div class="game-layout">
<section class="game-stage card-stage {{ $gameResult ? (($gameResult['push'] ?? false) ? 'stage-push' : (($gameResult['won'] ?? false) ? 'stage-win' : 'stage-loss')) : '' }}">
    <div class="playing-card js-playing-card {{ $gameResult ? 'card-settled' : '' }} {{ $redSuit ? 'red-card' : '' }}">
        <span class="card-rank">{{ data_get($gameResult, 'result.rank_label', '7') }}</span>
        <span class="card-suit">{{ $suitSymbol }}</span>
    </div>
    <h2>{{ $gameResult ? (($gameResult['push'] ?? false) ? 'Push' : (($gameResult['won'] ?? false) ? 'Winner' : 'Try again')) : 'Will the card beat 7?' }}</h2>
    <p>Choose higher or lower. A 7 returns your stake; a win pays 1.98×.</p>
</section>
<section class="panel">
    <form method="post" action="{{ route('games.highlow.play', $game) }}" class="stack js-play-form">
        @csrf
        <input type="hidden" name="request_id" value="{{ $requestId }}">
        <label>Stake<input type="number" name="stake" min="{{ $game->min_bet }}" max="{{ $game->max_bet }}" value="{{ old('stake', $game->min_bet) }}" required></label>
        <label>Prediction
            <select name="selection">
                <option value="higher" @selected(old('selection') === 'higher')>Higher than 7</option>
                <option value="lower" @selected(old('selection') === 'lower')>Lower than 7</option>
            </select>
        </label>
        <button class="button" type="submit" data-loading-text="Drawing card…">Draw card</button>
    </form>
    <hr>
    <div class="seed-box"><small>Server seed hash</small><code>{{ $seed->server_seed_hash }}</code><small>Client seed: {{ $seed->client_seed }} · Next nonce: {{ $seed->nonce }}</small></div>
</section>
</div>
@endsection
