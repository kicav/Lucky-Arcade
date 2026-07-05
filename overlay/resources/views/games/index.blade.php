@extends('layouts.app')
@section('title', 'Game lobby')
@section('content')
<div class="page-head"><div><span class="eyebrow">GAME LOBBY</span><h1>Choose a game</h1></div><div class="balance"><span>Balance</span><strong>{{ number_format(auth()->user()->wallet->balance) }}</strong></div></div>
@if(auth()->user()->isSelfExcluded())<div class="alert error">Self-exclusion is active until {{ auth()->user()->self_excluded_until->format('Y-m-d H:i') }}. You may inspect games but cannot place a bet.</div>@endif
<div class="grid cards">
@foreach($games as $game)
    @php($icon = match($game->code) {'dice' => '⚄', 'roulette' => '◉', 'coinflip' => 'H/T', 'highlow' => 'A↕', default => '◆'})
    <article class="card"><div class="game-icon">{{ $icon }}</div><h2>{{ $game->name }}</h2><p>{{ $game->description }}</p><p><small>Stake: {{ number_format($game->min_bet) }}–{{ number_format($game->max_bet) }}</small></p><a class="button" href="{{ route('games.show', $game) }}">Play</a></article>
@endforeach
</div>
@endsection
