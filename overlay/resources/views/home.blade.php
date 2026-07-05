@extends('layouts.app')
@section('title', 'Lucky Arcade')
@section('content')
<section class="hero">
    <div>
        <span class="eyebrow">OPEN-SOURCE SOCIAL GAMING</span>
        <h1>Transparent games with virtual credits.</h1>
        <p>Play Dice, European Roulette, Coin Flip and High Low, inspect your seed hash and verify every historical result after rotating the seed.</p>
        @auth
            <a class="button" href="{{ route('games.index') }}">Open game lobby</a>
        @else
            <a class="button" href="{{ route('register') }}">Create demo account</a>
        @endauth
    </div>
    <div class="hero-card">
        <div class="metric"><span>Currency</span><strong>Virtual credits</strong></div>
        <div class="metric"><span>Randomness</span><strong>HMAC-SHA256</strong></div>
        <div class="metric"><span>Admin result control</span><strong>Disabled</strong></div>
    </div>
</section>

<h2>Available games</h2>
<div class="grid cards">
    @foreach($games as $game)
        <article class="card">
            <div class="game-icon">{{ $game->code === 'dice' ? '⚄' : '◉' }}</div>
            <h3>{{ $game->name }}</h3>
            <p>{{ $game->description }}</p>
            <small>{{ number_format($game->min_bet) }}–{{ number_format($game->max_bet) }} credits</small>
        </article>
    @endforeach
</div>
@endsection
